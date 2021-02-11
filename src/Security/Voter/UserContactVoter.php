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
use App\Entity\UserContact;
use DateInterval;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserContactVoter extends Voter
{
    public const VIEW    = 'view';
    public const EDIT    = 'edit';
    public const FORGET  = 'forget';
    public const DELETE  = 'delete';

    private int $contactForgetDelay;
    private int $contactEditDelay;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->contactForgetDelay = (int) $parameters->get('app.contactForgetDelay');
        $this->contactEditDelay = (int) $parameters->get('app.contactEditDelay');
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::FORGET, self::DELETE])
            && $subject instanceof \App\Entity\UserContact;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        assert($user instanceof User);
        assert($subject instanceof UserContact);

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::FORGET:
                return $this->canForget($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    private function canView(UserContact $userContact): bool
    {
        if (!$userContact->isForgotten()) {
            return true;
        } elseif ($this->contactForgetDelay === 0) {
            return false;
        } else {
            return $userContact->getForgottenAt() > new DateTime($this->contactForgetDelay . ' hours ago');
        }
    }

    private function canEdit(UserContact $userContact, User $user): bool
    {
        return (!$userContact->isForgotten() && $userContact->getUser() === $user && !$user->isDisabled());
    }

    private function canForget(UserContact $userContact, User $user): bool
    {
        return ($this->canEdit($userContact, $user) && $user->getNotForgottenUserContacts()->count() > 1);
    }

    private function canDelete(UserContact $userContact, User $user): bool
    {
        if ($this->canForget($userContact, $user)) {
            if ($user->getInteresteds()->isEmpty() && $user->getValidations()->isEmpty()) {
                return true;
            } else {
                return $userContact->getCreatedAt() > new DateTime($this->contactEditDelay . 'minutes ago');
            }
        } else {
            return false;
        }
    }
}
