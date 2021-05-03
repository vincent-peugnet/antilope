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

use App\Repository\UserRepository;
use App\Service\FileUploader;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email adress")
 */
class User implements UserInterface
{

    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Regex(
     *  pattern = "/^[a-z0-9A-Z]+(?:-[a-z0-9A-Z]+)*$/",
     *  message = "User name should only contain a-z, A-Z, 0-9 separated only by - or _"
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 30,
     *      minMessage = "Your user name must be at least {{ limit }} characters long",
     *      maxMessage = "Your user name cannot be longer than {{ limit }} characters"
     * )
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

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Manage::class, mappedBy="user")
     */
    private $manages;

    /**
     * @ORM\OneToMany(targetEntity=UserContact::class, mappedBy="user")
     */
    private $userContacts;

    /**
     * @ORM\OneToMany(targetEntity=Interested::class, mappedBy="user")
     */
    private $interesteds;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastActivity;

    /**
     * @ORM\Column(type="boolean")
     */
    private $disabled;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity=Bookmark::class, mappedBy="user", orphanRemoval=true)
     */
    private $bookmarks;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="user", orphanRemoval=true)
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="user", orphanRemoval=true)
     */
    private $answers;

    public function __construct()
    {
        $this->disabled = false;
        $this->validations = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->lastActivity = new DateTime();
        $this->shareScore = 0;
        $this->paranoia = 0;
        $this->manages = new ArrayCollection();
        $this->userContacts = new ArrayCollection();
        $this->interesteds = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->answers = new ArrayCollection();
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
        $roles[] = self::ROLE_USER;

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
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function addShareScore(int $shareScore): self
    {
        $this->shareScore += $shareScore;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|Manage[]
     */
    public function getManages(): Collection
    {
        return $this->manages;
    }

    public function addManage(Manage $manage): self
    {
        if (!$this->manages->contains($manage)) {
            $this->manages[] = $manage;
            $manage->setUser($this);
        }

        return $this;
    }

    /**
     * @return Collection|UserContact[]
     */
    public function getUserContacts(): Collection
    {
        return $this->userContacts;
    }

    public function addUserContact(UserContact $userContact): self
    {
        if (!$this->userContacts->contains($userContact)) {
            $this->userContacts[] = $userContact;
            $userContact->setUser($this);
        }

        return $this;
    }

    public function removeUserContact(UserContact $userContact): self
    {
        if ($this->userContacts->removeElement($userContact)) {
            // set the owning side to null (unless already changed)
            if ($userContact->getUser() === $this) {
                $userContact->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Interested[]
     */
    public function getInteresteds(): Collection
    {
        return $this->interesteds;
    }

    public function addInterested(Interested $interested): self
    {
        if (!$this->interesteds->contains($interested)) {
            $this->interesteds[] = $interested;
            $interested->setUser($this);
        }

        return $this;
    }

    public function removeInterested(Interested $interested): self
    {
        if ($this->interesteds->removeElement($interested)) {
            // set the owning side to null (unless already changed)
            if ($interested->getUser() === $this) {
                $interested->setUser(null);
            }
        }

        return $this;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(\DateTimeInterface $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return bool if user is admin
     */
    public function isAdmin(): bool
    {
        return (in_array(self::ROLE_ADMIN, $this->roles));
    }

    public function setAdmin(bool $admin): self
    {
        if ($admin) {
            $this->roles[] = self::ROLE_ADMIN;
            $this->roles = array_unique($this->roles);
        } else {
            $this->roles = array_filter($this->roles, function (string $role) {
                return $role !== self::ROLE_ADMIN;
            });
        }

        return $this;
    }

    public function getAvatarPath(): string
    {
        return FileUploader::AVATAR . '/' . $this->avatar;
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
            $bookmark->setUser($this);
        }

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        if ($this->bookmarks->removeElement($bookmark)) {
            // set the owning side to null (unless already changed)
            if ($bookmark->getUser() === $this) {
                $bookmark->setUser(null);
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
            $question->setUser($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getUser() === $this) {
                $question->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setUser($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getUser() === $this) {
                $answer->setUser(null);
            }
        }

        return $this;
    }

    //_______________ special functions _______________

    /**
     * @return Collection|Sharable[] ArrayCollection of Sharable objects
     */
    public function getInterestedSharables(): Collection
    {
        return $this->interesteds->map(function (Interested $interested) {
            return $interested->getSharable();
        });
    }

    /**
     * Get Real Managed list
     *
     * @return Collection|Manage[]
     */
    public function getConfirmedManages(): Collection
    {
        return $this->manages->filter(function (Manage $manage) {
            return $manage->getConfirmed();
        });
    }

    public function getConfirmedEnabledManages(): Collection
    {
        return $this->manages->filter(function (Manage $manage) {
            return (
                $manage->getConfirmed() &&
                !$manage->getSharable()->isDisabled()
            );
        });
    }

    /**
     * Get invitations to manage sharables
     *
     * @return Collection|Manage[]
     */
    public function getUnconfirmedManages(): Collection
    {
        return $this->manages->filter(function (Manage $manage) {
            return !$manage->getConfirmed();
        });
    }

    /**
     * Get confirmed not anonymous managed list
     *
     * @return Collection|Manage[]
     */
    public function getConfirmedOnymousManages(): Collection
    {
        return $this->manages->filter(function (Manage $manage) {
            return $manage->getConfirmed() && !$manage->isAnonymous();
        });
    }

    public function getNotForgottenUserContacts()
    {
        return $this->userContacts->filter(function (UserContact $userContact) {
            return !$userContact->isForgotten();
        });
    }

    public function isContactable(): bool
    {
        return !$this->getNotForgottenUserContacts()->isEmpty();
    }
}
