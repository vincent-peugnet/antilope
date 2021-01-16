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
     * @Assert\Range(
     *      min = 0,
     *      max = 100,
     *      notInRangeMessage = "Rank must be beetwen {{ min }} and {{ max }}.",
     * )
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

    /**
     * @ORM\Column(type="smallint")
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

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->shareScoreReq = 0;
        $this->accountAgeReq = 0;
        $this->validatedReq = 0;
        $this->verifiedReq = false;
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
}
