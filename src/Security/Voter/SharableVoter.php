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
use App\Entity\Manage;
use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
use App\Entity\Validation;
use App\Repository\InterestedRepository;
use App\Repository\ManageRepository;
use App\Repository\ReportSharableRepository;
use App\Repository\UserClassRepository;
use App\Repository\ValidationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SharableVoter extends Voter
{
    public const VIEW         = 'view';
    public const EDIT         = 'edit';
    public const VALIDATE     = 'validate';
    public const CREATE       = 'create';
    public const INTEREST     = 'interest';
    public const INTERESTED   = 'interested';
    public const CONTACT      = 'contact';
    public const QUESTION     = 'question';
    public const GEO          = 'geo';
    public const REPORT       = 'report';
    public const VIEW_REPORTS = 'view_reports';

    private UserClassRepository $userClassRepository;
    private InterestedRepository $interestedRepository;
    private ValidationRepository $validationRepository;
    private ManageRepository $manageRepository;
    private ReportSharableRepository $reportSharableRepository;

    public function __construct(
        UserClassRepository $userClassRepository,
        InterestedRepository $interestedRepository,
        ValidationRepository $validationRepository,
        ManageRepository $manageRepository,
        ReportSharableRepository $reportSharableRepository
    ) {
        $this->userClassRepository = $userClassRepository;
        $this->interestedRepository = $interestedRepository;
        $this->validationRepository = $validationRepository;
        $this->manageRepository = $manageRepository;
        $this->reportSharableRepository = $reportSharableRepository;
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
            self::QUESTION,
            self::GEO,
            self::REPORT,
            self::VIEW_REPORTS
        ])
            && $subject instanceof \App\Entity\Sharable;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
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
            case self::QUESTION:
                return $this->canQuestion($sharable, $user);
            case self::GEO:
                return $this->canViewGeo($sharable, $user);
            case self::REPORT:
                return $this->canReport($sharable, $user);
            case self::VIEW_REPORTS:
                return $this->canViewReports($sharable, $user);
        }

        return false;
    }

    private function canEdit(Sharable $sharable, User $user): bool
    {
        $manage = $this->canManage($sharable, $user);
        return ($manage !== null && $manage->getConfirmed() && !$user->isDisabled());
    }

    private function canView(Sharable $sharable, User $user): bool
    {
        if ($user->isDisabled()) {
            return false;
        } elseif ($this->canManage($sharable, $user)) {
            return true;
        }
        if (is_null($sharable->getVisibleBy())) {
            return $user->getUserClass()->getAccess();
        } else {
            $visibleByUserClasses = $this->userClassRepository->findBetween($sharable->getVisibleBy());
            return (in_array($user->getUserClass(), $visibleByUserClasses));
        }
    }

    private function canBeInterested(Sharable $sharable, User $user): bool
    {
        if ($this->canManage($sharable, $user)) {
            return false;
        }
        if (
            $sharable->isAccessible() &&
            !$sharable->isDisabled() &&
            $sharable->getInterestedMethod() > 1 &&
            $sharable->isContactable() &&
            $this->canView($sharable, $user) &&
            !$this->passedEnd($sharable) &&
            !$user->getUserContacts()->isEmpty() &&
            !$this->alreadyInterested($sharable, $user) &&
            !$this->alreadyValidated($sharable, $user)
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function canViewInterested(Sharable $sharable, User $user): bool
    {
        return (
            $this->canEdit($sharable, $user) &&
            $sharable->getInterestedMethod() > 1
        );
    }

    private function canViewContact(Sharable $sharable, User $user): bool
    {
        if (!$this->canView($sharable, $user)) {
            return false;
        }
        if ($sharable->getInterestedMethod() > 1 && $this->canEdit($sharable, $user)) {
            return true;
        }
        $interested = $this->alreadyInterested($sharable, $user);
        if (
            $sharable->isAccessible() &&
            !$sharable->isDisabled() &&
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

    private function canViewGeo(Sharable $sharable, User $user): bool
    {
        if (!$this->canView($sharable, $user)) {
            return false;
        }
        if ($sharable->getInterestedMethod() === 1) {
            return true;
        }
        $interested = $this->alreadyInterested($sharable, $user);
        if (
            $sharable->isAccessible() &&
            !$sharable->isDisabled() &&
            !is_null($interested) &&
            !$this->alreadyValidated($sharable, $user)
        ) {
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
            $sharable->isAccessible() &&
            !$sharable->isDisabled() &&
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
            !$user->isDisabled() &&
            $user->getUserClass()->getShare() &&
            !$user->getUserContacts()->isEmpty()
        );
    }

    private function canQuestion(Sharable $sharable, User $user): bool
    {
        return (
            $this->canView($sharable, $user) &&
            !$sharable->isDisabled() &&
            $sharable->isAccessible() &&
            $user->getUserClass()->getCanQuestion() &&
            !$this->canEdit($sharable, $user) &&
            !$this->alreadyValidated($sharable, $user)
        );
    }

    private function canReport(Sharable $sharable, User $user): bool
    {
        return (
            $this->canView($sharable, $user) &&
            $user->getUserClass()->getCanReport() &&
            !$this->canEdit($sharable, $user) &&
            !$this->alreadyReported($sharable, $user)
        );
    }

    private function canViewReports(Sharable $sharable, User $user)
    {
        return (
            $this->canView($sharable, $user) &&
            $user->getRole() >= User::ROLE_MODERATOR &&
            !$sharable->getReports()->isEmpty()
        );
    }

    //________________________________

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
    private function alreadyInterested(Sharable $sharable, User $user): ?Interested
    {
        return $this->interestedRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);
    }

    private function alreadyValidated(Sharable $sharable, User $user): bool
    {
        return (bool) $this->validationRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId(),
        ]);
    }

    /**
     * Check if Manage object exist
     * @return Manage|null
     */
    private function canManage(Sharable $sharable, User $user): ?Manage
    {
        return $this->manageRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);
    }

    private function alreadyReported(Sharable $sharable, User $user): bool
    {
        return (bool) $this->reportSharableRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId(),
        ]);
    }
}
