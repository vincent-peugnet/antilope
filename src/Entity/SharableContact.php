<?php

namespace App\Entity;

use App\Repository\SharableContactRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SharableContactRepository::class)
 */
class SharableContact extends Contact
{
    /**
     * @ORM\ManyToOne(targetEntity=Sharable::class, inversedBy="sharableContacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sharable;

    public function __construct()
    {
        parent::__construct();
    }

    public function getSharable(): ?Sharable
    {
        return $this->sharable;
    }

    public function setSharable(?Sharable $sharable): self
    {
        $this->sharable = $sharable;

        return $this;
    }
}
