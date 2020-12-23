<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserClass::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userClass;

    /**
     * @ORM\Column(type="integer")
     */
    private $shareScore;

    /**
     * @ORM\ManyToMany(targetEntity=Sharable::class, mappedBy="managedBy")
     */
    private $sharables;

    /**
     * @ORM\OneToMany(targetEntity=Validation::class, mappedBy="user")
     */
    private $validations;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(callback={"App\Security\Voter\UserVoter", "getParanoiaLevels"})
     */
    private $paranoia;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="parent")
     */
    private $invitations;

    /**
     * @ORM\OneToOne(targetEntity=Invitation::class, mappedBy="child", cascade={"persist", "remove"})
     */
    private $invitation;

    public function __construct()
    {
        $this->sharables = new ArrayCollection();
        $this->validations = new ArrayCollection();
        $this->invitations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAt()
    {
        $this->createdAt = new DateTime();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUserClass(): ?UserClass
    {
        return $this->userClass;
    }

    public function setUserClass(?UserClass $userClass): self
    {
        $this->userClass = $userClass;

        return $this;
    }

    public function getShareScore(): ?int
    {
        return $this->shareScore;
    }

    public function setShareScore(int $shareScore): self
    {
        $this->shareScore = $shareScore;

        return $this;
    }

    /**
     * @return Collection|Sharable[]
     */
    public function getSharables(): Collection
    {
        return $this->sharables;
    }

    public function addSharable(Sharable $sharable): self
    {
        if (!$this->sharables->contains($sharable)) {
            $this->sharables[] = $sharable;
            $sharable->addManagedBy($this);
        }

        return $this;
    }

    public function removeSharable(Sharable $sharable): self
    {
        if ($this->sharables->removeElement($sharable)) {
            $sharable->removeManagedBy($this);
        }

        return $this;
    }

    /**
     * @return Collection|Validation[]
     */
    public function getValidations(): Collection
    {
        return $this->validations;
    }

    public function addValidation(Validation $validation): self
    {
        if (!$this->validations->contains($validation)) {
            $this->validations[] = $validation;
            $validation->setUser($this);
        }

        return $this;
    }

    public function removeValidation(Validation $validation): self
    {
        if ($this->validations->removeElement($validation)) {
            // set the owning side to null (unless already changed)
            if ($validation->getUser() === $this) {
                $validation->setUser(null);
            }
        }

        return $this;
    }

    public function getParanoia(): ?int
    {
        return $this->paranoia;
    }

    public function setParanoia(int $paranoia): self
    {
        $this->paranoia = $paranoia;

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setParent($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getParent() === $this) {
                $invitation->setParent(null);
            }
        }

        return $this;
    }

    public function getInvitation(): ?Invitation
    {
        return $this->invitation;
    }

    public function setInvitation(?Invitation $invitation): self
    {
        // unset the owning side of the relation if necessary
        if ($invitation === null && $this->invitation !== null) {
            $this->invitation->setChild(null);
        }

        // set the owning side of the relation if necessary
        if ($invitation !== null && $invitation->getChild() !== $this) {
            $invitation->setChild($this);
        }

        $this->invitation = $invitation;

        return $this;
    }
}
