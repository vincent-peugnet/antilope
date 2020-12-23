<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    const PARANOIA = [
        0 => [],
        1 => [self::VIEW_VALIDATIONS],
        2 => [self::VIEW_VALIDATIONS, self::VIEW_SHARABLES],
        3 => [self::VIEW_VALIDATIONS, self::VIEW_SHARABLES, self::VIEW_STATS],
    ];

    const VIEW_STATS = 'view_stats';
    const VIEW_VALIDATIONS = 'view_validations';
    const VIEW_SHARABLES = 'view_sharables';
    const VIEW     = 'view';
    const EDIT     = 'edit';

    /**
     * Return all the paranoïa levels listed in the PARANOIA constant
     */
    static function getParanoiaLevels(): array
    {
        return array_keys(self::PARANOIA);
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::VIEW_VALIDATIONS, self::VIEW_SHARABLES, self::VIEW_STATS])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
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
                break;
            case self::VIEW:
                return true;
                break;
            case self::VIEW_VALIDATIONS:
                return $this->canView($user, $userProfile, self::VIEW_VALIDATIONS);
                break;
            case self::VIEW_SHARABLES:
                return $this->canView($user, $userProfile, self::VIEW_SHARABLES);
                break;
            case self::VIEW_STATS:
                return $this->canView($user, $userProfile, self::VIEW_STATS);
                break;
        }

        return false;
    }

    private function canEdit(User $user, User $userProfile): bool
    {
        return ($user === $userProfile);
    }

    private function canView(User $user, User $userProfile, string $view): bool
    {
        if ($this->canEdit($user, $userProfile)) {
            return true;
        } elseif (in_array($view, self::PARANOIA[$userProfile->getParanoia()])) {
            return false;
        } else {
            return true;
        }
    }
}
