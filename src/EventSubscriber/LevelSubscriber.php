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

namespace App\EventSubscriber;

use App\Entity\Manage;
use App\Entity\User;
use App\Event\ManageEvent;
use App\Event\SharableEvent;
use App\Event\UserEvent;
use App\Event\ShareScoreEvent;
use App\Event\UserClassEvent;
use App\Event\ValidationEvent;
use App\Service\LevelUp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LevelSubscriber implements EventSubscriberInterface
{
    private LevelUp $levelUp;
    private EntityManagerInterface $em;
    private EventDispatcherInterface $dispatcher;

    public function __construct(LevelUp $levelUp, EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        $this->levelUp = $levelUp;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            ShareScoreEvent::UPDATE => 'onShareScoreUpdate',
            ValidationEvent::NEW => ['onValidationNew', -10],
            SharableEvent::ENABLE => 'onSharableEnable',
            SharableEvent::DISABLE => 'onSharableEnable',
            ManageEvent::NEW => 'onManageNew',
            ManageEvent::CONFIRM => 'onManageNew',
            UserEvent::AVATAR => 'onUserAvatar',
            UserClassEvent::EDIT => 'onUserClassEdit',
        ];
    }

    public function onShareScoreUpdate(ShareScoreEvent $event): void
    {
        $user = $event->getUser();
        $this->check($user);
    }

    public function onValidationNew(ValidationEvent $event): void
    {
        $user = $event->getValidation()->getUser();
        $this->check($user);
    }

    public function onSharableEnable(SharableEvent $sharableEvent): void
    {
        $manages = $sharableEvent->getSharable()->getConfirmedManagers();
        $users = $manages->map(function (Manage $manage) {
            return $manage->getUser();
        });
        foreach ($users as $user) {
            assert($user instanceof User);
            $this->check($user);
        }
    }

    public function onManageNew(ManageEvent $manageEvent): void
    {
        $user = $manageEvent->getManage()->getUser();
        $this->check($user);
    }

    public function onUserAvatar(UserEvent $userEvent): void
    {
        $user = $userEvent->getUser();
        $this->check($user);
    }

    public function onUserClassEdit(UserClassEvent $userClassEvent): void
    {
        $userClass = $userClassEvent->getUserClass();
        foreach ($userClass->getUsers() as $user) {
            assert($user instanceof User);
            $this->check($user);
        }
        if (!is_null($userClass->getPrev())) {
            foreach ($userClass->getPrev()->getUsers() as $user) {
                assert($user instanceof User);
                $this->check($user);
            }
        }
    }

    private function check(User $user): bool
    {
        $change = $this->levelUp->check($user);

        if ($change) {
            $this->em->persist($user);
            $this->em->flush();
            $this->dispatcher->dispatch(new UserEvent($user), UserEvent::USERCLASS_UPDATE);
        }
        return $change;
    }
}
