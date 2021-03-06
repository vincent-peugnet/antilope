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

namespace App\Service;

use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
use App\Repository\UserClassRepository;

class SharePoints
{
    private UserClassRepository $userClassRepository;

    public function __construct(UserClassRepository $userClassRepository)
    {
        $this->userClassRepository = $userClassRepository;
    }

    /**
     * Indicate the Rank of an userClass in percent
     */
    public function rank(UserClass $userClass): int
    {
        $position = count($this->userClassRepository->findLowerthan($userClass));
        $total = count($this->userClassRepository->findAll());
        $rank = round($position / $total * 100);
        return (int) $rank;
    }

    public function calculate(User $user, Sharable $sharable): int
    {
        $userRank = $this->rank($user->getUserClass());
        $validationCount = count($sharable->getValidations());
        $managedBy = $sharable->getManagedBy();
        $managerCount = count($managedBy);

        $rankPoint = 100 * log10($userRank + 11) - 100;
        $validationRatio = ( 4 / ( $validationCount + 1 ));
        $points = round($rankPoint * $validationRatio / $managerCount);

        return (int) $points;
    }
}
