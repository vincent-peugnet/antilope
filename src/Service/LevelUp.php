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
use App\Entity\UserClass;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class LevelUp
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Call the Check method and persist User in case of promotion
     */
    public function checkUpdate(User $user): User
    {
        $needUpdate = $this->check($user);
        if ($needUpdate) {
            $this->em->persist($user);
            $this->em->flush();
        }
        return $user;
    }

    /**
     * Check for promotion or demotion
     * @param User $user the User to check, passed by reference and modified if promoting or demoting is needed
     * @return bool True in case of userClass change
     */
    public function check(User &$user): bool
    {
        $originalUserClass = $user->getUserClass();
        $user = $this->checkDown($user);
        $user = $this->checkUp($user);
        return $user->getUserClass() !== $originalUserClass;
    }

    /**
     * Check if the User can level Up and update It
     *
     * @param User $user
     * @return User the updated or not user
     */
    public function checkUp(User $user): User
    {
        $userClass = $user->getUserClass()->getNext();
        if ($userClass) {
            if (
                $this->shareScore($user, $userClass) &&
                $this->accountAge($user, $userClass) &&
                $this->validated($user, $userClass) &&
                $this->manage($user, $userClass) &&
                $this->verified($user, $userClass)
            ) {
                $user->setUserClass($userClass);
                $this->checkUp($user);
            }
        }
        return $user;
    }

    /**
     *  Check if the User still deserve it's user class, otherwise, downgrade findNext($user->getUserClass()it
     *
     * @param User $user
     * @return User the updated or not user
     */
    public function checkDown(User $user): User
    {
        $userClass = $user->getUserClass();
        if (
            !$this->shareScore($user, $userClass) ||
            !$this->accountAge($user, $userClass) ||
            !$this->validated($user, $userClass) ||
            !$this->manage($user, $userClass) ||
            !$this->verified($user, $userClass)
        ) {
            $userClass = $user->getUserClass()->getPrev();
            if ($userClass) {
                $user->setUserClass($userClass);
                $this->checkDown($user);
            }
        }
        return $user;
    }

    private function shareScore(User $user, UserClass $userClass): bool
    {
        return ($user->getShareScore() >= $userClass->getShareScoreReq());
    }

    private function accountAge(User $user, UserClass $userClass): bool
    {
        $interval = new DateInterval('P' . $userClass->getAccountAgeReq() . 'D');
        $ageReq = $user->getCreatedAt()->add($interval);
        return ($ageReq < new DateTime());
    }

    private function validated(User $user, UserClass $userClass): bool
    {
        return (count($user->getValidations()) >= $userClass->getValidatedReq());
    }

    private function manage(User $user, UserClass $userClass): bool
    {
        return (count($user->getConfirmedEnabledManages()) >= $userClass->getManageReq());
    }

    private function verified(User $user, UserClass $userClass): bool
    {
        return ($userClass->getVerifiedReq() && $user->isVerified() || !$userClass->getVerifiedReq());
    }
}
