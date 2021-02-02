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

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class Inactivity
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Check if an user need to be disabled beccause of inactivity
     */
    public function check(User $user): bool
    {
        if ($user->getUserClass()->getMaxInactivity() && !$user->isDisabled()) {
            $limit = new DateTime();
            $limit->sub($user->getUserClass()->getMaxInactivityTime());
            return ($user->getLastActivity() < $limit);
        } else {
            return false;
        }
    }

    public function checkUpdate(User &$user): bool
    {
        if ($this->check($user)) {
            $user->setDisabled(true);
            $this->em->persist($user);
            $this->em->flush();
            return true;
        } else {
            return false;
        }
    }
}
