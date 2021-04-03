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

use App\Entity\Interested;
use App\Entity\Sharable;
use App\Entity\User;
use App\Repository\SharableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    public const PARANOIA = [
        0 => [],
        1 => [self::VIEW_BOOKMARKS],
        2 => [self::VIEW_BOOKMARKS, self::VIEW_INTERESTEDS],
        3 => [self::VIEW_BOOKMARKS, self::VIEW_INTERESTEDS, self::VIEW_VALIDATIONS],
        4 => [self::VIEW_BOOKMARKS, self::VIEW_INTERESTEDS, self::VIEW_VALIDATIONS, self::VIEW_SHARABLES],
        5 => [
            self::VIEW_BOOKMARKS,
            self::VIEW_INTERESTEDS,
            self::VIEW_VALIDATIONS,
            self::VIEW_SHARABLES,
            self::VIEW_STATS
        ],
    ];

    public const VIEW_STATS       = 'view_stats';
    public const VIEW_BOOKMARKS   = 'view_bookmarks';
    public const VIEW_INTERESTEDS = 'view_interesteds';
    public const VIEW_VALIDATIONS = 'view_validations';
    public const VIEW_SHARABLES   = 'view_sharables';
    public const VIEW             = 'view';
    public const EDIT             = 'edit';
    public const CONTACT          = 'contact';

    /** @var EntityManagerInterface $em */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * Return all the paranoïa levels listed in the PARANOIA constant
     * @return array|int[]
     */
    public static function getParanoiaLevels(): array
    {
        return array_keys(self::PARANOIA);
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [
            self::EDIT,
            self::VIEW,
            self::VIEW_BOOKMARKS,
            self::VIEW_INTERESTEDS,
            self::VIEW_VALIDATIONS,
            self::VIEW_SHARABLES,
            self::VIEW_STATS,
            self::CONTACT,
            ])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var User */
        $userProfile = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($user, $userProfile);
            case self::VIEW:
                return $this->canView($user, $userProfile);
            case self::VIEW_BOOKMARKS:
                return $this->canViewSpecific($user, $userProfile, self::VIEW_BOOKMARKS);
            case self::VIEW_INTERESTEDS:
                return $this->canViewSpecific($user, $userProfile, self::VIEW_INTERESTEDS);
            case self::VIEW_VALIDATIONS:
                return $this->canViewSpecific($user, $userProfile, self::VIEW_VALIDATIONS);
            case self::VIEW_SHARABLES:
                return $this->canViewSpecific($user, $userProfile, self::VIEW_SHARABLES);
            case self::VIEW_STATS:
                return $this->canViewSpecific($user, $userProfile, self::VIEW_STATS);
            case self::CONTACT:
                return $this->canContact($user, $userProfile);
        }

        return false;
    }

    private function canEdit(User $user, User $userProfile): bool
    {
        return ($user === $userProfile && !$user->isDisabled());
    }

    private function canView(User $user, User $userProfile): bool
    {
        return (!$user->isDisabled() || $user === $userProfile);
    }

    private function canViewSpecific(User $user, User $userProfile, string $view): bool
    {
        if ($this->canEdit($user, $userProfile)) {
            return true;
        } elseif (in_array($view, self::PARANOIA[$userProfile->getParanoia()])) {
            return false;
        } else {
            return true;
        }
    }

    private function canContact(User $user, User $userProfile): bool
    {
        if ($this->canEdit($user, $userProfile)) {
            return true;
        }
        if ($user->isDisabled() || $userProfile->isDisabled()) {
            return false;
        }

        $sharableRepo = $this->em->getRepository(Sharable::class);
        assert($sharableRepo instanceof SharableRepository);

        // Check if $userProfile is interested by a sharable managed by $user
        if (!$user->getManages()->isEmpty() && !$userProfile->getInteresteds()->isEmpty()) {
            $sharables = $sharableRepo->findByManagerAndInterested($user, $userProfile);

            //TODO check if sharable end is passed
            $filteredSharables = $sharables->filter(function (Sharable $sharable) {
                return ($sharable->getInterestedMethod() > 1 && !$sharable->isDisabled() && $sharable->isAccessible());
            });
            return !$filteredSharables->isEmpty();
        }

        // Check if $user is interested by a sharable managed by $userProfile
        if (!$userProfile->getManages()->isEmpty() && !$user->getInteresteds()->isEmpty()) {
            $sharables = $sharableRepo->findByManagerAndInterested($userProfile, $user);

            //TODO check if sharable end is passed
            $filteredSharables = $sharables->filter(function (Sharable $sharable) use ($user) {
                if (
                    $sharable->isDisabled() ||
                    !$sharable->isAccessible() ||
                    $sharable->getInterestedMethod() === 1 ||
                    $sharable->getInterestedMethod() === 4 ||
                    $sharable->getContactableManagers()->isEmpty()
                ) {
                    return false;
                }
                if ($sharable->getInterestedMethod() === 3 && $sharable->isAccessible()) {
                    $reviwedInterest = $sharable->getInteresteds()->filter(
                        function (Interested $interested) use ($user) {
                            return ($interested->getUser() === $user && $interested->getReviewed());
                        }
                    );
                    return !$reviwedInterest->isEmpty();
                } else {
                    return true;
                }
            });
            return !$filteredSharables->isEmpty();
        }
        return false;
    }
}
