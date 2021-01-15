<?php

namespace App\Entity;

use App\Repository\InvitationRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvitationRepository::class)
 */
class Invitation
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parent;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="invitation", cascade={"persist", "remove"})
     */
    private $child;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $code;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getParent(): ?User
    {
        return $this->parent;
    }

    public function setParent(?User $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChild(): ?User
    {
        return $this->child;
    }

    public function setChild(?User $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
