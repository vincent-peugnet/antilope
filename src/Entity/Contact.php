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

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

abstract class Contact
{
    public const EMAIL = 'email';
    public const PHONE = 'phone';
    public const URL = 'url';
    public const MATRIX = 'matrix';
    public const ADRESS = 'address';
    public const FACEBOOK = 'facebook';
    public const OTHER = 'other';

    public static function allowedTypes(): array
    {
        return [
            'Email' => self::EMAIL,
            'Phone number' => self::PHONE,
            'Matrix ID' => self::MATRIX,
            'URL adress' => self::URL,
            'Address' => self::ADRESS,
            'Facebook' => self::FACEBOOK,
            'Other' => self::OTHER,
        ];
    }


    public static function allowedTypeValues(): array
    {
        return array_values(self::allowedTypes());
    }


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastEditedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $forgottenAt = null;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Choice(callback="allowedTypeValues", message="Type does not exist")
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "must be at least {{ limit }} characters long",
     *      maxMessage = "cannot be longer than {{ limit }} characters"
     * )
     */
    protected $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $info;


    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->lastEditedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getForgottenAt(): ?DateTime
    {
        return $this->forgottenAt;
    }

    public function setForgottenAt(?DateTime $forgottenAt): self
    {
        $this->forgottenAt = $forgottenAt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    //_______________ special functions _______________

    public function isForgotten(): bool
    {
        return !is_null($this->forgottenAt);
    }

    /**
     * @Assert\Callback
     */
    public function validateContent(ExecutionContextInterface $context, $payload)
    {
        $violation = [];
        switch ($this->type) {
            case self::URL:
                $constraint = new Url();
                break;

            case self::EMAIL:
                $constraint = new Email();
                break;

            case self::PHONE:
                if (!is_numeric($this->content)) {
                    $message = 'Phone number should only contain numerical characters';
                }
                break;

            case self::MATRIX:
                if (!preg_match('#^\@[a-zA-Z0-9-_]+\:([a-zA-Z0-9-_.]+\.[a-zA-Z0-9-_.]+)+$#', $this->content)) {
                    $message = 'Matrix ID should use the form @username:server.net';
                }
                break;
        

            case self::FACEBOOK:
                if (!preg_match('#^https?:\/\/(www.)?facebook.com\/\S+#', $this->content)) {
                    $message = 'Facebook link must start with https://facebook.com';
                }
                break;
        }

        if (isset($constraint)) {
            $validator = Validation::createValidator();
            $violations = $validator->validate($this->content, $constraint);

            if (0 !== count($violations)) {
                foreach ($violations as $violation) {
                    $context->buildViolation($violation->getMessage())
                    ->atPath('content')
                    ->addViolation();
                }
            }
        }

        if (isset($message)) {
            $context->buildViolation($message)
            ->atPath('content')
            ->addViolation();
        }
    }
}
