<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private ?EntityManagerInterface $em = null;
    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown(); // garantir un kernel propre pour createClient
        $this->client = static::createClient();

        $container = self::getContainer();
        $this->em = $container->get('doctrine')->getManager();

        // Recréation du schéma (utile avec sqlite:///:memory:)
        $meta = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($meta)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($meta);
            $schemaTool->createSchema($meta);
        }

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get('security.user_password_hasher');

        // Création de user1
        $this->user = new User();
        $this->user->setUsername('user1');
        $this->user->setEmail('user1@example.com');
        $this->user->setRoles(['ROLE_USER']);
        $this->user->setPassword($hasher->hashPassword($this->user, '123456'));

        // Création de admin
        $this->admin = new User();
        $this->admin->setUsername('admin');
        $this->admin->setEmail('admin@example.com');
        $this->admin->setRoles(['ROLE_ADMIN']);
        $this->admin->setPassword($hasher->hashPassword($this->admin, '123456'));

        $this->em->persist($this->user);
        $this->em->persist($this->admin);
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

    public function testLogin(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'user1',
            '_password' => '123456',
        ]);
        $this->client->submit($form);

        // Doit rediriger vers la home
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Bienvenue');
    }

    public function testLoginAsAdmin(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'admin',
            '_password' => '123456',
        ]);
        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Bienvenue');
    }

    public function testLoginWithWrongCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'user1',
            '_password' => 'WrongPassword',
        ]);
        $this->client->submit($form);

        // Peut renvoyer sur /login avec redirection ou pas
        if ($this->client->getResponse()->isRedirection()) {
            $crawler = $this->client->followRedirect();
        }

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.alert.alert-danger');
        $this->assertStringContainsString(
            'Identifiants invalides.',
            $crawler->filter('div.alert.alert-danger')->text()
        );
    }

    public function testAlreadyAuthenticatedIsRedirectedFromLogin(): void
    {
        // Authentifier directement l'utilisateur persisted
        $this->client->loginUser($this->user);

        // Aller sur /login doit renvoyer vers /
        $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/');
    }

}
