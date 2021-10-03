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
 * PHP version 7.2
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Entity;

use App\Repository\UserClassRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use App\Security\Voter\UserVoter;
use DateInterval;
use DateTime;
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
    private bool $share = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $access = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $canInvite = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canQuestion = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canSetVisibleBy = false;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice(callback={"App\Security\Voter\UserVoter", "getParanoiaLevels"})
     */
    private $maxParanoia;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 0,
     *      max = 8760,
     *      notInRangeMessage = "must be beetwen {{ min }} and {{ max }}.",
     * )
     */
    private $inviteFrequency;

    /**
     * @ORM\Column(type="integer")
     */
    private $shareScoreReq;

    /**
     * @ORM\Column(type="integer")
     */
    private $accountAgeReq;

    /**
     * @ORM\Column(type="integer")
     */
    private $validatedReq;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verifiedReq;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastEditedAt;

    /**
     * @ORM\OneToOne(targetEntity=UserClass::class, inversedBy="prev", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $next;

    /**
     * @ORM\OneToOne(targetEntity=UserClass::class, mappedBy="next", cascade={"persist"})
     */
    private $prev;

    /**
     * @ORM\OneToMany(targetEntity=Sharable::class, mappedBy="visibleBy")
     */
    private $sharables;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 0,
     *      max = 3650,
     *      notInRangeMessage = "must be beetwen {{ min }} and {{ max }} days.",
     * )
     */
    private $maxInactivity;

    /**
     * @ORM\Column(type="integer")
     */
    private $manageReq;

    /**
     * @ORM\Column(type="boolean")
     */
    private $avatarReq;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visibleBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canReport = true;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $paranoiaLevels = UserVoter::getParanoiaLevels();
        $this->maxParanoia = end($paranoiaLevels);
        $this->maxInactivity = 0;
        $this->inviteFrequency = 0;
        $this->shareScoreReq = 0;
        $this->accountAgeReq = 0;
        $this->validatedReq = 0;
        $this->manageReq = 0;
        $this->verifiedReq = false;
        $this->avatarReq = false;
        $this->visibleBy = false;
        $this->createdAt = new DateTime();
        $this->lastEditedAt = $this->createdAt;
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getInviteFrequency(): ?int
    {
        return $this->inviteFrequency;
    }

    public function setInviteFrequency(int $inviteFrequency): self
    {
        $this->inviteFrequency = $inviteFrequency;

        return $this;
    }

    public function getShareScoreReq(): ?int
    {
        return $this->shareScoreReq;
    }

    public function setShareScoreReq(int $shareScoreReq): self
    {
        $this->shareScoreReq = $shareScoreReq;

        return $this;
    }

    public function getAccountAgeReq(): ?int
    {
        return $this->accountAgeReq;
    }

    public function setAccountAgeReq(int $accountAgeReq): self
    {
        $this->accountAgeReq = $accountAgeReq;

        return $this;
    }

    public function getValidatedReq(): ?int
    {
        return $this->validatedReq;
    }

    public function setValidatedReq(int $validatedReq): self
    {
        $this->validatedReq = $validatedReq;

        return $this;
    }

    public function getVerifiedReq(): ?bool
    {
        return $this->verifiedReq;
    }

    public function setVerifiedReq(bool $verifiedReq): self
    {
        $this->verifiedReq = $verifiedReq;

        return $this;
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

    public function getLastEditedAt(): ?\DateTimeInterface
    {
        return $this->lastEditedAt;
    }

    public function setLastEditedAt(\DateTimeInterface $lastEditedAt): self
    {
        $this->lastEditedAt = $lastEditedAt;

        return $this;
    }

    public function getNext(): ?self
    {
        return $this->next;
    }

    public function setNext(?self $next): self
    {
        $this->next = $next;

        return $this;
    }

    public function getPrev(): ?self
    {
        return $this->prev;
    }

    public function setPrev(?self $prev): self
    {
        // unset the owning side of the relation if necessary
        if ($prev === null && $this->prev !== null) {
            $this->prev->setNext(null);
        }

        // set the owning side of the relation if necessary
        if ($prev !== null && $prev->getNext() !== $this) {
            $prev->setNext($this);
        }

        $this->prev = $prev;

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
            $sharable->setVisibleBy($this);
        }

        return $this;
    }

    public function removeSharable(Sharable $sharable): self
    {
        if ($this->sharables->removeElement($sharable)) {
            // set the owning side to null (unless already changed)
            if ($sharable->getVisibleBy() === $this) {
                $sharable->setVisibleBy(null);
            }
        }

        return $this;
    }

    public function getMaxInactivity(): ?int
    {
        return $this->maxInactivity;
    }

    public function getMaxInactivityTime(): DateInterval
    {
        return new DateInterval('P' . $this->maxInactivity . 'D');
    }

    public function setMaxInactivity(int $maxInactivity): self
    {
        $this->maxInactivity = $maxInactivity;

        return $this;
    }

    public function getCanQuestion(): ?bool
    {
        return $this->canQuestion;
    }

    public function setCanQuestion(bool $canQuestion): self
    {
        $this->canQuestion = $canQuestion;

        return $this;
    }

    public function getManageReq(): ?int
    {
        return $this->manageReq;
    }

    public function setManageReq(int $manageReq): self
    {
        $this->manageReq = $manageReq;

        return $this;
    }

    public function getCanSetVisibleBy(): ?bool
    {
        return $this->canSetVisibleBy;
    }

    public function setCanSetVisibleBy(bool $canSetVisibleBy): self
    {
        $this->canSetVisibleBy = $canSetVisibleBy;

        return $this;
    }

    // ____________ special functions

    /**
     * @return Collection|User[] List of all not disabled users
     */
    public function getNotDisabledUsers(): Collection
    {
        return $this->users->filter(function (User $user) {
            return !$user->isDisabled();
        });
    }

    public function getAvatarReq(): ?bool
    {
        return $this->avatarReq;
    }

    public function setAvatarReq(bool $avatarReq): self
    {
        $this->avatarReq = $avatarReq;

        return $this;
    }

    public function getVisibleBy(): ?bool
    {
        return $this->visibleBy;
    }

    public function setVisibleBy(bool $visibleBy): self
    {
        $this->visibleBy = $visibleBy;

        return $this;
    }

    public function getCanReport(): ?bool
    {
        return $this->canReport;
    }

    public function setCanReport(bool $canReport): self
    {
        $this->canReport = $canReport;

        return $this;
    }
}
