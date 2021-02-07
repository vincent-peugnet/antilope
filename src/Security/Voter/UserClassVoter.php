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
use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserClassVoter extends Voter
{
    public const EDIT   = 'edit';
    public const CREATE = 'create';
    public const DELETE = 'delete';

    private UserClassRepository $userClassRepository;

    public function __construct(UserClassRepository $userClassRepository)
    {
        $this->userClassRepository = $userClassRepository;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::CREATE, self::DELETE])
            && $subject instanceof \App\Entity\UserClass;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        assert($user instanceof User);
        assert($subject instanceof UserClass);

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($user);
            case self::CREATE:
                return $this->canCreate($user);
            case self::DELETE:
                return $this->canDelete($user);
        }

        return false;
    }

    private function canEdit(User $user): bool
    {
        return $user->isAdmin();
    }

    private function canCreate(User $user): bool
    {
        return $user->isAdmin();
    }

    private function canDelete(User $user): bool
    {
        return ($user->isAdmin() && $this->userClassRepository->count([]) > 1);
    }
}
