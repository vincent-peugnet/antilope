<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Search
{
    const SORT_BY = ['id', 'createdAt', 'lastEditedAt', 'name'];

    /**
     * @var string
     */
    public $q = '';

    /**
     * @var int
     * @Assert\Choice({0, 1, -1})
     */
    public $disabled = 0;

    /**
     * @var string
     * @Assert\Choice(choices=Search::SORT_BY, message="choose a propriety to sort by")
     * @Assert\NotBlank
     */
    public $sortBy = 'id';

    /**
     * @var string
     * @Assert\Choice({"ASC", "DESC"})
     * @Assert\NotBlank
     */
    public $order = 'DESC';
}