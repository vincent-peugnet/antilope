<?php

namespace App\Form;

use App\Entity\SharableSearch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharableSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', SearchType::class, [
                'label' => 'Search',
                'required' => false,
            ])
            ->add('disabled', ChoiceType::class, [
                'choices' => SharableSearch::DISABLED
            ])
            ->add('validated', ChoiceType::class, [
                'choices' => SharableSearch::VALIDATED
            ])
            ->add('sortBy', ChoiceType::class, [
                'choices' => SharableSearch::SORT_BY
            ])
            ->add('order', ChoiceType::class, [
                'choices' => SharableSearch::ORDER
            ])
            ->add('search', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SharableSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
