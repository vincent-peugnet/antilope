<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SharableSearch
{
    const DISABLED = [
        'only disabled' => 1,
        'hide disabled' => 0,
        'show all' => -1,
    ];
    const VALIDATED = [
        'already validated' => 1,
        'not validated' => 0,
        'show all' => -1,
    ];
    const SORT_BY = [
        'id' => 'id',
        'createdAt' => 'createdAt',
        'lastEditedAt' => 'lastEditedAt',
        'name' => 'name',
    ];
    const ORDER = [
        'ascending' => 'ASC',
        'descending' => 'DESC',
    ];

    /**
     * @var string
     */
    public $q = '';

    /**
     * @var int
     * @Assert\Choice(callback="useDisabled")
     */
    public $disabled = 0;

    /**
     * @var int
     * @Assert\Choice(callback="useValidated")
     */
    public $validated = -1;

    /**
     * @var string
     * @Assert\Choice(callback="useSortBy")

     */
    public $sortBy = 'id';

    /**
     * @var string
     * @Assert\Choice(callback="useOrder")
     * @Assert\NotBlank
     */
    public $order = 'DESC';

    public static function useDisabled(): array
    {
        return array_values(self::DISABLED);
    }

    public static function useValidated(): array
    {
        return array_values(self::VALIDATED);
    }

    public static function useSortBy(): array
    {
        return array_values(self::SORT_BY);
    }

    public static function useOrder(): array
    {
        return array_values(self::ORDER);
    }
}