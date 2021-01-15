<?php

namespace App\DataFixtures;

use App\Entity\UserClass;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserClassFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userClass = new UserClass();
        $userClass
            ->setRank(10)
            ->setAccess(false)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxParanoia(0)
            ->setName('basic_user');
        $manager->persist($userClass);

        $userClass = new UserClass();
        $userClass
            ->setRank(20)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(false)
            ->setInviteFrequency(0)
            ->setMaxParanoia(1)
            ->setShareScoreReq(300)
            ->setAccountAgeReq(7)
            ->setValidatedReq(0)
            ->setName('member');
        $manager->persist($userClass);

        $userClass = new UserClass();
        $userClass
            ->setRank(30)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(true)
            ->setInviteFrequency(30)
            ->setMaxParanoia(2)
            ->setShareScoreReq(1000)
            ->setAccountAgeReq(21)
            ->setValidatedReq(1)
            ->setName('power_user');
        $manager->persist($userClass);

        $userClass = new UserClass();
        $userClass
            ->setRank(40)
            ->setAccess(true)
            ->setShare(true)
            ->setCanInvite(true)
            ->setInviteFrequency(14)
            ->setMaxParanoia(3)
            ->setShareScoreReq(5000)
            ->setAccountAgeReq(42)
            ->setValidatedReq(5)
            ->setName('elite');
        $manager->persist($userClass);


        $manager->flush();
    }
}
