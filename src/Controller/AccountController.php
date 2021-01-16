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

namespace App\Controller;

use App\Entity\Invitation;
use App\Entity\User;
use App\Form\InvitationCreateType;
use App\Repository\InvitationRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateInterval;
use DateTime;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class AccountController extends AbstractController
{
    private $userLimitReached = false;
    private $needToWait = false;
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/account/invitation", name="account_invitation")
     */
    public function invitation(InvitationRepository $invitationRepository): Response
    {
        $invitationDuration = new DateInterval(
            'PT' . $this->getParameter('app.invitationDuration') . 'H'
        );

        $usedInvitations = $invitationRepository->findUsedInvitations($this->getUser());

        $activeInvitations = $invitationRepository->findActiveInvitations(
            $this->getUser(),
            $invitationDuration
        );


        return $this->render('account/invitation.html.twig', [
            'usedInvitations' => $usedInvitations,
            'activeInvitations' => $activeInvitations,
            'invitationDuration' => $invitationDuration,
        ]);
    }


    /**
     * @Route("/account/invitation/new", name="account_invitation_new")
     */
    public function newInvitation(
        InvitationRepository $invitationRepository,
        Request $request,
        UserRepository $userRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $canInvite = $user->getUserClass()->getCanInvite();
        if (!$canInvite) {
            throw $this->createAccessDeniedException();
        }


        // Check user limit
        $userLimit = $this->getParameter('app.userLimit');
        if (!empty($userLimit)) {
            $userCount = $userRepository->count([]);
            if ($userCount >= $userLimit) {
                $this->userLimitReached = true;
            }
        }

        // Check invite frequency
        if ($user->getUserClass()->getInviteFrequency() !== 0) {
            $inviteFrequency = new DateInterval('P' . $user->getUserClass()->getInviteFrequency() . 'D');
            $lastInvitation = $invitationRepository->findOneBy(['parent' => $user->getId()], ['createdAt' => 'DESC']);
            if (!empty($lastInvitation)) {
                $minInviteDate = $lastInvitation->getCreatedAt()->add($inviteFrequency);
                if ($minInviteDate >= new DateTime()) {
                    $this->needToWait = true;
                }
            }
        }

        $invitation = new Invitation();
        $form = $this->createForm(InvitationCreateType::class, null, [
            'userLimitReached' => $this->userLimitReached,
            'needToWait' => $this->needToWait,
        ]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invitation = $form->getData();

            $invitation->setParent($user);

            $uuid = Uuid::v4();
            $code = $uuid->toBase58();
            $codeExist = $invitationRepository->findOneBy(['code' => $code]);
            if (!$codeExist) {
                $invitation->setCode($code);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invitation);
            $entityManager->flush();

            return $this->redirectToRoute('account_invitation');
        }

        $invitationDuration = new DateInterval(
            'PT' . $this->getParameter('app.invitationDuration') . 'H'
        );


        return $this->render('account/invitation_new.html.twig', [
            'form' => $form->createView(),
            'invitationDuration' => $invitationDuration,
            'userLimitReached' => $this->userLimitReached,
            'needToWait' => $this->needToWait,
        ]);
    }

    /**
     * @Route("/account/settings", name="account_settings")
     */
    public function settings(): Response
    {
        return $this->render('account/settings.html.twig', []);
    }

    /**
     * @Route("account/settings/email/send", name="app_email_send")
     */
    public function sendUserEmail(): Response
    {
        $user = $this->getUser();
        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'app_email_verify',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@antilope.net', $this->getParameter('app.siteName')))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('email/confirmation_email.html.twig')
        );
        $email = $user->getEmail();
        $this->addFlash('primary', "An email have been send to $email");
        return $this->redirectToRoute('account_settings');
    }



    /**
     * @Route("account/settings/email/verify", name="app_email_verify")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('warning', $exception->getReason());

            return $this->redirectToRoute('account_settings');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('account_settings');
    }
}
