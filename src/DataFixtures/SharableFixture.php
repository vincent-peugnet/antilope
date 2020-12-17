<?php

namespace App\DataFixtures;

use App\Entity\Sharable;
use App\Entity\User;
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
        $nicolas = $sharableRepo->findOneBy(['username' => 'nicolas']);

        $sharable = new Sharable();
        $sharable->addManagedBy($nicolas)
            ->setDisabled(false)
            ->setName('Aide sur les Thinkpads')
            ->setDescription('Je peux vous aider à trouver ou réparer des Thinkpads');
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
    }
}
