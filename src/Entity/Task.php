<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column()]
    private ?DateTime $createdAt = null;

    #[ORM\Column()]
    #[Assert\NotBlank(message: 'Vous devez saisir un titre.')]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Vous devez saisir du contenu.')]
    private ?string $content = null;

    #[ORM\Column()]
    private ?bool $isDone = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)] 
    private ?User $user = null;

    /**
     * Task constructor.
     *
     * Initializes the createdAt property to the current date and time,
     * and sets isDone to false by default.
     */
    public function __construct()
    {
        $this->createdAt = new Datetime();
        $this->isDone = false;
    }

    /**
     * Gets the ID of the task.
     *
     * @return int|null The ID of the task or null if not set.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets the ID of the task.
     *
     * @param int|null $id The ID to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the creation date and time of the task.
     *
     * @return DateTime|null The creation date and time or null if not set.
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date and time of the task.
     *
     * @param DateTime $createdAt The creation date and time to set.
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Gets the title of the task.
     *
     * @return string|null The title of the task.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the title of the task.
     *
     * @param string $title The title to set.
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * Gets the content of the task.
     *
     * @return string|null The content of the task.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Sets the content of the task.
     *
     * @param string $content The content to set.
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * Checks if the task is done.
     *
     * @return bool|null True if the task is done, false otherwise, or null if not set.
     */
    public function isDone(): ?bool
    {
        return $this->isDone;
    }

    /**
     * Sets the done status of the task.
     *
     * @param bool $isDone The done status to set.
     */
    public function toggle($flag): void
    {
        $this->isDone = $flag;
    }

    /**
     * Gets the user associated with the task.
     *
     * @return User|null The user associated with the task or null if not set.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Gets the username associated with the task.
     *
     * @return string The username.
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}