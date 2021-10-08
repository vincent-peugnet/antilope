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

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AnswerFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            QuestionFixture::class,
            UserFixture::class,
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

        $questionRepo = $manager->getRepository(Question::class);
        $repairing = $questionRepo->findOneBy(['text' => 'Do you think you can help on repairing ?']);
        $sell = $questionRepo->findOneBy(['text' => 'Do you sell thinkpads ?']);
        assert($repairing instanceof Question);
        assert($sell instanceof Question);

        $answer1 = new Answer();
        $answer1->setUser($nicolas)
            ->setQuestion($repairing)
            ->setText('yes, I think it may be possible depending on your device')
            ->setCreatedAt(new DateTime('2020-06-09'));
        $manager->persist($answer1);

        $answer2 = new Answer();
        $answer2->setUser($guilhem)
            ->setQuestion($sell)
            ->setText('As far as I know, nicolas does not sell thinkpads but he can help you choose one')
            ->setCreatedAt(new DateTime('2021-03-03'));
        $manager->persist($answer2);

        $manager->flush();
    }
}
