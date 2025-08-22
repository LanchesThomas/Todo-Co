<?php

namespace App\Security;

use App\Entity\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const EDIT   = 'TASK_EDIT';
    public const DELETE = 'TASK_DELETE';

    public function __construct(private readonly Security $security) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::EDIT, self::DELETE], true)
            && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Task $task */
        $task = $subject;

        $user = $this->security->getUser();
        if (!$user instanceof UserInterface) {
            return false; // non connecté
        }

        return match ($attribute) {
            self::EDIT   => $this->canEdit($task, $user),
            self::DELETE => $this->canDelete($task, $user),
            default      => false,
        };
    }

    private function isOwner(Task $task, UserInterface $user): bool
    {
        $owner = $task->getUser();
        if (!$owner || !method_exists($owner, 'getId') || !method_exists($user, 'getId')) {
            return false;
        }

        return $owner->getId() === $user->getId();
    }

    private function canEdit(Task $task, UserInterface $user): bool
    {
        // édition réservée au propriétaire
        return $this->isOwner($task, $user);
    }

    private function canDelete(Task $task, UserInterface $user): bool
    {
        // 1) OK pour supprimer sa propre tâche
        if ($this->isOwner($task, $user)) {
            return true;
        }

        // 2) ADMIN : peut supprimer les tâches "anonymous"
        if ($this->security->isGranted('ROLE_ADMIN') && $this->isAnonymousOwner($task)) {
            return true;
        }

        return false;
    }


    private function isAnonymousOwner(Task $task): bool
    {
        $owner = $task->getUser();
        if (!$owner) {
            return false;
        }

        // On ne regarde QUE le username
        if (!method_exists($owner, 'getUsername')) {
            return false; // pas de username sur l'entité User -> considéré non-anonymous
        }

        return $owner->getUsername() === 'anonymous';
    }
}
