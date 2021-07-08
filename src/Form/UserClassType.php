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
use App\Repository\UserClassRepository;
use App\Security\Voter\UserVoter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserClassType extends AbstractType
{
    private UserClassRepository $userClassRepository;

    public function __construct(UserClassRepository $userClassRepository)
    {
        $this->userClassRepository = $userClassRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $userClass = $builder->getData();
        assert($userClass instanceof UserClass);

        // If The it's a new user class
        if (is_null($userClass->getId())) {
            $builder->add('next', EntityType::class, [
                'class' => UserClass::class,
                'label' => new TranslatableMessage('next user class'),
                'choices' => $this->userClassRepository->findAll(),
                'placeholder' => 'none',
                'help' => 'Your user class will be placed before the selected one.',
                'required' => false,
            ]);
        }

        $builder
            ->add('name')
            ->add('share', null, [
                'label' => new TranslatableMessage('can create sharables'),
            ])
            ->add('access', null, [
                'label' => new TranslatableMessage('can access sharables'),
            ])
            ->add('canQuestion', null, [
                'label' => new TranslatableMessage('can ask questions'),
            ])
            ->add('canSetVisibleBy', null, [
                'label' => new TranslatableMessage('can set visibleBy parameter'),
            ])
            ->add('canInvite', null, [
                'label' => new TranslatableMessage('can invite users'),
            ])
            ->add('maxInactivity', null, [
                'help' => 'inactivity allowed before account is disabled (in days)',
            ])
            ->add('maxParanoia', ChoiceType::class, [
                'choices' => UserVoter::getParanoiaLevels(),
            ])
            ->add('inviteFrequency', null, [
                'help' => 'Time before allowing next invitation (in days)',
            ])
            ->add('shareScoreReq', null, [
                'label' => new TranslatableMessage('Share Score Requirement'),
            ])
            ->add('accountAgeReq', null, [
                'label' => new TranslatableMessage('Account Age Requirement'),
                'help' => 'in days',
            ])
            ->add('manageReq', null, [
                'label' => new TranslatableMessage('Managed sharables Requirement'),
                'help' => 'disabled sharables does not count',
            ])
            ->add('validatedReq', null, [
                'label' => new TranslatableMessage('Validation given Requirement'),
                'help' => 'number of validation send by the user',
            ])
            ->add('verifiedReq', null, [
                'label' => new TranslatableMessage('Email verification Requirement'),
            ])
            ->add('avatarReq', null, [
                'label' => new TranslatableMessage('Avatar picture requirement'),
            ])
            ->add('visibleBy', null, [
                'label' => 'Visible by',
                'help' => 'can be selected as visibleBy in a sharable',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $userClass = $event->getData();
            assert($userClass instanceof UserClass);
            $form = $event->getForm();



            if ($userClass->getId() === null) {
                $form->add('create', SubmitType::class);
            } else {
                $form->add('edit', SubmitType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserClass::class,
        ]);
    }
}
