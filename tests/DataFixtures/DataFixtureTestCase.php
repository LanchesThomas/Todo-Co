<?php

namespace App\Tests\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class DataFixtureTestCase extends WebTestCase
{
    /** @var \Symfony\Bundle\FrameworkBundle\KernelBrowser */
    protected $client;

    /** @var ContainerInterface */
    protected $containerTest;

    /** @var EntityManagerInterface */
    protected $entityManager;

    protected function setUp(): void
    {
        parent::setUp(); // boot le kernel

        // On réutilise la base existante : on ne drop/create rien ici
        $this->client = static::createClient();
        $this->containerTest = $this->client->getContainer();
        $this->entityManager = $this->containerTest->get('doctrine.orm.entity_manager');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null; // éviter les fuites mémoire
        }
    }
}
