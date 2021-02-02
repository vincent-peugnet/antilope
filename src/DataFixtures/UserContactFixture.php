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

use App\Entity\Contact;
use App\Entity\User;
use App\Entity\UserContact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserContactFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            UserFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $userRepo = $manager->getRepository(User::class);
        $audrey = $userRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $userRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $userRepo->findOneBy(['username' => 'nicolas']);
        $leatine = $userRepo->findOneBy(['username' => 'leatine']);
        $vincent = $userRepo->findOneBy(['username' => 'vincent']);
        $guilhem = $userRepo->findOneBy(['username' => 'guilhem']);
        $relou = $userRepo->findOneBy(['username' => 'relou']);

        $audreyContact = new UserContact();
        $audreyContact->setUser($audrey)
            ->setType(Contact::EMAIL)
            ->setContent('audrey@ensapc.fr');
        $manager->persist($audreyContact);

        $guillaumeContact = new UserContact();
        $guillaumeContact->setUser($guillaume)
            ->setType(Contact::OTHER)
            ->setContent('52 Rue Victor Hugo');
        $manager->persist($guillaumeContact);

        $nicolasContact = new UserContact();
        $nicolasContact->setUser($nicolas)
            ->setType(Contact::OTHER)
            ->setContent('@n-peugnet:club1.fr')
            ->setInfo('This is my Matrix ID');
        $manager->persist($nicolasContact);

        $leatineContact = new UserContact();
        $leatineContact->setUser($leatine)
            ->setType(Contact::PHONE)
            ->setContent('0634918853')
            ->setInfo('It is a fake number ;)');
        $manager->persist($leatineContact);


        $vincentContact = new UserContact();
        $vincentContact->setUser($vincent)
            ->setType(Contact::EMAIL)
            ->setContent('vincent-peugnet@riseup.net')
            ->setInfo('My new official email adress');
        $manager->persist($vincentContact);


        $guilhemContact = new UserContact();
        $guilhemContact->setUser($guilhem)
            ->setType(Contact::PHONE)
            ->setContent('0642722425')
            ->setInfo('My phone');
        $manager->persist($guilhemContact);

        $relouContact = new UserContact();
        $relouContact->setUser($relou)
            ->setType(Contact::OTHER)
            ->setContent('You cannot find me so easily')
            ->setInfo('hehehe');
        $manager->persist($relouContact);

        $manager->flush();
    }
}
