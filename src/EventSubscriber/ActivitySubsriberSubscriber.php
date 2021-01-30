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

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Security;

class ActivitySubsriberSubscriber implements EventSubscriberInterface
{
    private Security $security;
    private EntityManagerInterface $em;
    private ParameterBagInterface $parameterBag;

    public function __construct(Security $security, EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->security = $security;
        $this->em = $em;
        $this->parameterBag = $parameterBag;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        if ($event->isMasterRequest()) {
            $user = $this->security->getUser();
            $delay = $this->parameterBag->get('app.lastActivityDelay');
            if (($user instanceof User) && $user->getLastActivity() < new DateTime("$delay minute ago")) {
                $user->setLastActivity(new DateTime());
                $this->em->persist($user);
                $this->em->flush();
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }
}
