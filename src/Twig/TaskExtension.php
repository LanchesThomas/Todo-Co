<?php

namespace App\Twig;

use App\Entity\Task;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TaskExtension extends AbstractExtension
{
    public function __construct(private Security $security) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('can_edit_or_delete', [$this, 'canEditOrDelete']),
        ];
    }

    public function canEditOrDelete(Task $task): bool
{
    $user = $this->security->getUser();

    return
        $task->getUser() === $user ||
        (
            $task->getUser()?->getUsername() === 'anonymous' &&
            $this->security->isGranted('ROLE_ADMIN')
        );
}

}
