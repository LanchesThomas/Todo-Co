<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Repository\TaskRepository;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskRepository $taskRepository,
    ) {}

    #[Route('', name: 'task_list', methods: ['GET'])]
    public function list(): Response
    {
        $tasks = $this->taskRepository->findAll();
        $user = $this->getUser();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'user' => $user,
        ]);
    }

    #[Route('/done', name: 'task_list_done', methods: ['GET'])]
        public function doneList(): Response
    {
        $tasks = $this->taskRepository->findBy(['isDone' => true]);
        $user = $this->getUser();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'user' => $user,
        ]);
    }


    #[Route('/create', name: 'task_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 🔗 Associer automatiquement l'utilisateur connecté
            $task->setUser($this->getUser());

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Task $task, Request $request): Response
    {
        if (!$this->canUserEditOrDelete($task)) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette tâche.');
            return $this->redirectToRoute('task_list');
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }


    #[Route('/{id}/toggle', name: 'task_toggle', methods: ['GET'])]
    public function toggle(Task $task): RedirectResponse
    {
        $task->toggle(!$task->isDone());
        $this->entityManager->flush();

        $this->addFlash('success', sprintf(
            'La tâche "%s" a bien été marquée comme %s.',
            $task->getTitle(),
            $task->isDone() ? 'faite' : 'non faite'
        ));

        return $this->redirectToRoute('task_list');
    }


    #[Route('/{id}/delete', name: 'task_delete', methods: ['GET'])]
    public function delete(Task $task): RedirectResponse
    {
        if (!$this->canUserEditOrDelete($task)) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette tâche.');
            return $this->redirectToRoute('task_list');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }


    private function canUserEditOrDelete(Task $task): bool
    {
        $currentUser = $this->getUser();

        return
            $task->getUser() === $currentUser ||
            (
                $task->getUser()?->getUsername() === 'anonymous'
                && $this->isGranted('ROLE_ADMIN')
            );
    }


}
