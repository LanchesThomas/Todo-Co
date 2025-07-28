<?php
// tests/Entity/UserEntityTest.php
namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Task;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    public function testIdGetterSetter(): void
    {
        $user = new User();
        $this->assertNull($user->getId());

        $returned = $user->setId(42);
        $this->assertSame($user, $returned);
        $this->assertSame(42, $user->getId());
    }

    public function testUsernameGetterSetter(): void
    {
        $user = new User();
        $this->assertNull($user->getUsername());

        $user->setUsername('alice');
        $this->assertSame('alice', $user->getUsername());
    }

    public function testUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $this->assertSame('user@example.com', $user->getUserIdentifier());
    }

    public function testPasswordGetterSetter(): void
    {
        $user = new User();
        $this->assertNull($user->getPassword());

        $user->setPassword('secret');
        $this->assertSame('secret', $user->getPassword());
    }

    public function testEmailGetterSetter(): void
    {
        $user = new User();
        $this->assertNull($user->getEmail());

        $user->setEmail('foo@bar.com');
        $this->assertSame('foo@bar.com', $user->getEmail());
    }

    public function testRolesDefaultAndSetter(): void
    {
        $user = new User();
        // default roles should include ROLE_USER
        $roles = $user->getRoles();
        $this->assertContains('ROLE_USER', $roles);

        // setting roles
        $user->setRoles(['ROLE_ADMIN']);
        $roles2 = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles2);
        $this->assertContains('ROLE_USER', $roles2, 'ROLE_USER always present');
    }

    public function testEraseCredentials(): void
    {
        $user = new User();
        // just ensure method can be called without errors
        $this->assertNull($user->eraseCredentials());
    }

    public function testTasksCollection(): void
    {
        $user = new User();
        $tasks = $user->getTasks();
        $this->assertInstanceOf(Collection::class, $tasks);

        $task1 = new Task();
        $task2 = new Task();

        $returned = $user->addTask($task1);
        $this->assertSame($user, $returned);
        $this->assertTrue($tasks->contains($task1));
        $this->assertSame($user, $task1->getUser());

        // adding same task again should not duplicate
        $user->addTask($task1);
        $this->assertCount(1, $tasks);

        // add another and remove
        $user->addTask($task2);
        $this->assertTrue($tasks->contains($task2));

        $return2 = $user->removeTask($task1);
        $this->assertSame($user, $return2);
        $this->assertFalse($tasks->contains($task1));
        $this->assertNull($task1->getUser());
    }
}
