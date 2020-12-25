<?php

namespace App\Validator;

use App\Repository\InvitationRepository;
use DateInterval;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CodeValidator extends ConstraintValidator
{
    private $invitationRepository;
    private $invitationDuration;

    public function __construct(InvitationRepository $invitationRepository, ParameterBagInterface $params) {
        $this->invitationRepository = $invitationRepository;
        $this->invitationDuration = $params->get('app.invitationDuration');
    }


    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\Code */

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'string');

            // separate multiple types using pipes
            // throw new UnexpectedValueException($value, 'string|int');
        }

        $invitationDuration = new DateInterval(
            'PT' .$this->invitationDuration. 'H'
        );
        $invitation = $this->invitationRepository->findOneBy(['code' => $value]);

        if (!empty($invitation) && empty($invitation->getChild())) {
            $expireAt = $invitation->getCreatedAt()->add($invitationDuration);
            if ($expireAt < new DateTime()) {
                $this->context->buildViolation($constraint->messageExpired)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            }
        } else {
            $this->context->buildViolation($constraint->messageDoesNotExist)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
        }
    }
}
