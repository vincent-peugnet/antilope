<?php

namespace App\Form;

use App\Entity\Invitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvitationCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userLimitReached = $options['userLimitReached'];
        $needToWait = $options['needToWait'];

        $builder->add('generate', SubmitType::class);

        if ($userLimitReached || $needToWait) {
            $builder->get('generate')->setDisabled(true);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invitation::class,
            'userLimitReached' => false,
            'needToWait' => false,
        ]);
        $resolver->setAllowedTypes('userLimitReached', 'bool');
        $resolver->setAllowedTypes('needToWait', 'bool');
    }
}
