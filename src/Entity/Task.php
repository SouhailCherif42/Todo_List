<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deadline = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'ownedTasks')]
    #[ORM\JoinTable(name: 'task_owners')]
    private Collection $owners; // Relation ManyToMany pour les propriétaires

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'assignedTasks')]
    #[ORM\JoinTable(name: 'task_assignees')]
    private Collection $assignees; // Relation ManyToMany pour les assignations

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $priority = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    public function __construct()
    {
        $this->owners = new ArrayCollection();
        $this->assignees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getOwners(): Collection
    {
        return $this->owners;
    }

    public function addOwner(User $owner): self
    {
        if (!$this->owners->contains($owner)) {
            $this->owners->add($owner);
        }

        return $this;
    }

    public function removeOwner(User $owner): self
    {
        $this->owners->removeElement($owner);

        return $this;
    }

    public function getAssignees(): Collection
    {
        return $this->assignees;
    }

    public function addAssignee(User $assignee): self
    {
        if (!$this->assignees->contains($assignee)) {
            $this->assignees->add($assignee);
        }

        return $this;
    }

    public function removeAssignee(User $assignee): self
    {
        $this->assignees->removeElement($assignee);

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }
}
