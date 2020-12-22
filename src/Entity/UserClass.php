<?php

namespace App\Entity;

use App\Repository\UserClassRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserClassRepository::class)
 */
class UserClass
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $rank;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="userClass")
     */
    private $users;

    /**
     * @ORM\Column(type="boolean")
     */
    private $share;

    /**
     * @ORM\Column(type="boolean")
     */
    private $access;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canInvite;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(callback={"App\Security\Voter\UserVoter", "getParanoiaLevels"})
     */
    private $maxParanoia;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setUserClass($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getUserClass() === $this) {
                $user->setUserClass(null);
            }
        }

        return $this;
    }

    public function getShare(): ?bool
    {
        return $this->share;
    }

    public function setShare(bool $share): self
    {
        $this->share = $share;

        return $this;
    }

    public function getAccess(): ?bool
    {
        return $this->access;
    }

    public function setAccess(bool $access): self
    {
        $this->access = $access;

        return $this;
    }

    public function getCanInvite(): ?bool
    {
        return $this->canInvite;
    }

    public function setCanInvite(bool $canInvite): self
    {
        $this->canInvite = $canInvite;

        return $this;
    }

    public function getMaxParanoia(): ?int
    {
        return $this->maxParanoia;
    }

    public function setMaxParanoia(int $maxParanoia): self
    {
        $this->maxParanoia = $maxParanoia;

        return $this;
    }
}
