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

namespace App\Service;

use App\Entity\User;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class InvitationService
{
    private $userLimit;
    private $userRepository;
    private $invitationRepository;
    private $openRegistration;
    private $invitationDuration;

    public function __construct(
        ParameterBagInterface $params,
        UserRepository $userRepository,
        InvitationRepository $invitationRepository
    ) {
        $this->userLimit = (int) $params->get('app.userLimit');
        $this->openRegistration = (bool) $params->get('app.openRegistration');
        $this->invitationDuration = (int) $params->get('app.invitationDuration');
        $this->userRepository = $userRepository;
        $this->invitationRepository = $invitationRepository;
    }

    /**
     * Indicate if user limit is reached
     */
    public function userLimitReached(): bool
    {
        if (!empty($this->userLimit)) {
            $userCount = $this->userRepository->count([]);
            return $userCount >= $this->userLimit;
        } else {
            return false;
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canInvite(User $user): bool
    {
        return (
            !$this->openRegistration &&
            !$this->userLimitReached() &&
            !$this->needToWait($user)
        );
    }

    /**
     * Indicate if the user need to wait before generating new invitation
     * @param User $user
     * @return bool
     */
    public function needToWait(User $user): bool
    {
        if ($user->getUserClass()->getInviteFrequency() !== 0) {
            $inviteFrequency = new DateInterval('P' . $user->getUserClass()->getInviteFrequency() . 'D');
            $lastInvitation = $this->invitationRepository->findOneBy(
                ['parent' => $user->getId()],
                ['createdAt' => 'DESC']
            );
            if (!empty($lastInvitation)) {
                $minInviteDate = $lastInvitation->getCreatedAt()->add($inviteFrequency);
                return $minInviteDate >= new DateTime();
            }
        }
        return false;
    }

    public function getopenRegistration(): bool
    {
        return $this->openRegistration;
    }

    public function getInvitationDuration(): DateInterval
    {
        return new DateInterval(
            'PT' . $this->invitationDuration . 'H'
        );
    }
}
