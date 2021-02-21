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
use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use DateTime;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharableType extends AbstractType
{
    private UserClassRepository $userClassRepository;

    public function __construct(UserClassRepository $userClassRepository)
    {
        $this->userClassRepository = $userClassRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sharable = $builder->getData();
        assert($sharable instanceof Sharable);

        $builder
            ->add('name')
            ->add('description')
            ->add('details', null, [
                'required' => true,
                'help' => 'Long description, where you can use Markdown <i class="fab fa-markdown"></i>',
                'help_html' => true,
            ])
            ->add('visibleBy', EntityType::class, [
                'class' => UserClass::class,
                'choices' => $this->userClassRepository->findAll(),
                'placeholder' => '',
                'required' => false,
                'help' => 'But you can overide this with your own choice if you feel the need',
            ])
            ->add('interestedMethod', ChoiceType::class, [
                'label' => 'How contact infos are exchanged',
                'choices' => Sharable::INTERESTED_OPTIONS,
                    // phpcs:ignore Generic.Files.LineLength.TooLong
                'help' => '1 <i class="fas fa-lock-open fa-fw"></i> user don\'t need access any contact infos</br>2 <i class="fas fa-bolt fa-fw"></i> contact infos are automaticaly exchanged</br>3 <i class="fas fa-stamp fa-fw"></i> contact infos are manualy send to interested users</br>4 <i class="fas fa-lock fa-fw"></i> Only interested user contact infos are send',
                'help_html' => true,
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
        ]);
    }
}
