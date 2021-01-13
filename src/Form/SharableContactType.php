<?php

namespace App\Form;

use App\Entity\SharableContact;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharableContactType extends ContactType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SharableContact::class,
        ]);
    }
}
