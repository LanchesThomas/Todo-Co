<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $em;
    private User $user1;
    private User $user2;
    private User $admin;
    private User $anonymous;

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

        // Création des utilisateurs
        $this->user1 = new User();
        $this->user1->setUsername('user1');
        $this->user1->setEmail('user1@example.com');
        $this->user1->setRoles(['ROLE_USER']);
        $this->user1->setPassword($hasher->hashPassword($this->user1, '123456'));

        $this->user2 = new User();
        $this->user2->setUsername('user2');
        $this->user2->setEmail('user2@example.com');
        $this->user2->setRoles(['ROLE_USER']);
        $this->user2->setPassword($hasher->hashPassword($this->user2, '123456'));

        $this->admin = new User();
        $this->admin->setUsername('admin');
        $this->admin->setEmail('admin@example.com');
        $this->admin->setRoles(['ROLE_ADMIN']);
        $this->admin->setPassword($hasher->hashPassword($this->admin, '123456'));

        $this->anonymous = new User();
        $this->anonymous->setUsername('anonymous');
        $this->anonymous->setEmail('anonymous@example.com');
        $this->anonymous->setRoles(['ROLE_USER']);
        $this->anonymous->setPassword($hasher->hashPassword($this->anonymous, '123456'));

        $this->em->persist($this->user1);
        $this->em->persist($this->user2);
        $this->em->persist($this->admin);
        $this->em->persist($this->anonymous);

        // Création des tâches de test
        $task1 = new Task();
        $task1->setTitle('Tâche de user1');
        $task1->setContent('Contenu de la tâche 1');
        $task1->setUser($this->user1);
        $task1->toggle(false);

        $task2 = new Task();
        $task2->setTitle('Tâche de user1 à supprimer');
        $task2->setContent('Contenu de la tâche 2');
        $task2->setUser($this->user1);
        $task2->toggle(false);

        $task3 = new Task();
        $task3->setTitle('Tâche anonyme');
        $task3->setContent('Contenu de la tâche anonyme');
        $task3->setUser($this->anonymous);
        $task3->toggle(false);

        $task4 = new Task();
        $task4->setTitle('Tâche de user2');
        $task4->setContent('Contenu de la tâche de user2');
        $task4->setUser($this->user2);
        $task4->toggle(false);

        $this->em->persist($task1);
        $this->em->persist($task2);
        $this->em->persist($task3);
        $this->em->persist($task4);
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
        $this->client->request('GET', '/tasks');
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testListAction(): void
    {
        $this->performLogin('user1', '123456');

        $this->client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
    }

    public function testCreateAction(): void
    {
        $this->loginEntityUser('user1');

        $crawler = $this->client->request('GET', '/tasks/create');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('input[name="task[title]"]');
        $this->assertSelectorExists('textarea[name="task[content]"]');

        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'Nouvelle tâche',
            'task[content]' => 'Ceci est une tâche crée par un test',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testEditAction(): void
    {
        $this->loginEntityUser('user1');

        $crawler = $this->client->request('GET', '/tasks/1/edit');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('input[name="task[title]"]');
        $this->assertSelectorExists('textarea[name="task[content]"]');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'Modification de tache',
            'task[content]' => 'Je modifie une tache',
        ]);
        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteTaskAction(): void
    {
        $this->performLogin('user1', '123456');

        $this->client->request('GET', '/tasks/2/delete');
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Message exact du contrôleur
        $this->assertSelectorTextContains('div.alert.alert-success', "La tâche a bien été supprimée.");
    }

    public function testDeleteTaskActionWhereSimpleUserIsNotAuthor(): void
    {
        $this->performLogin('user1', '123456');

        $this->client->request('GET', '/tasks/4/delete');
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Message exact du contrôleur
        $this->assertSelectorTextContains(
            'div.alert.alert-danger',
            "Vous ne pouvez pas supprimer cette tâche."
        );
    }

    public function testDeleteTaskActionWithSimpleUserWhereAuthorIsAnonymous(): void
    {
        $this->performLogin('user1', '123456');

        $this->client->request('GET', '/tasks/3/delete');
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Message exact du contrôleur
        $this->assertSelectorTextContains(
            'div.alert.alert-danger',
            "Vous ne pouvez pas supprimer cette tâche."
        );
    }

    public function testDeleteTaskActionWithAdminWhereAuthorIsAnonymous(): void
    {
        $this->performLogin('admin', '123456');

        $this->client->request('GET', '/tasks/3/delete');
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // L'admin peut supprimer les tâches anonymes
        $this->assertSelectorTextContains('div.alert.alert-success', "La tâche a bien été supprimée.");
    }

    public function testDeleteTaskActionWhereItemDontExists(): void
    {
        $this->performLogin('user1', '123456');

        $this->client->request('GET', '/tasks/-100/delete');
        $this->assertResponseStatusCodeSame(404);
    }

     public function testDoneListAction(): void
    {
        $this->loginEntityUser('user1');

        $task = $this->em->getRepository(Task::class)->find(1);
        $task->toggle(true);
        $this->em->flush();

        $this->client->request('GET', '/tasks/done');
        $this->assertResponseIsSuccessful();
    }

    public function testDoneListActionWithoutLogin(): void
    {
        $this->client->request('GET', '/tasks/done');
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects('/login');
    }

    public function testToggleTaskAction(): void
    {
        $this->performLogin('user1', '123456');

        $this->client->request('GET', '/tasks/1/toggle');
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        // Vérifier le message de toggle
        $this->assertSelectorTextContains('div.alert.alert-success', 'a bien été marquée comme');
    }

    public function testEditActionWithUnauthorizedUser(): void
    {
        $this->loginEntityUser('user1');

        // Essayer de modifier la tâche de user2 (ID 4)
        $this->client->request('GET', '/tasks/4/edit');
        $this->assertResponseStatusCodeSame(302);

        $crawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains(
            'div.alert.alert-danger',
            "Vous ne pouvez pas modifier cette tâche."
        );
    }
}