<?php

/**
 * This file is part of Antilope
 *
 * Antilope is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * PHP version 7.4
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Entity;

use App\Repository\SharableRepository;
use App\Service\FileUploader;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

    public const RADIUS_OPTIONS = [
        'No radius' => 0,
        '250m' => 250,
        '5km' => 5000,
    ];

    public static function interestedOptionsValues()
    {
        return array_values(self::INTERESTED_OPTIONS);
    }

    public static function radiusOptionsValues()
    {
        return array_values(self::RADIUS_OPTIONS);
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 2,
     *     max = 64,
     *     minMessage = "name at least {{ limit }} characters long",
     *     maxMessage = "name cannot be longer than {{ limit }} characters",
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
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 5,
     *     max = 255,
     *     minMessage = "description should be at least {{ limit }} characters long",
     *     maxMessage = "description cannot be longer than {{ limit }} characters",
     * )
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=UserClass::class, inversedBy="sharables")
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

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="sharables")
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cover;

    /**
     * @ORM\OneToMany(targetEntity=Bookmark::class, mappedBy="sharable", orphanRemoval=true)
     */
    private $bookmarks;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="sharable", orphanRemoval=true)
     */
    private $questions;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Range(
     *      min = -90,
     *      max =  90,
     *      notInRangeMessage = "Latitude must be between {{ min }} and {{ max }}",
     * )
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Range(
     *      min = -180,
     *      max =  180,
     *      notInRangeMessage = "Longitude must be between {{ min }} and {{ max }}",
     * )
     */
    private $longitude;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(callback="radiusOptionsValues")
     */
    private $radius;

    public function __construct()
    {
        $this->managedBy = new ArrayCollection();
        $this->validations = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->lastEditedAt = new DateTime();
        $this->disabled = false;
        $this->interestedMethod = 2;
        $this->interesteds = new ArrayCollection();
        $this->sharableContacts = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->radius = 0;
    }

    public function __toString(): string
    {
        return (string) $this->name;
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
            return ($manage->isContactable() && $manage->getConfirmed() && !$manage->getUser()->isDisabled());
        });
    }

    /**
     * Get all manage relation object that have confirmed
     *
     * @return Collection|Manage[]
     */
    public function getConfirmedManagers(): Collection
    {
        return $this->getManagedBy()->filter(function (Manage $manage) {
            return $manage->getConfirmed();
        });
    }

    /**
     * @return Collection|Manage[] Collection of Manage objects
     */
    public function getConfirmedNotDisabledManagers(): Collection
    {
        return $this->getManagedBy()->filter(function (Manage $manage) {
            return ($manage->getConfirmed() && !$manage->getUser()->isDisabled());
        });
    }

    /**
     * @return Collection|Manage[] Collection of Manage objects
     */
    public function getConfirmedOnymousManagers(): Collection
    {
        return $this->getManagedBy()->filter(function (Manage $manage) {
            return ($manage->getConfirmed() && !$manage->isAnonymous());
        });
    }

    /**
     * @return Collection Collection of User objects
     */
    public function getAnonymousManagerUsers(): Collection
    {
        $manages = $this->getManagedBy()->filter(function (Manage $manage) {
            return ($manage->getConfirmed() && $manage->isAnonymous());
        });
        return $manages->map(function (Manage $manage) {
            return $manage->getUser();
        });
    }

    /**
     * @return bool True if at least one confirmed not disabled manager
     */
    public function isAccessible(): bool
    {
        return ($this->getConfirmedNotDisabledManagers()->count() > 0);
    }

    public function isContactable(): bool
    {
        $contactableManagers = $this->getConfirmedNotDisabledManagers()->filter(function (Manage $manage) {
            return $manage->getUser()->isContactable();
        });
        return (!$this->getSharableContacts()->isEmpty() || !$contactableManagers->isEmpty());
    }

    public function getCoverPath(): string
    {
        return FileUploader::COVER . '/' . $this->cover;
    }

    public function isGeo(): bool
    {
        return (!is_null($this->latitude) && !is_null($this->longitude));
    }

    // _______________ callbacks _____________________


    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if (!is_null($this->latitude) && is_null($this->longitude)) {
            $context->buildViolation('You should include both latitude and longitude')
                ->atPath('longitude')
                ->addViolation();
        }

        if (!is_null($this->longitude) && is_null($this->latitude)) {
            $context->buildViolation('You should include both latitude and longitude')
                ->atPath('latitude')
                ->addViolation();
        }
    }

    // _______________ setters and getters  _____________________


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

    public function isDisabled(): ?bool
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

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * @return Collection|Bookmark[]
     */
    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks[] = $bookmark;
            $bookmark->setSharable($this);
        }

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        if ($this->bookmarks->removeElement($bookmark)) {
            // set the owning side to null (unless already changed)
            if ($bookmark->getSharable() === $this) {
                $bookmark->setSharable(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setSharable($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getSharable() === $this) {
                $question->setSharable(null);
            }
        }

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getRadius(): ?int
    {
        return $this->radius;
    }

    public function setRadius(int $radius): self
    {
        $this->radius = $radius;

        return $this;
    }
}
