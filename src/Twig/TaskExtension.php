<?php

namespace App\Twig;

use App\Entity\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TaskExtension extends AbstractExtension
{
    /**
     * TaskExtension constructor.
     *
     * Initializes the security service.
     *
     * @param Security $security
     */
    public function __construct(private Security $security) {}

    /**
     * Returns the list of Twig functions provided by this extension.
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('can_edit_or_delete', [$this, 'canEditOrDelete']),
        ];
    }

    /**
     * Checks if the current user can edit or delete a task.
     *
     * @param Task $task
     * @return bool
     */
   public function canEditOrDelete(Task $task): bool
    {
        $user = $this->security->getUser();

        // Pas d'utilisateur connecté → pas de droit
        if (!$user) {
            return false;
        }

        $taskOwner = $task->getUser();

        // Si la tâche appartient à "anonymous", seuls les admins peuvent éditer/supprimer
        if ($taskOwner && $taskOwner->getUsername() === 'anonymous') {
            return $this->security->isGranted('ROLE_ADMIN');
        }

        // Sinon (tâche non-anonyme), seul le propriétaire peut éditer/supprimer
        return $taskOwner === $user;
    }


}
