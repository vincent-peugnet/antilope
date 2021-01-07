<?php

namespace App\Security\Voter;

use App\Entity\Manage;
use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\Validation;
use App\Repository\UserClassRepository;
use App\Repository\ValidationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SharableVoter extends Voter
{
    private $em;

    const VIEW       = 'view';
    const EDIT       = 'edit';
    const VALIDATE   = 'validate';
    const CREATE     = 'create';
    const INTERESTED = 'interested';

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::EDIT, self::VALIDATE, self::CREATE, self::INTERESTED])
            && $subject instanceof \App\Entity\Sharable;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Sharable $sharable */
        $sharable = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($sharable, $user);
                break;
            case self::VIEW:
                return $this->canView($sharable, $user);
                break;
            case self::VALIDATE:
                return $this->canValidate($sharable, $user);
                break;
            case self::CREATE:
                return $this->canCreate($user);
                break;
            case self::INTERESTED:
                return $this->canBeInterested($sharable, $user);
                break;
        }

        return false;
    }

    private function canEdit(Sharable $sharable, User $user): bool
    {
        $manageRepository = $this->em->getRepository(Manage::class);
        return (bool) $manageRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);
    }

    private function canView(Sharable $sharable, User $user): bool
    {
        if ($this->canEdit($sharable, $user)) {
            return true;
        }
        if (null !== $sharable->getVisibleBy()) {
            if ($user->getUserClass()->getRank() >= $sharable->getVisibleBy()->getRank()) {
                return true;
            } else {
                return false;
            }
        }
        if ($user->getUserClass()->getAccess()) {
            return true;
        }
        return false;
    }

    private function canBeInterested(Sharable $sharable, User $user): bool
    {
        if ($sharable->getInterestedMethod() === 1) {
            return false;
        } else {
            return true;
        }
    }

    private function canValidate(Sharable $sharable, User $user): bool
    {
        // if users can edit, this mean they manage the sharable
        if ($this->canEdit($sharable, $user)) {
            return false;
        }

        // Check if the begin date is already passed
        if (!empty($sharable->getBeginAt())) {
            if ($sharable->getBeginAt() < new DateTime()) {
                $passed = true;
            } else {
                $passed = false;
            }
        } else {
            $passed = true;
        }


        if ($this->canView($sharable, $user)
            && !$sharable->getDisabled()
            && $passed
            ) {

             /** @var ValidationRepository  */
            $validationRepo = $this->em->getRepository(Validation::class);
            
            $alreadyValidated = $validationRepo->count([
                'user' => $user->getId(),
                'sharable' => $sharable->getId(),
            ]);
            if (!$alreadyValidated) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function canCreate(User $user): bool
    {
        return $user->getUserClass()->getShare();
    }
}
