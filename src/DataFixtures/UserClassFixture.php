<?php

namespace App\DataFixtures;

use App\Entity\UserClass;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserClassFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userClass= new UserClass();
        $userClass
            ->setRank(10)
            ->setName('basic_user');
        $manager->persist($userClass);

        $userClass= new UserClass();
        $userClass
            ->setRank(20)
            ->setName('member');
        $manager->persist($userClass);

        $userClass= new UserClass();
        $userClass
            ->setRank(30)
            ->setName('power_user');
        $manager->persist($userClass);


        $manager->flush();
    }
}
