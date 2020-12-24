<?php

namespace App\DataFixtures;

use App\Entity\Invitation;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InvitationFixtures extends Fixture implements DependentFixtureInterface
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
        $sharableRepo = $manager->getRepository(User::class);
        $audrey = $sharableRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $sharableRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $sharableRepo->findOneBy(['username' => 'nicolas']);
        $leatine = $sharableRepo->findOneBy(['username' => 'leatine']);
        $bolos = $sharableRepo->findOneBy(['username' => 'bolos']);
        $guilhem = $sharableRepo->findOneBy(['username' => 'guilhem']);


        $invitation = new Invitation();
        $invitation->setParent($guilhem)
            ->setChild($nicolas)
            ->setCode('4PSNj2bESZdhefbx6H69qv')
            ->setCreatedAt(new DateTime('2018-01-01'));
        $manager->persist($invitation);
        $manager->flush();



        $invitation = new Invitation();
        $invitation->setParent($guilhem)
            ->setChild($audrey)
            ->setCode('WxZzeo6q1UtrhqdV4951MF')
            ->setCreatedAt(new DateTime('2018-03-03'));
        $manager->persist($invitation);
        $manager->flush();


        $invitation = new Invitation();
        $invitation->setParent($nicolas)
            ->setChild($leatine)
            ->setCode('LvWaKeZxngmracvKvj3GSE')
            ->setCreatedAt(new DateTime('2019-06-06'));
        $manager->persist($invitation);
        $manager->flush();


        $invitation = new Invitation();
        $invitation->setParent($nicolas)
            ->setChild($bolos)
            ->setCode('SmSZGjkFXg1Nq4tdKYPTTd')
            ->setCreatedAt(new DateTime('2020-02-02'));
        $manager->persist($invitation);
        $manager->flush();


        $invitation = new Invitation();
        $invitation->setParent($leatine)
            ->setChild($guillaume)
            ->setCode('2RqwjP5SKucMkiAx6Rne9D')
            ->setCreatedAt(new DateTime('2020-04-04'));
        $manager->persist($invitation);
        $manager->flush();
    }
}
