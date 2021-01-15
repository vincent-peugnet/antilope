<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SharableSearch
{
    public const DISABLED = [
        'only disabled' => 1,
        'hide disabled' => 0,
    ];
    public const VALIDATED = [
        'I validated' => 1,
        'I did not validate' => 0,
    ];
    public const SORT_BY = [
        'id' => 'id',
        'createdAt' => 'createdAt',
        'lastEditedAt' => 'lastEditedAt',
        'name' => 'name',
    ];
    public const ORDER = [
        'ascending' => 'ASC',
        'descending' => 'DESC',
    ];

    /**
     * @var string|null
     */
    private $query = '';

    /**
     * @var bool|null
     */
    private $disabled = false;

    /**
     * @var bool|null
     */
    private $validated = null;

    /**
     * @var int|null
     */
    private $managedBy = null;

    /**
     * @var string|null
     * @Assert\Choice(callback="useSortBy")

     */
    private $sortBy = 'id';

    /**
     * @var string|null
     * @Assert\Choice(callback="useOrder", message="Order should be ASC or DESC")
     */
    private $order = 'DESC';



    public static function useSortBy(): array
    {
        return array_values(self::SORT_BY);
    }

    public static function useOrder(): array
    {
        return array_values(self::ORDER);
    }



    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(?bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(?bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    public function getManagedBy(): ?int
    {
        return $this->managedBy;
    }

    public function setManagedBy(?int $managedBy): self
    {
        $this->managedBy = $managedBy;

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(?string $sortBy): self
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getOrder(): ?string
    {
        return $this->order;
    }

    public function setOrder(?string $order): self
    {
        $this->order = $order;

        return $this;
    }
}
