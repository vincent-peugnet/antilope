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

use App\Entity\Manage;
use App\Entity\Sharable;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ManageFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            UserFixture::class,
            SharableFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $userRepo = $manager->getRepository(User::class);
        $audrey = $userRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $userRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $userRepo->findOneBy(['username' => 'nicolas']);
        $vincent = $userRepo->findOneBy(['username' => 'vincent']);
        $leatine = $userRepo->findOneBy(['username' => 'leatine']);
        $relou = $userRepo->findOneBy(['username' => 'relou']);

        $sharableRepo = $manager->getRepository(Sharable::class);
        $thinkpad = $sharableRepo->findOneBy(['name' => 'Aide sur les Thinkpads']);
        $microscope = $sharableRepo->findOneBy(['name' => 'Un microscope']);
        $grotte = $sharableRepo->findOneBy(['name' => 'Grotte scrète']);
        $mers = $sharableRepo->findOneBy(['name' => 'Maison de mers']);
        $concert = $sharableRepo->findOneBy(['name' => 'Concert de Tendre Ael']);
        $champignon = $sharableRepo->findOneBy(['name' => 'Un coin à champignon']);
        $cliffAndCars = $sharableRepo->findOneBy(['name' => "Cliff n' Cars"]);
        $raveParty = $sharableRepo->findOneBy(['name' => "Rave Party"]);

        $manage = new Manage();
        $manage->setSharable($thinkpad);
        $manage->setUser($nicolas);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($microscope);
        $manage->setUser($guillaume);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($grotte);
        $manage->setUser($audrey);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($mers);
        $manage->setUser($guillaume);
        $manage->setContactable(false);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($mers);
        $manage->setUser($nicolas);
        $manage->setContactable(false);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($concert);
        $manage->setUser($leatine);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($champignon);
        $manage->setUser($leatine);
        $manage->setContactable(false);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($champignon);
        $manage->setUser($guillaume);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($cliffAndCars);
        $manage->setUser($relou);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($raveParty);
        $manage->setUser($relou);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($raveParty);
        $manage->setUser($nicolas);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($raveParty);
        $manage->setUser($vincent);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();
    }
}
