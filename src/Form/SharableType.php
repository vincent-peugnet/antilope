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

use App\Entity\Sharable;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserClass;
use App\Repository\TagRepository;
use App\Repository\UserClassRepository;
use App\Validator\TagCount;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Image;

class SharableType extends AbstractType
{
    private UserClassRepository $userClassRepository;
    private TagRepository $tagRepository;

    public function __construct(UserClassRepository $userClassRepository, TagRepository $tagRepository)
    {
        $this->userClassRepository = $userClassRepository;
        $this->tagRepository = $tagRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sharable = $builder->getData();
        assert($sharable instanceof Sharable);
        $disableVisibleBy = (bool) $options['disableVisibleBy'];

        $builder
            ->add('name')
            ->add('coverFile', FileType::class, [
                'label' => new TranslatableMessage('cover'),
                'help' => 'Jpeg, Png or Gif of maximum 2Mo',
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
            ->add('description')
            ->add('details', null, [
                'required' => true,
                'help' => 'Long description, where you can use Markdown <i class="fab fa-markdown"></i>',
                'help_html' => true,
                'attr' => [
                    'data-provide' => 'markdown',
                    'data-iconlibrary' => 'fa',
                    'data-resize' => 'vertical',
                    'data-fullscreen' => '{enable: false}',
                ],
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'constraints' => [new TagCount()],
            ])
            ->add('visibleBy', EntityType::class, [
                'class' => UserClass::class,
                'choices' => $this->userClassRepository->findAll(),
                'placeholder' => '',
                'required' => false,
                // phpcs:ignore Generic.Files.LineLength.TooLong
                'help' => 'This will indicate the user class from which user could access this sharable. This will override the the default behavior.',
                'disabled' => $disableVisibleBy,
            ])
            ->add('interestedMethod', ChoiceType::class, [
                'label' => new TranslatableMessage('How contact infos are exchanged'),
                'choices' => Sharable::INTERESTED_OPTIONS,
                    // phpcs:ignore Generic.Files.LineLength.TooLong
                'help' => '1 <i class="fas fa-lock-open fa-fw"></i> user don\'t need access any contact infos</br>2 <i class="fas fa-bolt fa-fw"></i> contact infos are automaticaly exchanged</br>3 <i class="fas fa-stamp fa-fw"></i> contact infos are manualy send to interested users</br>4 <i class="fas fa-lock fa-fw"></i> Only interested user contact infos are send',
                'help_html' => true,
            ])
            ->add('latitude', NumberType::class, [
                'html5' => true,
                'scale' => 7,
                'required' => false,
                'attr' => [
                    'min' => -90,
                    'max' => 90,
                    'step' => 0.0000001,
                ],
            ])
            ->add('longitude', NumberType::class, [
                'html5' => true,
                'scale' => 7,
                'required' => false,
                'attr' => [
                    'min' => -90,
                    'max' => 90,
                    'step' => 0.0000001,
                ],
            ])
            ->add('radius', ChoiceType::class, [
                'help' => 'you can anonymise your location using a circle instead of point',
                'choices' => Sharable::RADIUS_OPTIONS,
                'required' => true,
            ])
            ->add('beginAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required'   => false,
                'help' => 'If what you\'re sharing have a begin date, indicate it',
            ])
            ->add('endAt', DateTimeType::class, [
                'widget' => 'single_text',
                'required'   => false,
                'help' => 'If what you\'re sharing have a end date, indicate it',
            ])
        ;


        if ($sharable->getId() && !( empty($sharable->getBeginAt()) || $sharable->getBeginAt() > new DateTime())) {
            $builder->get('beginAt')->setDisabled(true);
        }

        if ($sharable->getId() && !( empty($sharable->getEndAt()) || $sharable->getEndAt() > new DateTime())) {
            $builder->get('endAt')->setDisabled(true);
        }


        if ($sharable->getId() === null) {
            $builder->add('create', SubmitType::class);
        } else {
            $builder->add('edit', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sharable::class,
            'disableVisibleBy' => true,
        ]);

        $resolver->setAllowedTypes('disableVisibleBy', 'bool');
    }
}
