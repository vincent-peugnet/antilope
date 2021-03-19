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

use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Sharable;
use App\Entity\User;

class QuestionSearch
{
    public const SORT_BY = [
        'id' => 'id',
        'createdAt' => 'createdAt',
    ];
    public const ORDER = [
        'ascending' => 'ASC',
        'descending' => 'DESC',
    ];

    /**
     * @var string|null
     */
    private $query = '';

    private bool $onlyNotAnswered = false;

    /**
     * @var Sharable|null
     */
    private $sharable = null;

    /**
     * @var User|null
     */
    private $user = null;

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

    public function __construct()
    {
    }

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

    public function getOnlyNotAnswered(): bool
    {
        return $this->onlyNotAnswered;
    }

    public function setOnlyNotAnswered(bool $onlyNotAnswered): self
    {
        $this->onlyNotAnswered = $onlyNotAnswered;

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

    public function getSharable(): ?Sharable
    {
        return $this->sharable;
    }

    public function setSharable(?Sharable $sharable): self
    {
        $this->sharable = $sharable;

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
