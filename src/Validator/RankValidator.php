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

namespace App\Validator;

use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class RankValidator extends ConstraintValidator
{
    /** @var UserClassRepository $userClassrepository */
    private UserClassRepository $userClassRepository;
    /** @var EntityManagerInterface $entityManager */
    private EntityManagerInterface $entityManager;
    /** @var bool $rankswap Allow User Class to be moved after next one or before previous one */
    private bool $rankSwap;

    public function __construct(
        UserClassRepository $userClassRepository,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ) {
        $this->userClassRepository = $userClassRepository;
        $this->entityManager = $entityManager;
        $this->rankSwap = $params->get('app.userClassRankSwap');
    }

    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof Rank);

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_int($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $alreadyExistingUserClass = $this->userClassRepository->findOneBy(['rank' => $value]);

        if ($alreadyExistingUserClass) {
            assert($alreadyExistingUserClass instanceof UserClass);
            $this->context->buildViolation($constraint->messageAlreadyExist)
            ->setParameter('{{ value }}', (string) $value)
            ->setParameter('{{ userClass }}', $alreadyExistingUserClass->getName())
            ->addViolation();
        }
        $userClass = $this->context->getRoot()->getData();
        assert($userClass instanceof UserClass && !$this->rankSwap);
        if ($userClass->getId() !== null) {
            $oldRank = $this->entityManager->getUnitOfWork()->getOriginalEntityData($userClass)['rank'];
            $oldUserClass = new UserClass();
            $oldUserClass->setRank($oldRank);
            $nextUserClass = $this->userClassRepository->findNext($oldUserClass);
            if ($nextUserClass !== null) {
                if ($value > $nextUserClass->getRank()) {
                    $this->context->buildViolation($constraint->messageMaxRank)
                    ->setParameter('{{ max }}', (string) $nextUserClass->getRank())
                    ->setParameter('{{ name }}', (string) $nextUserClass->getName())
                    ->addViolation();
                }
            }
            $previousUserClass = $this->userClassRepository->findPrevious($oldUserClass);
            if ($previousUserClass !== null) {
                if ($value < $previousUserClass->getRank()) {
                    $this->context->buildViolation($constraint->messageMinRank)
                    ->setParameter('{{ min }}', (string) $previousUserClass->getRank())
                    ->setParameter('{{ name }}', (string) $previousUserClass->getName())
                    ->addViolation();
                }
            }
        }
    }
}
