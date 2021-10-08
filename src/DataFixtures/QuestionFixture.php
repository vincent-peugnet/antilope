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
 * @copyright 2021-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\DataFixtures;

use App\Entity\Question;
use App\Entity\Sharable;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class QuestionFixture extends Fixture implements DependentFixtureInterface
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
        $guilhem = $userRepo->findOneBy(['username' => 'guilhem']);
        $nicolas = $userRepo->findOneBy(['username' => 'nicolas']);
        $leatine = $userRepo->findOneBy(['username' => 'leatine']);
        $relou = $userRepo->findOneBy(['username' => 'relou']);
        $bolos = $userRepo->findOneBy(['username' => 'bolos']);

        $sharableRepo = $manager->getRepository(Sharable::class);
        $thinkpad = $sharableRepo->findOneBy(['name' => 'Aide sur les Thinkpads']);
        $microscope = $sharableRepo->findOneBy(['name' => 'Un microscope']);
        $grotte = $sharableRepo->findOneBy(['name' => 'Grotte scrète']);
        $mers = $sharableRepo->findOneBy(['name' => 'Maison de mers']);
        $concert = $sharableRepo->findOneBy(['name' => 'Concert de Tendre Ael']);
        $champignon = $sharableRepo->findOneBy(['name' => 'Un coin à champignon']);
        $cliffAndCars = $sharableRepo->findOneBy(['name' => "Cliff n' Cars"]);

        $question1 = new Question();
        $question1->setUser($audrey)
            ->setSharable($microscope)
            ->setText('Can we use it for spiruline ?');
        $manager->persist($question1);

        $question2 = new Question();
        $question2->setUser($guilhem)
            ->setSharable($thinkpad)
            ->setCreatedAt(new DateTime('2020-04-08'))
            ->setText('Do you think you can help on repairing ?');
        $manager->persist($question2);

        $question3 = new Question();
        $question3->setUser($bolos)
            ->setSharable($thinkpad)
            ->setText('Do you sell thinkpads ?')
            ->setCreatedAt(new DateTime('2021-02-02'));
        $manager->persist($question3);

        $manager->flush();
    }
}
