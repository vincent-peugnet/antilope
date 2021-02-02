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
use App\Entity\UserClass;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SharableFixture extends Fixture implements DependentFixtureInterface
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
        $userClassRepo = $manager->getRepository(UserClass::class);
        $basicUser = $userClassRepo->findOneBy(['name' => 'basic_user']);
        $elite = $userClassRepo->findOneBy(['name' => 'elite']);
        $powerUser = $userClassRepo->findOneBy(['name' => 'power_user']);

        $sharable = new Sharable();
        $sharable->setName('Aide sur les Thinkpads')
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2020-01-02'))
            ->setLastEditedAt(new DateTime('2020-03-02'))
            ->setVisibleBy($basicUser)
            ->setResponsibility(true)
            ->setDescription('Je peux vous aider à trouver ou réparer des Thinkpads')
            ->setDetails('Les thinkpads sont des appareils souvent utilisés par les entreprises,
                donc intéressants à trouver sur __le bon coin__.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName('Un microscope')
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2019-11-11'))
            ->setLastEditedAt(new DateTime('2019-11-11'))
            ->setResponsibility(true)
            ->setInterestedMethod(3)
            ->setDescription('Je peux voir des trucs avec mon microscope.')
            ->setDetails('Un *petit* microsope, qui saura donner des résultats intéressants.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName('Grotte scrète')
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2019-12-11'))
            ->setLastEditedAt(new DateTime('2019-12-12'))
            ->setResponsibility(true)
            ->setInterestedMethod(4)
            ->setDescription('Cachette secrète sous la maison familiale.')
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->setDetails('![](https://www.grottes-musee-de-saulges.com/sites/www.grottes-musee-de-saulges.com/files/styles/edito_paragraphe_1/public/thumbnails/image/margot_salle_des_troglodythes.jpg?itok=DWnszGyz)');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName('Maison de mers')
            ->setDisabled(true)
            ->setCreatedAt(new DateTime('2020-08-08'))
            ->setLastEditedAt(new DateTime('2020-09-09'))
            ->setResponsibility(true)
            ->setDescription('La bonne vielle maison familiale')
            ->setDetails('- Nombreux couchages
- ping pong
- écran plasma');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName('Concert de Tendre Ael')
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2020-07-07'))
            ->setLastEditedAt(new DateTime('2020-07-08'))
            ->setResponsibility(true)
            ->setVisibleBy($elite)
            ->setDescription('Un concert très privé !')
            ->setDetails('__OHH YEAHH BABY__ que du *bon* son');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName('Un coin à champignon')
            ->setDisabled(false)
            ->setInterestedMethod(1)
            ->setLastEditedAt(new DateTime('2020-07-08'))
            ->setResponsibility(false)
            ->setDescription('Dans la forêt de Bernouille')
            ->setDetails('Ils sont miam miam');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName("Cliff n' Cars")
            ->setVisibleBy($basicUser)
            ->setDisabled(false)
            ->setInterestedMethod(2)
            ->setLastEditedAt(new DateTime('2020-03-02'))
            ->setBeginAt(new DateTime("yesterday"))
            ->setResponsibility(true)
            ->setDescription('like in the movie "Rebel without a Cause" ')
            ->setDetails('We are going to drive at high speed and then jump out at the last time.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->setName("Rave Party")
            ->setVisibleBy($powerUser)
            ->setDisabled(false)
            ->setInterestedMethod(2)
            ->setBeginAt(new DateTime("tomorrow"))
            ->setLastEditedAt(new DateTime('2020-03-02'))
            ->setResponsibility(true)
            ->setDescription('A classic good old Rave illegal Rave Party')
            ->setDetails('You can grab some friends with you');
        $manager->persist($sharable);
        $manager->flush();
    }
}
