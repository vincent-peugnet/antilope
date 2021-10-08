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

use App\Entity\Rule;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RuleFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $rule1 = new Rule();
        $rule1->setName('Respect Staff')
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->setText('They are experienced, knowledgeable, and have a reason for their actions. If you disagree with a staff member, discuss it privately and courteously. Needlessly arguing with or being disrespectful towards staff members will not be taken lightly.')
            ->setCreatedAt(new DateTime('yesterday'));
        $manager->persist($rule1);

        $rule2 = new Rule();
        $rule2->setName('One account per person per lifetime')
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->setText('Do not create a new account under any circumstance. Anyone found creating additional accounts will be banned. You may not share your account with other people.')
            ->setCreatedAt(new DateTime('2019-06-09'));
        $manager->persist($rule2);

        $rule3 = new Rule();
        $rule3->setName('Do not sell, trade, give away, or misuse invites in any way')
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->setText('Invites are only meant for personal acquaintances. Staff and approved members may offer invites on other trackers in designated, class-restricted invite forums. Do not offer invites on public websites or any private site that exist solely for the purpose of invite giveaways, trading, or selling. Do not offer invites on IRC channels or other social networks. Offering an invite via personal message is still considered giving an invite away. Your account will be banned or at least lose invite privileges and access to our invites forum if you misuse them in any way. Please see the Invites section of the rules for specifics.')
            ->setCreatedAt(new DateTime('2018-01-01'));
        $manager->persist($rule3);

        $rule4 = new Rule();
        $rule4->setName('Dangerousness')
            ->setText('Nothing should endanger users under any circumstances.')
            ->setCreatedAt(new DateTime('2018-03-05'));
        $manager->persist($rule4);

        $rule5 = new Rule();
        $rule5->setName('Reports')
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->setText('Do do reports for nothing, or bad/fake reports, this add too much effort for moderators for nothing.')
            ->setCreatedAt(new DateTime('2018-03-05'));
        $manager->persist($rule5);

        $manager->flush();
    }
}
