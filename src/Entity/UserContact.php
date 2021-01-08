<?php

namespace App\Entity;

use App\Repository\UserContactRepository;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @ORM\Entity(repositoryClass=UserContactRepository::class)
 */
class UserContact
{
    const EMAIL = 'email';
    const PHONE = 'phone';
    const URL = 'url';
    const ADRESS = 'adress';
    const OTHER = 'other';

    static public function allowedTypes(): array
    {
        return [
            'Email' => self::EMAIL,
            'Phone number' => self::PHONE,
            'URL adress' => self::URL,
            'Adress' => self::ADRESS,
            'Other' => self::OTHER,
        ];
    }

    static public function allowedTypeValues(): array
    {
        return array_values(self::allowedTypes());
    }


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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userContacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\Choice(callback="allowedTypeValues", message="Type does not exist")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "must be at least {{ limit }} characters long",
     *      maxMessage = "cannot be longer than {{ limit }} characters"
     * )
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $info;

    /**
     * @ORM\Column(type="boolean")
     */
    private $disabled;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->disabled = false;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
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
