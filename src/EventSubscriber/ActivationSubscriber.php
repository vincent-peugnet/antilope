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

use App\Entity\Sharable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\UserEvent;
use Doctrine\ORM\EntityManagerInterface;

class ActivationSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvent::DISABLED => 'onUserDisabled',
        ];
    }
    public function onUserDisabled(UserEvent $event)
    {
        $manages = $event->getUser()->getConfirmedEnabledManages();
        foreach ($manages as $manage) {
            $sharable = $manage->getSharable();
            if ($sharable->getConfirmedNotDisabledManagers()->count() === 0) {
                $sharable->setDisabled(true);
                $this->em->persist($sharable);
            }
        }
        $this->em->flush();
    }
}
