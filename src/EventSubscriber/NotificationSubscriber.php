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
use App\Event\InterestedEvent;
use App\Event\InvitationEvent;
use App\Event\ManageEvent;
use App\Event\QuestionEvent;
use App\Event\UserEvent;
use App\Event\ValidationEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class NotificationSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;
    private ParameterBagInterface $parameters;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameters)
    {
        $this->mailer = $mailer;
        $this->parameters = $parameters;
    }

    public static function getSubscribedEvents()
    {
        return [
            InterestedEvent::NEW => ['onInterestedNew', -100],
            InterestedEvent::REVIEWED => ['onInterestedReviewed', -100],
            ValidationEvent::NEW => ['onValidationNew', -100],
            UserEvent::USERCLASS_UPDATE => ['onLevelUpdate', -100],
            UserEvent::DISABLED => ['onUserDisabled', -100],
            InvitationEvent::USED => ['onInvitationUsed', -100],
            ManageEvent::INVITE => ['onManageInvite', -100],
            QuestionEvent::NEW => ['onQuestionNew', -100],
            QuestionEvent::ANSWERED => ['onQuestionAnswered', -100],
        ];
    }

    public function onInterestedNew(InterestedEvent $event): void
    {
        $managers = $event->getInterested()->getSharable()->getConfirmedNotDisabledManagers();
        $interestedUser = $event->getInterested()->getUser();
        $name = $event->getInterested()->getSharable()->getName();
        $username = $interestedUser->getUsername();
        $subject = "$username is interested in your sharable: $name";

        foreach ($managers->toArray() as $manage) {
            assert($manage instanceof Manage);
            $this->emailNotification($manage->getUser(), $subject, 'interested_new', [
                'interested' => $event->getInterested(),
            ]);
        }
    }

    public function onInterestedReviewed(InterestedEvent $event): void
    {
        $user = $event->getInterested()->getUser();
        $name = $event->getInterested()->getSharable()->getName();
        $subject = "Your interest on sharable $name has been reviewed";
        $this->emailNotification($user, $subject, 'interested_reviewed', [
            'sharable' => $event->getInterested()->getSharable(),
        ]);
    }

    public function onValidationNew(ValidationEvent $event): void
    {
        $name = $event->getValidation()->getSharable()->getName();
        $managers = $event->getValidation()->getSharable()->getConfirmedNotDisabledManagers();
        $subject = "You have received a validation on your sharable: $name";
        foreach ($managers->toArray() as $manage) {
            assert($manage instanceof Manage);
            $this->emailNotification($manage->getUser(), $subject, 'validation_new', [
                'validation' => $event->getValidation(),
            ]);
        }
    }

    public function onLevelUpdate(UserEvent $event): void
    {
        $user = $event->getUser();
        $userClassName = $user->getUserClass()->getName();
        $subject = "You are now in the user class: $userClassName";
        $this->emailNotification($user, $subject, 'user_userclass_update');
    }

    public function onUserDisabled(UserEvent $event): void
    {
        $user = $event->getUser();
        $disabled = $user->isDisabled() ? 'disabled' : 'not disabled';
        $subject = "Your account is now $disabled";
        $this->emailNotification($user, $subject, 'user_disabled');
    }

    public function onInvitationUsed(InvitationEvent $event): void
    {
        $invitation = $event->getInvitation();
        $parent = $invitation->getParent();
        $childName = $invitation->getChild()->getUsername();
        $subject = "Your invitation have been used by user: $childName";
        $this->emailNotification($parent, $subject, 'invitation_used', [
            'invitation' => $invitation,
        ]);
    }

    public function onManageInvite(ManageEvent $event)
    {
        $manage = $event->getManage();
        $user = $manage->getUser();
        $sharableName = $manage->getSharable()->getName();
        $subject = "You have been invited to manage a sharable: $sharableName";
        $this->emailNotification($user, $subject, 'manage_invite', [
            'manage' => $manage,
        ]);
    }

    public function onQuestionNew(QuestionEvent $event)
    {
        $sharable = $event->getQuestion()->getSharable();
        $name = $sharable->getName();
        $managers = $sharable->getConfirmedNotDisabledManagers();
        $subject = "You have a question on your sharable: $name";
        foreach ($managers->toArray() as $manage) {
            assert($manage instanceof Manage);
            $this->emailNotification($manage->getUser(), $subject, 'question_new', [
                'question' => $event->getQuestion(),
            ]);
        }
    }

    public function onQuestionAnswered(QuestionEvent $event)
    {
        $user = $event->getQuestion()->getUser();
        $name = $event->getQuestion()->getSharable()->getName();
        $subject = "Your question on sharable: $name has been answered!";
        $this->emailNotification($user, $subject, 'question_answered', [
            'question' => $event->getQuestion(),
        ]);
    }

    /**
     * @param array $context like `'var' => value`
     */
    private function emailNotification(User $user, string $subject, string $template, array $context = []): void
    {
        $context['user'] = $user;
        if ($user->isVerified()) {
            $email = (new TemplatedEmail())
            ->from(new Address($this->parameters->get('app.emailAddress'), $this->parameters->get('app.siteName')))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate("email/notification/$template.html.twig")
            ->context($context);
            $this->mailer->send($email);
        }
    }
}
