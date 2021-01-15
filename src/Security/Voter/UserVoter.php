<?php

namespace App\Security\Voter;

use App\Entity\Interested;
use App\Entity\Manage;
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
        1 => [self::VIEW_INTERESTEDS],
        2 => [self::VIEW_INTERESTEDS, self::VIEW_VALIDATIONS],
        3 => [self::VIEW_INTERESTEDS, self::VIEW_VALIDATIONS, self::VIEW_SHARABLES],
        4 => [self::VIEW_INTERESTEDS, self::VIEW_VALIDATIONS, self::VIEW_SHARABLES, self::VIEW_STATS],
    ];

    public const VIEW_STATS = 'view_stats';
    public const VIEW_INTERESTEDS = 'view_interesteds';
    public const VIEW_VALIDATIONS = 'view_validations';
    public const VIEW_SHARABLES = 'view_sharables';
    public const VIEW     = 'view';
    public const EDIT     = 'edit';
    public const CONTACT = 'contact';

    /** @var EntityManagerInterface $em */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    /**
     * Return all the paranoïa levels listed in the PARANOIA constant
     */
    static public function getParanoiaLevels(): array
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
            self::VIEW_VALIDATIONS,
            self::VIEW_SHARABLES,
            self::VIEW_STATS,
            self::VIEW_INTERESTEDS,
            self::CONTACT,
            ])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
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
                return true;
            case self::VIEW_INTERESTEDS:
                return $this->canView($user, $userProfile, self::VIEW_INTERESTEDS);
            case self::VIEW_VALIDATIONS:
                return $this->canView($user, $userProfile, self::VIEW_VALIDATIONS);
            case self::VIEW_SHARABLES:
                return $this->canView($user, $userProfile, self::VIEW_SHARABLES);
            case self::VIEW_STATS:
                return $this->canView($user, $userProfile, self::VIEW_STATS);
            case self::CONTACT:
                return $this->canContact($user, $userProfile);
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

    private function canContact(User $user, User $userProfile): bool
    {
        if ($this->canEdit($user, $userProfile)) {
            return true;
        }
        if (!$user->getManages()->isEmpty() && !$userProfile->getInteresteds()->isEmpty()) {
            $sharableRepo = $this->em->getRepository(Sharable::class);
            assert($sharableRepo instanceof SharableRepository);
            $sharables = $sharableRepo->findByManagerAndInterested($user, $userProfile);

            //TODO check if sharable end is passed
            $filteredSharables = $sharables->filter(function (Sharable $sharable) {
                return ($sharable->getInterestedMethod() > 1 && !$sharable->getDisabled() );
            });
            return !$filteredSharables->isEmpty();
        }
        if (!$userProfile->getManages()->isEmpty() && !$user->getInteresteds()->isEmpty()) {
            $sharableRepo = $this->em->getRepository(Sharable::class);
            assert($sharableRepo instanceof SharableRepository);
            $sharables = $sharableRepo->findByManagerAndInterested($userProfile, $user);

            //TODO check if sharable end is passed
            $filteredSharables = $sharables->filter(function (Sharable $sharable) use ($user) {
                if (
                    $sharable->getDisabled() ||
                    $sharable->getInterestedMethod() === 1 ||
                    $sharable->getInterestedMethod() === 4
                ) {
                    return false;
                }
                if ($sharable->getContactableManagers()->isEmpty()) {
                    return false;
                }
                if ($sharable->getInterestedMethod() === 3) {
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
