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
