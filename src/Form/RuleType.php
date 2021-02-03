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

use App\Entity\Rule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $rule = $builder->getData();

        $builder
            ->add('name')
            ->add('text')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $rule = $event->getData();
            assert($rule instanceof Rule);
            $form = $event->getForm();

            if ($rule->getId() === null) {
                $form->add('create', SubmitType::class);
            } else {
                $form->add('edit', SubmitType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Rule::class,
        ]);
    }
}
