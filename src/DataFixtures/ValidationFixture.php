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

use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\Validation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ValidationFixture extends Fixture implements DependentFixtureInterface
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
        $guilhem = $userRepo->findOneBy(['username' => 'guilhem']);

        $sharableRepo = $manager->getRepository(Sharable::class);
        $thinkpad = $sharableRepo->findOneBy(['name' => 'Aide sur les Thinkpads']);
        $microscope = $sharableRepo->findOneBy(['name' => 'Un microscope']);
        $grotte = $sharableRepo->findOneBy(['name' => 'Grotte scrète']);
        $mers = $sharableRepo->findOneBy(['name' => 'Maison de mers']);
        $concert = $sharableRepo->findOneBy(['name' => 'Concert de Tendre Ael']);
        $champignon = $sharableRepo->findOneBy(['name' => 'Un coin à champignon']);

        $guilhemValidatedChampignon = new Validation();
        $guilhemValidatedChampignon->setSharable($champignon)
            ->setUser($guilhem)
            ->setMessage('Very good spot ! Thanks');
        $manager->persist($guilhemValidatedChampignon);

        $guilhemValidatedThinkpad = new Validation();
        $guilhemValidatedThinkpad->setSharable($thinkpad)
            ->setUser($guilhem)
            ->setMessage('Thanks for helping me with my X250');
        $manager->persist($guilhemValidatedThinkpad);

        $guilhemValidatedMers = new Validation();
        $guilhemValidatedMers->setSharable($mers)
            ->setUser($guilhem)
            ->setMessage('Even if I am not a big fan of water, the little town ambiance is charming');
        $manager->persist($guilhemValidatedMers);

        $audreyValidatedChampignon = new Validation();
        $audreyValidatedChampignon->setSharable($champignon)
            ->setUser($audrey)
            ->setMessage('They are really delicious !');
        $manager->persist($audreyValidatedChampignon);

        $manager->flush();
    }
}
