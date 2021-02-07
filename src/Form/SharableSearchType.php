<?php

/**
 * This file is part of Antilope
 *
 * Antilope is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * PHP version 7.4
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

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
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('search', SubmitType::class, [
                'label' => '<i class="fas fa-search"></i> Search',
                'label_html' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
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
