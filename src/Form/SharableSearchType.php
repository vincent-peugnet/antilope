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
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserClass;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'search in Sharables names',
                ],
            ])
            ->add('disabled', CheckboxType::class, [
                'label' => 'disabled <span class="badge badge-danger"><i class="fas fa-ban"></i></span>',
                'label_html' => true,
                'help' => 'Show disabled sharables',
                'required' => false,
            ])
            ->add('tags', EntityType::class, [
                'label' => false,
                'class' => Tag::class,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('managedBy', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'label' => 'Managed by',
                'placeholder' => 'managed by...',
            ])
            ->add('validatedBy', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'label' => 'Validated by',
                'placeholder' => 'validated by...'
            ])
            ->add('bookmarkedBy', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'label' => 'Bookmarked by',
                'placeholder' => 'Bookmarked by...'
            ])
            ->add('interestedBy', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'label' => 'Is interested',
                'placeholder' => 'is interested...'
            ])
            ->add('visibleBy', EntityType::class, [
                'class' => UserClass::class,
                'required' => false,
                'label' => 'Visible By',
                'placeholder' => 'Visible by...'
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
