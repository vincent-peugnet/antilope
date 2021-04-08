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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class UserClassDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        $userClass = $builder->getData();
        assert($userClass instanceof UserClass);

        if (!$userClass->getSharables()->isEmpty()) {
            if (!is_null($userClass->getPrev())) {
                $choices['previous'] = [$userClass->getPrev()];
                $data = $userClass->getPrev();
            }
            if (!is_null($userClass->getNext())) {
                $choices['next'] = [$userClass->getNext()];
                $data = $userClass->getNext();
            }

            $builder
                ->add('target', EntityType::class, [
                    'class' => UserClass::class,
                    'mapped' => false,
                    'choices' => $choices,
                    'required' => true,
                    'label' => new TranslatableMessage('Target user class for sharables'),
                    'help' => 'Select the new user class for sharables that where accessible by this user class'
                ])
            ;
        }
        $builder
            ->add('delete', SubmitType::class, [
                'label' => new TranslatableMessage('delete user class'),
                'attr' => [
                    'class' => 'btn-danger',
                ]
            ])
        ;
        if (isset($data)) {
            $builder->get('target')->setData($data);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserClass::class,
        ]);
    }
}
