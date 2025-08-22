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
    /**
     * TaskController constructor.
     *
     * Initializes the EntityManager and TaskRepository.
     *
     * @param EntityManagerInterface $entityManager
     * @param TaskRepository $taskRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TaskRepository $taskRepository,
    ) {}

    /**
     * Displays the list of tasks.
     *
     * @return Response
     */
    #[Route('', name: 'task_list', methods: ['GET'])]
    public function list(): Response
    {
        $tasks = $this->taskRepository->findBy([], ['createdAt' => 'DESC']);
        $user = $this->getUser();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'user' => $user,
        ]);
    }

    /**
     * Displays the list of completed tasks.
     *
     * @return Response
     */
    #[Route('/done', name: 'task_list_done', methods: ['GET'])]
        public function doneList(): Response
    {
        $tasks = $this->taskRepository->findBy(['isDone' => true], ['createdAt' => 'DESC']);
        $user = $this->getUser();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'user' => $user,
        ]);
    }

    /**
     * Creates a new task.
     *
     * @param Request $request
     * @return Response
     */
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

    /**
     * Edits an existing task.
     *
     * @param Task $task
     * @param Request $request
     * @return Response
     */
    #[Route('/{id}/edit', name: 'task_edit', methods: ['GET', 'POST'])]
    public function edit(Task $task, Request $request): Response
    {
        // $this->denyAccessUnlessGranted('TASK_EDIT', $task);

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

    /**
     * Deletes a task.
     *
     * @param Task $task
     * @return RedirectResponse
     */
    #[Route('/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete(Task $task, Request $request): RedirectResponse
    {
        // $this->denyAccessUnlessGranted('TASK_DELETE', $task);

        // (Optionnel mais recommandé) Vérif CSRF
        if (!$this->isCsrfTokenValid('delete_task_'.$task->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('task_list');
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');
        return $this->redirectToRoute('task_list');
    }

    /**
     * Toggles the completion status of a task.
     *
     * @param Task $task
     * @return RedirectResponse
     */
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

}
