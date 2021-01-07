<?php

namespace App\Entity;

use App\Repository\InterestedRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InterestedRepository::class)
 */
class Interested
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="interesteds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Sharable::class, inversedBy="interesteds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sharable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $reviewed;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->reviewed = false;
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

    public function getReviewed(): ?bool
    {
        return $this->reviewed;
    }

    public function setReviewed(bool $reviewed): self
    {
        $this->reviewed = $reviewed;

        return $this;
    }
}
