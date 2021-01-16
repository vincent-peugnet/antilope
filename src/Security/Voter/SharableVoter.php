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
 * PHP version 7.2 
 * 
 * @package Antilope 
 * @author Vincent Peugnet <vincent-peugnet@riseup.net> 
 * @copyright 2020-2021 Vincent Peugnet 
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later 
 */ 

namespace App\Security\Voter;

use App\Entity\Interested;
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

    public const VIEW       = 'view';
    public const EDIT       = 'edit';
    public const VALIDATE   = 'validate';
    public const CREATE     = 'create';
    public const INTEREST   = 'interest';
    public const INTERESTED = 'interested';
    public const CONTACT    = 'contact';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::VALIDATE,
            self::CREATE,
            self::INTEREST,
            self::INTERESTED,
            self::CONTACT,
        ])
            && $subject instanceof \App\Entity\Sharable;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
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
            case self::VIEW:
                return $this->canView($sharable, $user);
            case self::VALIDATE:
                return $this->canValidate($sharable, $user);
            case self::CREATE:
                return $this->canCreate($user);
            case self::INTEREST:
                return $this->canBeInterested($sharable, $user);
            case self::INTERESTED:
                return $this->canViewInterested($sharable, $user);
            case self::CONTACT:
                return $this->canViewContact($sharable, $user);
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
        if ($this->canEdit($sharable, $user)) {
            return false;
        }
        if (
            !$sharable->getDisabled() &&
            $sharable->getInterestedMethod() > 1 &&
            $this->canView($sharable, $user) &&
            !$this->passedEnd($sharable) &&
            !$user->getUserContacts()->isEmpty() &&
            !$this->alreadyInterested($sharable, $user)
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function canViewInterested(Sharable $sharable, User $user): bool
    {
        return ($this->canEdit($sharable, $user) && $sharable->getInterestedMethod() > 1);
    }

    private function canViewContact(Sharable $sharable, User $user): bool
    {
        if (!$this->canView($sharable, $user)) {
            return false;
        }
        if ($this->canEdit($sharable, $user)) {
            return true;
        }
        $interested = $this->alreadyInterested($sharable, $user);
        if (
            !$sharable->getDisabled() &&
            $interested &&
            !$this->alreadyValidated($sharable, $user)
        ) {
            assert($interested instanceof Interested);
            switch ($sharable->getInterestedMethod()) {
                case 2:
                    return true;
                case 3:
                    return ($interested->getReviewed());
                default:
                    return false;
            }
        }
        return false;
    }

    private function canValidate(Sharable $sharable, User $user): bool
    {
        if (!$this->canView($sharable, $user)) {
            return false;
        }
        if ($this->canEdit($sharable, $user)) {
            return false;
        }

        if (
            !$sharable->getDisabled() &&
            $this->passedBegin($sharable) &&
            $this->passedBegin($sharable) &&
            !$this->alreadyValidated($sharable, $user)
        ) {
            $interested = $this->alreadyInterested($sharable, $user);
            if ($sharable->getInterestedMethod() === 1) {
                return true;
            } elseif ($interested) {
                assert($interested instanceof Interested);
                if ($sharable->getInterestedMethod() === 3) {
                    return $interested->getReviewed();
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function canCreate(User $user): bool
    {
        return(
            $user->getUserClass()->getShare() &&
            !$user->getUserContacts()->isEmpty()
        );
    }


    private function passedBegin(Sharable $sharable): bool
    {
        if (!empty($sharable->getBeginAt())) {
            return ($sharable->getBeginAt() < new DateTime());
        } else {
            return true;
        }
    }

    private function passedEnd(Sharable $sharable): bool
    {
        if (!empty($sharable->getEndAt())) {
            return ($sharable->getEndAt() < new DateTime());
        } else {
            return false;
        }
    }

    /**
     * @return Interested|null
     */
    private function alreadyInterested(Sharable $sharable, User $user)
    {
        $interestedRepository = $this->em->getRepository(Interested::class);
        return $interestedRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);
    }

    private function alreadyValidated(Sharable $sharable, User $user): bool
    {
        $validationRepo = $this->em->getRepository(Validation::class);
        return (bool) $validationRepo->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId(),
        ]);
    }
}
