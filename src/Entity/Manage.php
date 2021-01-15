<?php

namespace App\Entity;

use App\Repository\ManageRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ManageRepository::class)
 */
class Manage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="manages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Sharable::class, inversedBy="managedBy")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sharable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $contactable;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->contactable = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSharable(): ?Sharable
    {
        return $this->sharable;
    }

    public function setSharable(?Sharable $sharable): self
    {
        $this->sharable = $sharable;

        return $this;
    }

    public function getContactable(): ?bool
    {
        return $this->contactable;
    }

    public function setContactable(bool $contactable): self
    {
        $this->contactable = $contactable;

        return $this;
    }
}
