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

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Validation;
use App\Repository\ReportValidationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ValidationVoter extends Voter
{
    public const REPORT       = 'report';
    public const VIEW_REPORTS = 'view_reports';

    private AuthorizationCheckerInterface $authorization;
    private ReportValidationRepository $reportValidationRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ReportValidationRepository $reportValidationRepository
    ) {
        $this->authorization = $authorization;
        $this->reportValidationRepository = $reportValidationRepository;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::REPORT, self::VIEW_REPORTS])
            && $subject instanceof \App\Entity\Validation;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::REPORT:
                return $this->canReport($subject, $user);
            case self::VIEW_REPORTS:
                return $this->canViewReports($subject, $user);
        }

        return false;
    }

    private function canReport(Validation $validation, User $user): bool
    {
        return (
            $this->authorization->isGranted(SharableVoter::VIEW, $validation->getSharable()) &&
            !$this->alreadyReported($validation, $user)
        );
    }

    private function canViewReports(Validation $validation, User $user): bool
    {
        return (
            $user->getRole() >= User::ROLE_MODERATOR &&
            $validation->getUser() !== $user
        );
    }

    private function alreadyReported(Validation $validation, User $user): bool
    {
        return (bool) $this->reportValidationRepository->findOneBy([
            'validation' => $validation->getId(),
            'user' => $user->getId(),
        ]);
    }
}
