<?php

namespace App\Form;

use App\Entity\Interested;
use App\Entity\Manage;
use App\Entity\Sharable;
use App\Entity\User;
use App\Repository\ManageRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManageType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $manage = $builder->getData();
        assert($manage instanceof Manage);
        $sharable = $manage->getSharable();



        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->findPossibleManagers($sharable),
                'required' => true,
                'placeholder' => 'select an user',
            ])
            ->add('add_manager', SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Manage::class,
        ]);
    }
}
