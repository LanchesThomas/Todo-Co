<?php
// tests/Repository/TaskRepositoryTest.php
namespace App\Tests\Repository;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    public function testCanInstantiate(): void
    {
        self::bootKernel();
        $registry   = static::getContainer()->get('doctrine');
        $repository = new TaskRepository($registry);

        $this->assertInstanceOf(TaskRepository::class, $repository);
    }
}
