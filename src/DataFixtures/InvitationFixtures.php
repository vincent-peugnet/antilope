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

use App\Entity\Invitation;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InvitationFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            UserFixture::class,
            UserClassFixture::class,
        ];
    }


    public function load(ObjectManager $manager)
    {
        $sharableRepo = $manager->getRepository(User::class);
        $audrey = $sharableRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $sharableRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $sharableRepo->findOneBy(['username' => 'nicolas']);
        $leatine = $sharableRepo->findOneBy(['username' => 'leatine']);
        $bolos = $sharableRepo->findOneBy(['username' => 'bolos']);
        $guilhem = $sharableRepo->findOneBy(['username' => 'guilhem']);


        $invitation = new Invitation();
        $invitation->setParent($guilhem)
            ->setChild($nicolas)
            ->setCode('4PSNj2bESZdhefbx6H69qv')
            ->setCreatedAt(new DateTime('2018-01-01'));
        $manager->persist($invitation);
        $manager->flush();



        $invitation = new Invitation();
        $invitation->setParent($guilhem)
            ->setChild($audrey)
            ->setCode('WxZzeo6q1UtrhqdV4951MF')
            ->setCreatedAt(new DateTime('2018-03-03'));
        $manager->persist($invitation);
        $manager->flush();


        $invitation = new Invitation();
        $invitation->setParent($nicolas)
            ->setChild($leatine)
            ->setCode('LvWaKeZxngmracvKvj3GSE')
            ->setCreatedAt(new DateTime('2019-06-06'));
        $manager->persist($invitation);
        $manager->flush();


        $invitation = new Invitation();
        $invitation->setParent($nicolas)
            ->setChild($bolos)
            ->setCode('SmSZGjkFXg1Nq4tdKYPTTd')
            ->setCreatedAt(new DateTime('2020-02-02'));
        $manager->persist($invitation);
        $manager->flush();


        $invitation = new Invitation();
        $invitation->setParent($leatine)
            ->setChild($guillaume)
            ->setCode('2RqwjP5SKucMkiAx6Rne9D')
            ->setCreatedAt(new DateTime('2020-04-04'));
        $manager->persist($invitation);
        $manager->flush();
    }
}
