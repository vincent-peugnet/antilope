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

use App\Entity\Question;
use App\Entity\User;
use App\Repository\ValidationRepository;
use DateTime;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class QuestionVoter extends Voter
{
    public const VIEW = 'view';
    public const DELETE = 'delete';
    public const EDIT = 'edit';
    public const ANSWER = 'answer';

    private AuthorizationCheckerInterface $authorization;
    private ValidationRepository $validationRepository;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ValidationRepository $validationRepository
    ) {
        $this->authorization = $authorization;
        $this->validationRepository = $validationRepository;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::DELETE, self::ANSWER, self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Question;
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
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::ANSWER:
                return $this->canAnswer($subject, $user);
        }

        return false;
    }

    private function canView(Question $question, User $user): bool
    {
        return $this->authorization->isGranted(
            SharableVoter::VIEW,
            $question->getSharable()
        );
    }

    private function canDelete(Question $question, User $user): bool
    {
        if ($question->getUser() === $user && $question->getAnswers()->isEmpty()) {
            return true;
        } else {
            return $this->authorization->isGranted(
                SharableVoter::EDIT,
                $question->getSharable()
            );
        }
    }

    private function canAnswer(Question $question, User $user): bool
    {
        if (
            $question->getUser() !== $user &&
            $this->authorization->isGranted(
                SharableVoter::EDIT,
                $question->getSharable()
            )
        ) {
            return true;
        } else {
            $validation = $this->validationRepository->findOneBy([
                'sharable' => $question->getSharable()->getId(),
                'user' => $user,
            ]);
            if (!is_null($validation)) {
                return true;
            }
        }
        return false;
    }

    public function canEdit(Question $question, User $user): bool
    {
        if ($question->getUser() === $user && $question->getAnswers()->isEmpty()) {
            return ($question->getCreatedAt() > new DateTime('6 hours ago'));
        }
        return false;
    }
}
