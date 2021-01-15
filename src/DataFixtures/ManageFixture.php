<?php

namespace App\DataFixtures;

use App\Entity\Manage;
use App\Entity\Sharable;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ManageFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            UserFixture::class,
            SharableFixture::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        $userRepo = $manager->getRepository(User::class);
        $audrey = $userRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $userRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $userRepo->findOneBy(['username' => 'nicolas']);
        $leatine = $userRepo->findOneBy(['username' => 'leatine']);

        $sharableRepo = $manager->getRepository(Sharable::class);
        $thinkpad = $sharableRepo->findOneBy(['name' => 'Aide sur les Thinkpads']);
        $microscope = $sharableRepo->findOneBy(['name' => 'Un microscope']);
        $grotte = $sharableRepo->findOneBy(['name' => 'Grotte scrète']);
        $mers = $sharableRepo->findOneBy(['name' => 'Maison de mers']);
        $concert = $sharableRepo->findOneBy(['name' => 'Concert de Tendre Ael']);
        $champignon = $sharableRepo->findOneBy(['name' => 'Un coin à champignon']);

        $manage = new Manage();
        $manage->setSharable($thinkpad);
        $manage->setUser($nicolas);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($microscope);
        $manage->setUser($guillaume);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($grotte);
        $manage->setUser($audrey);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($mers);
        $manage->setUser($guillaume);
        $manage->setContactable(false);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($mers);
        $manage->setUser($nicolas);
        $manage->setContactable(false);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($concert);
        $manage->setUser($leatine);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($champignon);
        $manage->setUser($leatine);
        $manage->setContactable(false);

        $manager->persist($manage);
        $manager->flush();

        $manage = new Manage();
        $manage->setSharable($champignon);
        $manage->setUser($guillaume);
        $manage->setContactable(true);

        $manager->persist($manage);
        $manager->flush();
    }
}
