<?php

namespace App\Entity;

use App\Repository\UserContactRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserContactRepository::class)
 */
class UserContact extends Contact
{


    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userContacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $disabled;

    public function __construct()
    {
        parent::__construct();
        $this->disabled = false;
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

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }
}
