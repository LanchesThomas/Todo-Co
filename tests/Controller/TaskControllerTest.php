<?php
// tests/Controller/TaskControllerTest.php
namespace App\Tests\Controller;

use App\Controller\TaskController;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TaskControllerTest extends TestCase
{
    private EntityManagerInterface $em;
    private TaskRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repo = $this->createMock(TaskRepository::class);
    }

    public function testListRendersTasksAndUser(): void
    {
        $tasks = [new Task(), new Task()];
        $user = $this->createMock(User::class);

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->repo->expects($this->once())
            ->method('findAll')
            ->willReturn($tasks);

        $controller->expects($this->once())
            ->method('render')
            ->with('task/list.html.twig', [
                'tasks' => $tasks,
                'user' => $user,
            ])
            ->willReturn(new Response());

        $response = $controller->list();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testDoneListRendersOnlyDoneTasks(): void
    {
        $doneTasks = [new Task()];
        $user = $this->createMock(User::class);

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['getUser', 'render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->repo->expects($this->once())
            ->method('findBy')
            ->with(['isDone' => true])
            ->willReturn($doneTasks);

        $controller->expects($this->once())
            ->method('render')
            ->with('task/list.html.twig', [
                'tasks' => $doneTasks,
                'user' => $user,
            ])
            ->willReturn(new Response());

        $response = $controller->doneList();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testToggleChangesStatusAndRedirects(): void
    {
        $task = $this->createMock(Task::class);
        // Simule tâche déjà faite, on passe à non faite (toggle(false))
        $task->expects($this->exactly(2))
            ->method('isDone')
            ->willReturnOnConsecutiveCalls(true, false);
        $task->expects($this->once())
            ->method('toggle')
            ->with(false);

        $this->em->expects($this->once())
            ->method('flush');

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('addFlash')
            ->with(
                'success',
                sprintf(
                    'La tâche "%s" a bien été marquée comme %s.',
                    null,
                    'non faite'
                )
            );

        $fakeRedirect = new RedirectResponse('/tasks');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('task_list')
            ->willReturn($fakeRedirect);

        $response = $controller->toggle($task);
        $this->assertSame($fakeRedirect, $response);
    }

    public function testDeleteNotAllowedRedirectsWithErrorFlash(): void
    {
        $task = $this->createMock(Task::class);
        $user = $this->createMock(User::class);

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['getUser', 'addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('error', 'Vous ne pouvez pas supprimer cette tâche.');

        $fakeRedirect = new RedirectResponse('/tasks');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('task_list')
            ->willReturn($fakeRedirect);

        $response = $controller->delete($task);
        $this->assertSame($fakeRedirect, $response);
    }

    public function testDeleteAllowedRemovesAndRedirects(): void
    {
        $task = $this->createMock(Task::class);
        $user = $this->createMock(User::class);

        $this->em->expects($this->once())
            ->method('remove')
            ->with($task);
        $this->em->expects($this->once())
            ->method('flush');

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$this->em, $this->repo])
            ->onlyMethods(['getUser', 'addFlash', 'redirectToRoute'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $task->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $controller->expects($this->once())
            ->method('addFlash')
            ->with('success', 'La tâche a bien été supprimée.');

        $fakeRedirect = new RedirectResponse('/tasks');
        $controller->expects($this->once())
            ->method('redirectToRoute')
            ->with('task_list')
            ->willReturn($fakeRedirect);

        $response = $controller->delete($task);
        $this->assertSame($fakeRedirect, $response);
    }
}