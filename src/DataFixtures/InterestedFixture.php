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

use App\Entity\Interested;
use App\Entity\Sharable;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InterestedFixture extends Fixture implements DependentFixtureInterface
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
        $vincent = $userRepo->findOneBy(['username' => 'vincent']);
        $audrey = $userRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $userRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $userRepo->findOneBy(['username' => 'nicolas']);
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

        $vincentInterestedInGrotte = new Interested();
        $vincentInterestedInGrotte->setSharable($grotte)
            ->setUser($vincent);
        $manager->persist($vincentInterestedInGrotte);

        $leatineInterestedInGrotte = new Interested();
        $leatineInterestedInGrotte->setSharable($grotte)
            ->setUser($leatine);
        $manager->persist($leatineInterestedInGrotte);

        $relouInterestedInGrotte = new Interested();
        $relouInterestedInGrotte->setSharable($grotte)
            ->setUser($relou);
        $manager->persist($relouInterestedInGrotte);

        $leatineInterestedInMicroscope = new Interested();
        $leatineInterestedInMicroscope->setSharable($microscope)
            ->setUser($leatine)
            ->setReviewed(true);
        $manager->persist($leatineInterestedInMicroscope);

        $audreyInterestedInMicroscope = new Interested();
        $audreyInterestedInMicroscope->setSharable($microscope)
            ->setUser($audrey);
        $manager->persist($audreyInterestedInMicroscope);

        $audreyInterestedInMicroscope = new Interested();
        $audreyInterestedInMicroscope->setSharable($microscope)
            ->setUser($audrey);
        $manager->persist($audreyInterestedInMicroscope);

        $nicolasInterestedInCliffandCars = new Interested();
        $nicolasInterestedInCliffandCars->setSharable($cliffAndCars)
            ->setUser($nicolas);
        $manager->persist($nicolasInterestedInCliffandCars);

        $manager->flush();
    }
}
