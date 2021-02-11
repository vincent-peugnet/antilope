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

use App\Entity\Contact;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    private int $contactEditDelay;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->contactEditDelay = (int) $parameters->get('app.contactEditDelay');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $contact = $builder->getData();
        assert(is_subclass_of($contact, Contact::class));
        $new = (is_null($contact->getId()));
        $edit = (
            $new ||
            !$this->contactEditDelay ||
            $contact->getCreatedAt() > new DateTime($this->contactEditDelay . 'minutes ago')
        );

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => Contact::allowedTypes(),
                'placeholder' => 'Choose your contact type',
                'required' => true,
                'disabled' => !$new,
            ])
            ->add('content', TextType::class, [
                'required' => true,
                'disabled' => !$edit,
                'help' => 'Yout phone number, email adress, or wathever you whant to be join with',
            ])
            ->add('info', TextareaType::class, [
                'help' => 'You can precise some infos if you feel the need',
                'required' => false,
            ])
        ;

        if ($new) {
            $builder->add('add', SubmitType::class);
        } else {
            $builder->add('edit', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
