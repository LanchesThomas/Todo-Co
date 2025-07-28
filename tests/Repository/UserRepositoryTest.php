<?php
namespace App\Tests\Repository;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    public function testCanInstantiate(): void
    {
        self::bootKernel();
        $registry   = static::getContainer()->get('doctrine');
        $repository = new UserRepository($registry);

        $this->assertInstanceOf(UserRepository::class, $repository);
    }
}
