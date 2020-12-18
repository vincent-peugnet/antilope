<?php

namespace App\Form\Type;

use App\Entity\Sharable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('beginAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required'   => false,
                'help' => 'If what you\'re sharing have a begin date, indicate it !'
            ])
            ->add('endAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required'   => false,
                'help' => 'If what you\'re sharing have an end, indicate it !'
            ])
            ->add('disabled', null, [
                'help' => 'check this if the thing you\'re sharing is not available anymore (but can be available in the future)',
            ])
            ->add('visibleBy', null, [
                'placeholder' => '',
                'help' => 'If you want to set a specific class',
            ])
            ->add('edit', SubmitType::class)

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sharable::class,
        ]);
    }
}
