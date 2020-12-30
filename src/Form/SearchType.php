<?php

namespace App\Form;

use App\Entity\Search;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as TypeSearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TypeSearchType::class, [
                'label' => 'Search',
                'required' => false,
            ])
            ->add('disabled', ChoiceType::class, [
                'choices' => [
                    'only disabled' => 1,
                    'hide disabled' => 0,
                    'show all' => -1,
                ],
            ])
            ->add('sortBy', ChoiceType::class, [
                'choices' => [
                    'id' => 'id',
                    'createdAt' => 'createdAt',
                    'name' => 'name',
                    'lastEditedAt' => 'lastEditedAt',
                ]
            ])
            ->add('order', ChoiceType::class, [
                'choices' => [
                    'ASC' => 'ASC',
                    'DESC' => 'DESC',
                ]
            ])
            ->add('search', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
