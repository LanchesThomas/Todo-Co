<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
   public function testIndexDisplaysHomeWhenAuthenticated(): void
{
    self::ensureKernelShutdown();
    $client = static::createClient();
    $container = self::getContainer();
    $em = $container->get('doctrine')->getManager();

    // (Re)création du schéma si tu es en sqlite:///:memory:
    $meta = $em->getMetadataFactory()->getAllMetadata();
    if (!empty($meta)) {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $schemaTool->dropSchema($meta);
        $schemaTool->createSchema($meta);
    }

    // Création et persistence d'un utilisateur
    /** @var \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $hasher */
    $hasher = $container->get('security.user_password_hasher');
    $user = new \App\Entity\User();
    $user->setUsername('user1');
    $user->setEmail('user1@example.com');
    $user->setRoles(['ROLE_USER']);
    $user->setPassword($hasher->hashPassword($user, '123456'));
    $em->persist($user);
    $em->flush();

    // Authentification directe (bypass du formulaire)
    $client->loginUser($user);

    // Requête vers /
    $client->request('GET', '/');
    $this->assertResponseIsSuccessful();

    // Vérifie que le h1 attendu est présent (ajuste selon ton template)
    $this->assertSelectorTextContains(
        'h1',
        "Bienvenue sur Todo List"
    );
}

}
