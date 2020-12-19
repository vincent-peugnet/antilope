<?php

namespace App\DataFixtures;

use App\Entity\Sharable;
use App\Entity\User;
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
        ];
    }

    public function load(ObjectManager $manager)
    {
        $sharableRepo = $manager->getRepository(User::class);
        $audrey = $sharableRepo->findOneBy(['username' => 'audrey']);
        $guillaume = $sharableRepo->findOneBy(['username' => 'guillaume']);
        $nicolas = $sharableRepo->findOneBy(['username' => 'nicolas']);
        $leatine = $sharableRepo->findOneBy(['username' => 'leatine']);

        $sharable = new Sharable();
        $sharable->addManagedBy($nicolas)
            ->setDisabled(false)
            ->setName('Aide sur les Thinkpads')
            ->setDescription('Je peux vous aider à trouver ou réparer des Thinkpads');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($guillaume)
            ->setDisabled(false)
            ->setName('Un microscope')
            ->setDescription('Je peux voir des trucs avec mon microscope.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($nicolas)
            ->addManagedBy($audrey)
            ->setDisabled(false)
            ->setName('Grotte scrète')
            ->setDescription('Cachette secrète sous la maison familiale.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($leatine)
            ->setDisabled(false)
            ->setName('Concert de Tendre Ael')
            ->setDescription('Un ptit live sympatoche. Youhou');
        $manager->persist($sharable);
        $manager->flush();
    }
}
