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
        $default = new SharableSearch();
        $builder
            ->add('query', SearchType::class, [
                'label' => 'Search',
                'required' => false,
                'attr' => [
                    'placeholder' => 'search in Sharables names',
                ],
            ])
            ->add('disabled', ChoiceType::class, [
                'choices' => SharableSearch::DISABLED,
                'empty_data' => $default->getDisabled(),
                'required' => false,
                'expanded' => true,
            ])
            ->add('validated', ChoiceType::class, [
                'choices' => SharableSearch::VALIDATED,
                'empty_data' => $default->getValidated(),
                'required' => false,
                'expanded' => true,
            ])
            ->add('managedBy', IntegerType::class, [
                'required' => false,
                'label' => 'Managed by',
                'help' => 'user ID'
            ])
            ->add('sortBy', ChoiceType::class, [
                'choices' => SharableSearch::SORT_BY,
                'empty_data' => $default->getSortBy(),
            ])
            ->add('order', ChoiceType::class, [
                'choices' => SharableSearch::ORDER,
                'empty_data' => $default->getOrder(),
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
