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

use App\Entity\UserClass;
use App\Entity\UserSearch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $default = new UserSearch();
        $builder
            ->add('query')
            ->add('userClass', EntityType::class, [
                'class' => UserClass::class,
                'placeholder' => 'select an user class...',
                'required' => false,
            ])
            ->add('disabled', CheckboxType::class, [
                'label' => 'disabled <span class="badge badge-danger"><i class="fas fa-ban"></i></span>',
                'label_html' => true,
                'help' => 'Show disabled user',
                'required' => false,
            ])
            ->add('sortBy', ChoiceType::class, [
                'choices' => UserSearch::SORT_BY,
                'empty_data' => $default->getSortBy(),
            ])
            ->add('order', ChoiceType::class, [
                'choices' => UserSearch::ORDER,
                'empty_data' => $default->getOrder(),
            ])
            ->add('filter', SubmitType::class, [
                'label' => '<i class="fas fa-search"></i> Search',
                'label_html' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSearch::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
