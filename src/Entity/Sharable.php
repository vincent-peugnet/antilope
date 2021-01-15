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
    public const INTERESTED_OPTIONS = [
        '1 No need' => 1,
        '2 Automatic' => 2,
        '3 Manual' => 3,
        '4 Never' => 4,
    ];

    public static function interestedOptionsValues()
    {
        return array_values(self::INTERESTED_OPTIONS);
    }

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

    /**
     * @ORM\Column(type="boolean")
     */
    private $responsibility;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastEditedAt;

    /**
     * @ORM\OneToMany(targetEntity=Manage::class, mappedBy="sharable")
     */
    private $managedBy;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(callback="interestedOptionsValues")
     */
    private $interestedMethod;

    /**
     * @ORM\OneToMany(targetEntity=Interested::class, mappedBy="sharable")
     */
    private $interesteds;

    /**
     * @ORM\OneToMany(targetEntity=SharableContact::class, mappedBy="sharable")
     */
    private $sharableContacts;

    public function __construct()
    {
        $this->managedBy = new ArrayCollection();
        $this->validations = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->lastEditedAt = new DateTime();
        $this->responsibility = true;
        $this->interestedMethod = 2;
        $this->interesteds = new ArrayCollection();
        $this->sharableContacts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
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

    public function getResponsibility(): ?bool
    {
        return $this->responsibility;
    }

    public function setResponsibility(bool $responsibility): self
    {
        $this->responsibility = $responsibility;

        return $this;
    }

    public function getLastEditedAt(): ?\DateTimeInterface
    {
        return $this->lastEditedAt;
    }

    public function setLastEditedAt(\DateTimeInterface $lastEditedAt): self
    {
        $this->lastEditedAt = $lastEditedAt;

        return $this;
    }

    /**
     * @return Collection|Manage[]
     */
    public function getManagedBy(): Collection
    {
        return $this->managedBy;
    }

    public function addManagedBy(Manage $managedBy): self
    {
        if (!$this->managedBy->contains($managedBy)) {
            $this->managedBy[] = $managedBy;
            $managedBy->setSharable($this);
        }

        return $this;
    }

    public function removeManagedBy(Manage $managedBy): self
    {
        if ($this->managedBy->removeElement($managedBy)) {
            // set the owning side to null (unless already changed)
            if ($managedBy->getSharable() === $this) {
                $managedBy->setSharable(null);
            }
        }

        return $this;
    }

    public function getInterestedMethod(): ?int
    {
        return $this->interestedMethod;
    }

    public function setInterestedMethod(int $interestedMethod): self
    {
        $this->interestedMethod = $interestedMethod;

        return $this;
    }

    /**
     * Return only real interested Objects, users that validated are removed from this selection
     *
     * @return Collection|Interested[]
     */
    public function getInteresteds(): Collection
    {
        return $this->interesteds->filter(function (Interested $interested) {
            return !$this->getValidatedBy()->contains($interested->getUser());
        });
    }

    public function addInterested(Interested $interested): self
    {
        if (!$this->interesteds->contains($interested)) {
            $this->interesteds[] = $interested;
            $interested->setSharable($this);
        }

        return $this;
    }

    public function removeInterested(Interested $interested): self
    {
        if ($this->interesteds->removeElement($interested)) {
            // set the owning side to null (unless already changed)
            if ($interested->getSharable() === $this) {
                $interested->setSharable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SharableContact[]
     */
    public function getSharableContacts(): Collection
    {
        return $this->sharableContacts;
    }

    public function addSharableContact(SharableContact $sharableContact): self
    {
        if (!$this->sharableContacts->contains($sharableContact)) {
            $this->sharableContacts[] = $sharableContact;
            $sharableContact->setSharable($this);
        }

        return $this;
    }

    public function removeSharableContact(SharableContact $sharableContact): self
    {
        if ($this->sharableContacts->removeElement($sharableContact)) {
            // set the owning side to null (unless already changed)
            if ($sharableContact->getSharable() === $this) {
                $sharableContact->setSharable(null);
            }
        }

        return $this;
    }

    //_______________ special functions _______________

    /**
     * Get the Users that validated the Sharable
     *
     * @return Collection|User[]
     */
    public function getValidatedBy(): Collection
    {
        return $this->validations->map(function (Validation $validation) {
            return $validation->getUser();
        });
    }

    /**
     * Get all manage relation object that allow contact
     *
     * @return Collection|Manage[]
     */
    public function getContactableManagers(): Collection
    {
        return $this->getManagedBy()->filter(function (Manage $manage) {
            return $manage->getContactable();
        });
    }
}
