<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Code extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'The code "{{ value }}" is not valid.';
    public $messageExpired = 'The code you are using is expired';
    public $messageDoesNotExist = 'This code does not exist or have already been used';
}
