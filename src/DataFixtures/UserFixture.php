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
 * PHP version 7.2
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserClass;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture implements DependentFixtureInterface
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function getDependencies()
    {
        return [
            UserClassFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $userClassRepo = $manager->getRepository(UserClass::class);
        $basicUser = $userClassRepo->findOneBy(['name' => 'basic_user']);
        $member = $userClassRepo->findOneBy(['name' => 'member']);
        $powerUser = $userClassRepo->findOneBy(['name' => 'power_user']);
        $elite = $userClassRepo->findOneBy(['name' => 'elite']);

        $userBolos = new User();
        $userBolos
            ->setUsername('bolos')
            ->setEmail('bolos@coucou.fr')
            ->setPassword($this->passwordEncoder->encodePassword($userBolos, 'bolos'))
            ->setUserClass($basicUser)
            ->setCreatedAt(new DateTime('2020-02-02'))
            ->setParanoia(0)
            ->setShareScore(0);
        $manager->persist($userBolos);

        $userVincent = new User();
        $userVincent
            ->setUsername('vincent')
            ->setEmail('vincent-peugnet@riseup.net')
            ->setPassword($this->passwordEncoder->encodePassword($userVincent, 'vincent'))
            ->setUserClass($member)
            ->setCreatedAt(new DateTime('2020-06-02'))
            ->setParanoia(0)
            ->setRoles([User::ROLE_ADMIN])
            ->setShareScore(182);
        $manager->persist($userVincent);

        $userGuillaume = new User();
        $userGuillaume
            ->setUsername('guillaume')
            ->setEmail('guillaume.peugnet@free.fr')
            ->setPassword($this->passwordEncoder->encodePassword($userGuillaume, 'gp231299'))
            ->setUserClass($basicUser)
            ->setCreatedAt(new DateTime('2020-04-04'))
            ->setParanoia(0)
            ->setShareScore(288);
        $manager->persist($userGuillaume);

        $userLea = new User();
        $userLea
            ->setUsername('leatine')
            ->setEmail('paintendre.lea@ensapc.fr')
            ->setPassword($this->passwordEncoder->encodePassword($userLea, 'leatine'))
            ->setUserClass($member)
            ->setCreatedAt(new DateTime('2019-06-06'))
            ->setParanoia(0)
            ->setShareScore(1293);
        $manager->persist($userLea);

        $userNicolas = new User();
        $userNicolas
            ->setUsername('nicolas')
            ->setEmail('n.peugnet@free.fr')
            ->setPassword($this->passwordEncoder->encodePassword($userNicolas, 'espace'))
            ->setUserClass($member)
            ->setCreatedAt(new DateTime('2018-01-01'))
            ->setParanoia(1)
            ->setShareScore(745);
        $manager->persist($userNicolas);

        $userAudrey = new User();
        $userAudrey
            ->setUsername('audrey')
            ->setEmail('deluc.audrey@ensapc.fr')
            ->setPassword($this->passwordEncoder->encodePassword($userAudrey, 'missmogwai'))
            ->setUserClass($powerUser)
            ->setCreatedAt(new DateTime('2018-03-03'))
            ->setParanoia(0)
            ->setShareScore(2410);
        $manager->persist($userAudrey);

        $userGuilhem = new User();
        $userGuilhem
            ->setUsername('guilhem')
            ->setEmail('prevostleygonie.guilhem@ensapc.fr')
            ->setPassword($this->passwordEncoder->encodePassword($userGuilhem, 'guilhem'))
            ->setUserClass($elite)
            ->setCreatedAt(new DateTime('2017-09-09'))
            ->setParanoia(2)
            ->setShareScore(6852);
        $manager->persist($userGuilhem);


        $manager->flush();
    }
}
