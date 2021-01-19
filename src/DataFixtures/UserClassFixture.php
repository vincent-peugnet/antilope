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

namespace App\DataFixtures;

use App\Entity\UserClass;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserClassFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userClass = new UserClass();
        $userClass
            ->setRank(10)
            ->setAccess(false)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxParanoia(0)
            ->setName('basic_user');
        $manager->persist($userClass);

        $userClass = new UserClass();
        $userClass
            ->setRank(20)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxParanoia(1)
            ->setShareScoreReq(300)
            ->setAccountAgeReq(7)
            ->setValidatedReq(0)
            ->setName('member');
        $manager->persist($userClass);

        $userClass = new UserClass();
        $userClass
            ->setRank(30)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(true)
            ->setInviteFrequency(30)
            ->setMaxParanoia(2)
            ->setShareScoreReq(1000)
            ->setAccountAgeReq(21)
            ->setValidatedReq(1)
            ->setName('power_user');
        $manager->persist($userClass);

        $userClass = new UserClass();
        $userClass
            ->setRank(40)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(true)
            ->setInviteFrequency(14)
            ->setMaxParanoia(3)
            ->setShareScoreReq(5000)
            ->setAccountAgeReq(42)
            ->setValidatedReq(5)
            ->setName('elite');
        $manager->persist($userClass);


        $manager->flush();
    }
}
