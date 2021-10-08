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

use App\Entity\ReportSharable;
use App\Entity\Rule;
use App\Entity\Sharable;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReportSharableFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            UserFixture::class,
            SharableFixture::class,
            RuleFixture::class,
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
        $bolos = $userRepo->findOneBy(['username' => 'bolos']);

        $sharableRepo = $manager->getRepository(Sharable::class);
        $thinkpad = $sharableRepo->findOneBy(['name' => 'Aide sur les Thinkpads']);
        $microscope = $sharableRepo->findOneBy(['name' => 'Un microscope']);
        $grotte = $sharableRepo->findOneBy(['name' => 'Grotte scrète']);
        $mers = $sharableRepo->findOneBy(['name' => 'Maison de mers']);
        $concert = $sharableRepo->findOneBy(['name' => 'Concert de Tendre Ael']);
        $champignon = $sharableRepo->findOneBy(['name' => 'Un coin à champignon']);
        $cliffAndCars = $sharableRepo->findOneBy(['name' => "Cliff n' Cars"]);

        $ruleRepo = $manager->getRepository(Rule::class);
        $respectStaff = $ruleRepo->findOneBy(['name' => 'Respect Staff']);
        assert($respectStaff instanceof Rule);
        $dangerousness = $ruleRepo->findOneBy(['name' => 'Dangerousness']);
        assert($dangerousness instanceof Rule);

        $report1 = new ReportSharable();
        $report1->setSharable($cliffAndCars)
            ->setUser($guillaume)
            ->addRule($dangerousness)
            ->setMessage('This is way __too dangerous__, I think')
            ->setCreatedAt(new DateTime('2020-05-05'));
        $manager->persist($report1);

        $report2 = new ReportSharable();
        $report2->setSharable($champignon)
            ->setUser($bolos)
            ->addRule($respectStaff)
            ->addRule($dangerousness)
            ->setMessage('This is shiiiiiiiiiiiiiiit !!!!!! haha lol')
            ->setCreatedAt(new DateTime('2020-07-07'));
        $manager->persist($report2);


        $manager->flush();
    }
}
