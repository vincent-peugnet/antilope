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

use App\Repository\ReportSharableRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportSharableRepository::class)
 */
class ReportSharable extends Report
{

    /**
     * @ORM\ManyToMany(targetEntity=Rule::class, inversedBy="reportSharables")
     */
    protected $rules;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reportSharables")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity=Sharable::class, inversedBy="report")
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
