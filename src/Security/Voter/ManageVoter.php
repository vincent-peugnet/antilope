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

use App\Entity\Manage;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ManageVoter extends Voter
{
    public const REMOVE       = 'remove';
    public const CONFIRM      = 'confirm';
    public const HIDE_CONTACT = 'hide_contact';
    public const SHOW_CONTACT = 'show_contact';
    public const ANONYMOUS    = 'anonymous';
    public const ONYMOUS      = 'onymous';

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [
            self::REMOVE,
            self::CONFIRM,
            self::HIDE_CONTACT,
            self::SHOW_CONTACT,
            self::ANONYMOUS,
            self::ONYMOUS,
        ]) && $subject instanceof \App\Entity\Manage;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $manage = $subject;
        assert($subject instanceof Manage);
        assert($user instanceof User);

        switch ($attribute) {
            case self::REMOVE:
                return $this->canRemove($manage, $user);
            case self::CONFIRM:
                return $this->canConfirm($manage, $user);
            case self::HIDE_CONTACT:
                return $this->canHideContact($manage, $user);
            case self::SHOW_CONTACT:
                return $this->canShowContact($manage, $user);
            case self::ANONYMOUS:
                return $this->canAnonymous($manage, $user);
            case self::ONYMOUS:
                return $this->canOnymous($manage, $user);
        }

        return false;
    }

    private function canRemove(Manage $manage, User $user): bool
    {
        $sharable = $manage->getSharable();
        if ($manage->getUser() === $user) {
            if ($manage->getConfirmed()) {
                return (
                    $sharable->getConfirmedManagers()->count() > 1 &&
                    (
                        !$manage->isContactable() ||
                        !$sharable->getSharableContacts()->isEmpty() ||
                        $sharable->getContactableManagers()->count() > 1
                    )
                );
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    private function canConfirm(Manage $manage, User $user): bool
    {
        return ($manage->getUser() === $user && !$manage->getConfirmed());
    }

    private function canHideContact(Manage $manage, User $user): bool
    {
        $sharable = $manage->getSharable();
        return (
            $this->isConfirmedManager($manage, $user) &&
            $manage->isContactable() &&
            (
                !$sharable->getSharableContacts()->isEmpty() ||
                $sharable->getContactableManagers()->count() > 1
            )
        );
    }

    private function canShowContact(Manage $manage, User $user): bool
    {
        return (
            $this->isConfirmedManager($manage, $user) &&
            !$manage->isContactable() &&
            !$user->getUserContacts()->isEmpty()
        );
    }

    private function canAnonymous(Manage $manage, User $user): bool
    {
        return ($manage->getUser() === $user && !$manage->isAnonymous());
    }

    private function canOnymous(Manage $manage, User $user): bool
    {
        return ($manage->getUser() === $user && $manage->isAnonymous());
    }

    private function isConfirmedManager(Manage $manage, User $user): bool
    {
        return ($manage->getUser() === $user && $manage->getConfirmed());
    }
}
