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

use App\Event\ShareScoreEvent;
use App\Event\ValidationEvent;
use App\Service\LevelUp;
use App\Service\SharePoints;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ShareScoreSubscriber implements EventSubscriberInterface
{
    private SharePoints $sharePoints;
    private EntityManagerInterface $em;
    private LevelUp $levelUp;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        SharePoints $sharePoints,
        EntityManagerInterface $em,
        LevelUp $levelUp,
        EventDispatcherInterface $dispatcher
    ) {
        $this->sharePoints = $sharePoints;
        $this->em = $em;
        $this->levelUp = $levelUp;
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            ValidationEvent::NEW => 'onValidationNew',
        ];
    }

    public function onValidationNew(ValidationEvent $event): void
    {
        $sharable = $event->getValidation()->getSharable();
        $user = $event->getValidation()->getUser();
        $sharePoints = $this->sharePoints->calculate($user, $sharable);

        foreach ($sharable->getConfirmedNotDisabledManagers() as $manage) {
            $manager = $manage->getUser();
            $manager->addShareScore($sharePoints);
            $this->em->persist($manager);
            $this->dispatcher->dispatch(new ShareScoreEvent($manager, $sharePoints), ShareScoreEvent::UPDATE);
        }

        $this->em->flush();
    }
}
