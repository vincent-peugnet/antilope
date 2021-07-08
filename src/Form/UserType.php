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

use App\Entity\User;
use App\Security\Voter\UserVoter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User */
        $user = $builder->getData();


        $builder
            ->add('avatarFile', FileType::class, [
                'label' => new TranslatableMessage('Avatar'),
                'mapped' => false,
                'required' => false,
                'constraints' => new Image([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Please upload a JPEG, PNG or GIF image file',
                ])
            ])
            ->add('description', null, [
                'label' => new TranslatableMessage('description'),
                'help' => 'You can use Markdown to describe yourself.',
                'attr' => [
                    'data-provide' => 'markdown',
                    'data-iconlibrary' => 'fa',
                    'data-resize' => 'vertical',
                    'data-fullscreen' => '{enable: false}',
                ],
            ])
            ->add('paranoia', ChoiceType::class, [
                'label' => new TranslatableMessage('paranoïa level'),
                'choice_loader' => new CallbackChoiceLoader(
                    function () use ($user) {
                        return array_slice(
                            UserVoter::getParanoiaLevels(),
                            0,
                            $user->getUserClass()->getMaxParanoia() + 1
                        );
                    }
                ),
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'help' => '0 all users can see your profile</br>1 hide bookmarks</br>2 hide bookmarks, interested</br>3 hide bookmarks, interested, validations</br>4 hide bookmarks, interested, validations, sharables</br>5 hide bookmarks, interested, validations, sharables, stats',
                'help_html' => true,
            ])
            ->add('edit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
