<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[UniqueEntity(
    fields: ['email'],
    message: 'Cette adresse email est déjà utilisée.'
)]
#[UniqueEntity(
    fields: ['username'],
    message: 'Ce nom d\'utilisateur est déjà utilisé.'
)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 25, unique: true)]
    #[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $password = null;

    #[ORM\Column(length: 60, unique: true)]
    #[Assert\NotBlank(message: 'Vous devez saisir une adresse email.')]
    #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
    private ?string $email = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private Collection $tasks;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    /**
     * Returns the ID of the user.
     *
     * @return int|null The ID of the user or null if not set.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * Sets the ID of the user.
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
     * Returns the username.
     */    
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Sets the username.
     *
     * @param string $username The username to set.
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * Returns the salt used to encode the password.
     *
     * @return string|null The salt or null if not used.
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * Returns the password.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Sets the password.
     *
     * @param string $password The password to set.
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * Returns the email address of the user.
     *
     * @return string|null The email address or null if not set.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets the email address of the user.
     *
     * @param string $email The email address to set.
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * Returns the roles of the user.
     *
     * @see UserInterface
     * @return array<string> The roles of the user.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Removes sensitive data from the user.
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * Adds a task to the user's task collection.
     *
     * @param Task $task The task to add.
     * @return static Returns the current instance for method chaining.
     */
    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    /**
     * Removes a task from the user's task collection.
     *
     * @param Task $task The task to remove.
     * @return static Returns the current instance for method chaining.
     */
    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Sets the roles of the user.
     *
     * @param array<string> $roles The roles to set.
     * @return static Returns the current instance for method chaining.
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }
}