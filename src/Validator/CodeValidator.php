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
 * PHP version 7.2
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

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
    private InvitationRepository $invitationRepository;
    private int $invitationDuration;

    public function __construct(InvitationRepository $invitationRepository, ParameterBagInterface $params)
    {
        $this->invitationRepository = $invitationRepository;
        $this->invitationDuration = (int) $params->get('app.invitationDuration');
    }


    public function validate($value, Constraint $constraint): void
    {
        assert($constraint instanceof Code);

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
            'PT' . $this->invitationDuration . 'H'
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
