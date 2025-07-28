<?php
// tests/Entity/TaskEntityTest.php
namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class TaskEntityTest extends TestCase
{
    public function testConstructorInitializesDefaults(): void
    {
        $task = new Task();

        $this->assertInstanceOf(DateTime::class, $task->getCreatedAt(), 'createdAt should be a DateTime instance');
        $this->assertFalse($task->isDone(), 'isDone should default to false');
    }

    public function testGetAndSetId(): void
    {
        $task = new Task();
        $this->assertNull($task->getId());

        $returned = $task->setId(123);
        $this->assertSame($task, $returned, 'setId should be chainable');
        // getId() is declared to return ?string, so integer 123 becomes string '123'
        $this->assertSame('123', $task->getId());
    }

    public function testGetAndSetCreatedAt(): void
    {
        $task = new Task();
        $dt = new DateTime('2000-01-01');
        $task->setCreatedAt($dt);
        $this->assertSame($dt, $task->getCreatedAt());
    }

    public function testGetAndSetTitle(): void
    {
        $task = new Task();
        $this->assertNull($task->getTitle());
        $task->setTitle('My Title');
        $this->assertSame('My Title', $task->getTitle());
    }

    public function testGetAndSetContent(): void
    {
        $task = new Task();
        $this->assertNull($task->getContent());
        $task->setContent('Some content');
        $this->assertSame('Some content', $task->getContent());
    }

    public function testToggleChangesIsDone(): void
    {
        $task = new Task();
        $this->assertFalse($task->isDone());

        $task->toggle(true);
        $this->assertTrue($task->isDone());

        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testGetAndSetUser(): void
    {
        $task = new Task();
        $this->assertNull($task->getUser());

        $user = new User();
        $returned = $task->setUser($user);
        $this->assertSame($task, $returned, 'setUser should be chainable');
        $this->assertSame($user, $task->getUser());
    }
}
