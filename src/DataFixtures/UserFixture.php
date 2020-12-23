<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserClass;
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

        $userGuillaume = new User();
        $userGuillaume
            ->setUsername('guillaume')
            ->setPassword($this->passwordEncoder->encodePassword($userGuillaume, 'gp231299'))
            ->setUserClass($basicUser)
            ->setParanoia(0)
            ->setShareScore(0);
        $manager->persist($userGuillaume);

        $userLea = new User();
        $userLea
            ->setUsername('leatine')
            ->setPassword($this->passwordEncoder->encodePassword($userLea, 'leatine'))
            ->setUserClass($member)
            ->setParanoia(0)
            ->setShareScore(0);
        $manager->persist($userLea);

        $userNicolas = new User();
        $userNicolas
            ->setUsername('nicolas')
            ->setPassword($this->passwordEncoder->encodePassword($userNicolas, 'espace'))
            ->setUserClass($member)
            ->setParanoia(1)
            ->setShareScore(0);
        $manager->persist($userNicolas);

        $userAudrey = new User();
        $userAudrey
            ->setUsername('audrey')
            ->setPassword($this->passwordEncoder->encodePassword($userAudrey, 'missmogwai'))
            ->setUserClass($powerUser)
            ->setParanoia(0)
            ->setShareScore(0);
        $manager->persist($userAudrey);

        $userGuilhem = new User();
        $userGuilhem
            ->setUsername('guilhem')
            ->setPassword($this->passwordEncoder->encodePassword($userGuilhem, 'guilhem'))
            ->setUserClass($elite)
            ->setParanoia(2)
            ->setShareScore(0);
        $manager->persist($userGuilhem);


        $manager->flush();
    }
}
