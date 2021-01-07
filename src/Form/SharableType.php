<?php

namespace App\Form;

use App\Entity\Sharable;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('details', null, [
                'required' => true,
                'help' => 'Long description, where you can use Markdown'
            ])
            ->add('disabled', null, [
                'help' => 'check this if the thing you\'re sharing is not available anymore (but can be available in the future)',
            ])
            ->add('responsibility', null, [
                'help' => 'check this if you feel responsible for the sharable',
            ])
            ->add('visibleBy', null, [
                'placeholder' => '',
                'help' => 'Your sharable will be accessible from this user class',
            ])
            ->add('interestedMethod', ChoiceType::class, [
                'label' => 'How contact infos are exchanged',
                'choices' => Sharable::INTERESTED_OPTIONS,
                'help' => '1: user don\'t need access any contact infos</br>2: contact infos are automaticaly exchanged</br>3: contact infos are manualy send to interested users</br>4: Only interested user contact infos are send',
                'help_html' => true,
            ])
        ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Sharable */
            $sharable = $event->getData();
            $form = $event->getForm();

            if (empty($sharable->getBeginAt()) || $sharable->getBeginAt() > new DateTime()) {
                $form->add('beginAt', DateTimeType::class, [
                    'widget' => 'single_text',
                    'required'   => false,
                    'help' => 'If what you\'re sharing have a begin date, indicate it',
                ]);
            }
            if (empty($sharable->getEndAt()) || $sharable->getEndAt() > new DateTime()) {
                $form->add('endAt', DateTimeType::class, [
                    'widget' => 'single_text',
                    'required'   => false,
                    'help' => 'If what you\'re sharing have a end date, indicate it',
                ]);
            }
            
            if (!$sharable || $sharable->getId() === null) {
                $form->add('create', SubmitType::class);
            } else {
                $form->add('edit', SubmitType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sharable::class,
        ]);
    }
}
