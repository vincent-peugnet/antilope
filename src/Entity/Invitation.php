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

use App\Repository\InvitationRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvitationRepository::class)
 */
class Invitation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="invitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parent;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="invitation", cascade={"persist", "remove"})
     */
    private $child;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $code;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getParent(): ?User
    {
        return $this->parent;
    }

    public function setParent(?User $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getChild(): ?User
    {
        return $this->child;
    }

    public function setChild(?User $child): self
    {
        $this->child = $child;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
