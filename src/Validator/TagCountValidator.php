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

use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class TagCountValidator extends ConstraintValidator
{
    private ?int $min = null;
    private ?int $max = null;

    public function __construct(ParameterBagInterface $params)
    {
        $this->min = (int) $params->get('app.min_tag') ?? null;
        $this->max = (int) $params->get('app.max_tag') ?? null;
    }

    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof TagCount);
        if (null === $value || '' === $value) {
            return;
        }

        if (! $value instanceof Collection) {
            throw new UnexpectedValueException($value, 'Collection');
        }

        if ($this->min && count($value) < $this->min) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('{{ min }}', (string) $this->min)
                ->addViolation();
        }

        if ($this->max && count($value) > $this->max) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('{{ max }}', (string) $this->max)
                ->addViolation();
        }
    }
}
