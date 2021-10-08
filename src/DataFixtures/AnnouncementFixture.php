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

use App\Entity\Announcement;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AnnouncementFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $first = new Announcement();
        $first->setTitle('Welcome to the crazy network')
            ->setArticle('This is the first announcement, there will be more later !')
            ->setPublishedAt(new DateTime('2017-05-08'));
        $manager->persist($first);

        $last = new Announcement();
        $last->setTitle('There is a fish in the percolator !')
            ->setArticle('Just a little new about how the percolator is going.

![fish-percolator](https://c.tenor.com/nGCHCIDQ4doAAAAC/david-lynch.gif)

*Everything is going well*')
            ->setPublishedAt(new DateTime('2020-09-09'));
        $manager->persist($last);


        $manager->flush();
    }
}
