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

        $userGuillaume = new User();
        $userGuillaume
            ->setUsername('guillaume')
            ->setPassword($this->passwordEncoder->encodePassword($userGuillaume, 'gp231299'))
            ->setUserClass($basicUser);
        $manager->persist($userGuillaume);

        $userNicolas = new User();
        $userNicolas
            ->setUsername('nicolas')
            ->setPassword($this->passwordEncoder->encodePassword($userNicolas, 'espace'))
            ->setUserClass($member);
        $manager->persist($userNicolas);

        $userAudrey = new User();
        $userAudrey
            ->setUsername('audrey')
            ->setPassword($this->passwordEncoder->encodePassword($userAudrey, 'missmogwai'))
            ->setUserClass($powerUser);
        $manager->persist($userAudrey);


        $manager->flush();
    }
}
