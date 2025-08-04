<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $em;
    private User $admin;
    private User $user1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $container = self::getContainer();
        $this->em = $container->get('doctrine')->getManager();

        // Recréation propre du schéma
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($meta)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($meta);
            $schemaTool->createSchema($meta);
        }

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get('security.user_password_hasher');

        // Création d'un admin pour les tests
        $this->admin = new User();
        $this->admin->setUsername('admin');
        $this->admin->setEmail('admin@example.com');
        $this->admin->setRoles(['ROLE_ADMIN']);
        $this->admin->setPassword($hasher->hashPassword($this->admin, '123456'));

        // Création d'un utilisateur simple pour les tests
        $this->user1 = new User();
        $this->user1->setUsername('user1');
        $this->user1->setEmail('user1@example.com');
        $this->user1->setRoles(['ROLE_USER']);
        $this->user1->setPassword($hasher->hashPassword($this->user1, '123456'));

        $this->em->persist($this->admin);
        $this->em->persist($this->user1);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->em) {
            $this->em->close();
            $this->em = null;
        }
    }

    private function performLogin(string $usernameOrEmail, string $password): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $usernameOrEmail,
            '_password' => $password,
        ]);
        $this->client->submit($form);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', "Bienvenue");
    }

    private function loginEntityUser(string $username): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        $this->assertNotNull($user, "L'utilisateur $username doit exister en base pour se connecter.");
        $this->client->loginUser($user);
    }

    public function testListActionWithoutLogin(): void
    {
        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }

    public function testListActionWithSimpleUser(): void
    {
        $this->loginEntityUser('user1');

        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(403); // Accès interdit car pas ROLE_ADMIN
    }

    public function testListActionWithAdmin(): void
    {
        $this->loginEntityUser('admin');

        $this->client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateActionWithoutLogin(): void
    {
        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }

    public function testCreateActionWithSimpleUser(): void
    {
        $this->loginEntityUser('user1');

        $this->client->request('GET', '/users/create');
        $this->assertResponseStatusCodeSame(403); // Accès interdit car pas ROLE_ADMIN
    }

    public function testCreateActionWithAdmin(): void
    {
        $this->loginEntityUser('admin');

        $crawler = $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();

        // Vérifier que le formulaire est présent
        $this->assertSelectorExists('input[name="user[username]"]');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
        $this->assertSelectorExists('select[name="user[roles]"]');

        // Soumettre le formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'newuser',
            'user[email]' => 'newuser@example.com',
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
            'user[roles]' => 'ROLE_USER',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Vérifier le message de succès
        $this->assertSelectorTextContains('div.alert.alert-success', "L'utilisateur a bien été ajouté.");

        // Vérifier que l'utilisateur a été créé en base
        $newUser = $this->em->getRepository(User::class)->findOneBy(['username' => 'newuser']);
        $this->assertNotNull($newUser);
        $this->assertEquals('newuser@example.com', $newUser->getEmail());
        $this->assertEquals(['ROLE_USER'], $newUser->getRoles());
    }

    public function testEditActionWithoutLogin(): void
    {
        $this->client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }

    public function testEditActionWithSimpleUser(): void
    {
        $this->loginEntityUser('user1');

        $this->client->request('GET', '/users/1/edit');
        $this->assertResponseStatusCodeSame(403); // Accès interdit car pas ROLE_ADMIN
    }

    public function testEditActionWithAdmin(): void
    {
        $this->loginEntityUser('admin');

        $crawler = $this->client->request('GET', '/users/1/edit');
        $this->assertResponseIsSuccessful();

        // Vérifier que le formulaire est présent et pré-rempli
        $this->assertSelectorExists('input[name="user[username]"]');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
        $this->assertSelectorExists('select[name="user[roles]"]');

        // Modifier l'utilisateur
        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'admin_modified',
            'user[email]' => 'admin_modified@example.com',
            'user[password][first]' => 'newpassword123',
            'user[password][second]' => 'newpassword123',
            'user[roles]' => 'ROLE_ADMIN',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Vérifier le message de succès
        $this->assertSelectorTextContains('div.alert.alert-success', "L'utilisateur a bien été modifié.");

        // Vérifier que l'utilisateur a été modifié en base
        $this->em->refresh($this->admin);
        $this->assertEquals('admin_modified', $this->admin->getUsername());
        $this->assertEquals('admin_modified@example.com', $this->admin->getEmail());
    }

    public function testEditActionWithNonExistentUser(): void
    {
        $this->loginEntityUser('admin');

        $this->client->request('GET', '/users/-100/edit');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateActionWithInvalidData(): void
    {
        $this->loginEntityUser('admin');

        $crawler = $this->client->request('GET', '/users/create');
        $this->assertResponseIsSuccessful();

        // Soumettre le formulaire avec des données invalides (email manquant)
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'invaliduser',
            'user[email]' => '', // Email vide
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
            'user[roles]' => 'ROLE_USER',
        ]);
        $this->client->submit($form);

        // Le formulaire doit être re-affiché avec des erreurs
        $this->assertResponseIsSuccessful();
        // Note: Tu peux ajouter des vérifications d'erreurs spécifiques selon ton formulaire
    }

    public function testEditActionWithoutPasswordChange(): void
    {
        $this->loginEntityUser('admin');

        $crawler = $this->client->request('GET', '/users/1/edit');
        $this->assertResponseIsSuccessful();

        // Modifier seulement l'username, laisser les champs password vides
        $form = $crawler->selectButton('Modifier')->form([
            'user[username]' => 'admin_username_only',
            'user[email]' => $this->admin->getEmail(),
            'user[password][first]' => '', // Champs password vides
            'user[password][second]' => '',
            'user[roles]' => 'ROLE_ADMIN',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Vérifier que l'utilisateur a été modifié
        $this->em->refresh($this->admin);
        $this->assertEquals('admin_username_only', $this->admin->getUsername());
    }
}