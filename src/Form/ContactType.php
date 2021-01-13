<?php

namespace App\Form;

use App\Entity\Contact;
use App\Entity\UserContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => Contact::allowedTypes(),
                'placeholder' => 'Choose your contact type',
                'required' => true,
            ])
            ->add('content', TextType::class, [
                'required' => true,
                'help' => 'Yout phone number, email adress, or wathever you whant to be join with',
            ])
            ->add('info', TextareaType::class, [
                'help' => 'You can precise some infos if you feel the need',
                'required' => false,
            ])
            ->add('add', SubmitType::class)
        ;
    }
}
