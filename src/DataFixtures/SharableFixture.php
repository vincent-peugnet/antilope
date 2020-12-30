<?php

namespace App\DataFixtures;

use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
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

        $userClassRepo = $manager->getRepository(UserClass::class);
        $basicUser = $userClassRepo->findOneBy(['name' => 'basic_user']);
        $elite = $userClassRepo->findOneBy(['name' => 'elite']);

        $sharable = new Sharable();
        $sharable->addManagedBy($nicolas)
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2020-01-02'))
            ->setLastEditedAt(new DateTime('2020-03-02'))
            ->setName('Aide sur les Thinkpads')
            ->setVisibleBy($basicUser)
            ->setResponsibility(true)
            ->setDescription('Je peux vous aider à trouver ou réparer des Thinkpads')
            ->setDetails('Les thinkpads sont des appareils souvent utilisés par les entreprises,
                donc intéressants à trouver sur __le bon coin__.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($guillaume)
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2019-11-11'))
            ->setLastEditedAt(new DateTime('2019-11-11'))
            ->setName('Un microscope')
            ->setResponsibility(true)
            ->setDescription('Je peux voir des trucs avec mon microscope.')
            ->setDetails('Un *petit* microsope, qui saura donner des résultats intéressants.');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($audrey)
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2019-12-11'))
            ->setLastEditedAt(new DateTime('2019-12-12'))
            ->setName('Grotte scrète')
            ->setResponsibility(true)
            ->setDescription('Cachette secrète sous la maison familiale.')
            ->setDetails('![](https://www.grottes-musee-de-saulges.com/sites/www.grottes-musee-de-saulges.com/files/styles/edito_paragraphe_1/public/thumbnails/image/margot_salle_des_troglodythes.jpg?itok=DWnszGyz)');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($nicolas)
            ->addManagedBy($guillaume)
            ->setDisabled(true)
            ->setCreatedAt(new DateTime('2020-08-08'))
            ->setLastEditedAt(new DateTime('2020-09-09'))
            ->setName('Maison de mers')
            ->setResponsibility(true)
            ->setDescription('La bonne vielle maison familiale')
            ->setDetails('- Nombreux couchages
- ping pong
- écran plasma');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($leatine)
            ->setDisabled(false)
            ->setCreatedAt(new DateTime('2020-07-07'))
            ->setLastEditedAt(new DateTime('2020-07-08'))
            ->setName('Concert de Tendre Ael')
            ->setResponsibility(true)
            ->setVisibleBy($elite)
            ->setDescription('Un concert très privé !')
            ->setDetails('__OHH YEAHH BABY__ que du *bon* son');
        $manager->persist($sharable);
        $manager->flush();

        $sharable = new Sharable();
        $sharable->addManagedBy($leatine)
            ->addManagedBy($guillaume)
            ->setDisabled(false)
            ->setName('Un coin à champignon')
            ->setLastEditedAt(new DateTime('2020-07-08'))
            ->setResponsibility(false)
            ->setDescription('Dans la forêt de Bernouille')
            ->setDetails('Ils sont miam miam');
        $manager->persist($sharable);
        $manager->flush();
    }
}
