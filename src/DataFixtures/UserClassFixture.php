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
        $elite = new UserClass();
        $elite
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(true)
            ->setInviteFrequency(14)
            ->setMaxParanoia(4)
            ->setShareScoreReq(5000)
            ->setAccountAgeReq(42)
            ->setValidatedReq(3)
            ->setManageReq(5)
            ->setName('elite');
        $manager->persist($elite);

        $powerUser = new UserClass();
        $powerUser
            ->setNext($elite)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(true)
            ->setInviteFrequency(30)
            ->setMaxParanoia(3)
            ->setShareScoreReq(1000)
            ->setAccountAgeReq(21)
            ->setValidatedReq(1)
            ->setManageReq(3)
            ->setName('power_user');
        $manager->persist($powerUser);

        $member = new UserClass();
        $member
            ->setNext($powerUser)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxInactivity(90)
            ->setMaxParanoia(2)
            ->setShareScoreReq(300)
            ->setAccountAgeReq(7)
            ->setValidatedReq(0)
            ->setManageReq(1)
            ->setName('member');
        $manager->persist($member);

        $babyMember = new UserClass();
        $babyMember
            ->setNext($member)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxInactivity(90)
            ->setMaxParanoia(2)
            ->setShareScoreReq(0)
            ->setAccountAgeReq(0)
            ->setValidatedReq(0)
            ->setManageReq(1)
            ->setName('baby_member');
        $manager->persist($babyMember);

        $basicUser = new UserClass();
        $basicUser
            ->setNext($babyMember)
            ->setAccess(false)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxInactivity(30)
            ->setMaxParanoia(1)
            ->setName('basic_user');
        $manager->persist($basicUser);


        $manager->flush();
    }
}
