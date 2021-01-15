<?php

namespace App\Entity;

use App\Repository\ValidationRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ValidationRepository::class)
 */
class Validation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Sharable::class, inversedBy="validations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sharable;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="validations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sendAt;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $message;

    public function __construct()
    {
        $this->sendAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getSendAt(): ?\DateTimeInterface
    {
        return $this->sendAt;
    }

    public function setSendAt(?\DateTimeInterface $sendAt): self
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
