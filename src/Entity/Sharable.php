<?php

namespace App\Entity;

use App\Repository\SharableRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=SharableRepository::class)
 */
class Sharable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Length(
     *     min = 2,
     *     max = 64,
     *     minMessage = "name at least {{ limit }} characters long",
     *     maxMessage = "name cannot be longer than {{ limit }} characters",
     *     allowEmptyString = false
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\GreaterThan(
     *      "today UTC",
     *      message = "Begin date must be after today"
     * )
     */
    private $beginAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\GreaterThan(
     *      propertyPath = "beginAt",
     *      message = "End date must be after begin date"
     * )
     * @Assert\GreaterThan(
     *      "today UTC",
     *      message = "End date must be after today"
     * )
     */
    private $endAt;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="sharables")
     */
    private $managedBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $disabled;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *     min = 5,
     *     max = 255,
     *     minMessage = "description should be at least {{ limit }} characters long",
     *     maxMessage = "description cannot be longer than {{ limit }} characters",
     *     allowEmptyString = false
     * )
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserClass::class)
     */
    private $visibleBy;

    /**
     * @ORM\OneToMany(targetEntity=Validation::class, mappedBy="sharable")
     */
    private $validations;

    /**
     * @ORM\Column(type="text")
     */
    private $details;

    public function __construct()
    {
        $this->managedBy = new ArrayCollection();
        $this->validations = new ArrayCollection();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTimeInterface $beginAt): self
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getManagedBy(): Collection
    {
        return $this->managedBy;
    }

    public function addManagedBy(User $managedBy): self
    {
        if (!$this->managedBy->contains($managedBy)) {
            $this->managedBy[] = $managedBy;
        }

        return $this;
    }

    public function removeManagedBy(User $managedBy): self
    {
        $this->managedBy->removeElement($managedBy);

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVisibleBy(): ?UserClass
    {
        return $this->visibleBy;
    }

    public function setVisibleBy(?UserClass $visibleBy): self
    {
        $this->visibleBy = $visibleBy;

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
            $validation->setSharable($this);
        }

        return $this;
    }

    public function removeValidation(Validation $validation): self
    {
        if ($this->validations->removeElement($validation)) {
            // set the owning side to null (unless already changed)
            if ($validation->getSharable() === $this) {
                $validation->setSharable(null);
            }
        }

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }
}
