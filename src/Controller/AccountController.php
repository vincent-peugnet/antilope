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

namespace App\Controller;

use App\Entity\User;
use App\Form\InvitationCreateType;
use App\Repository\InvitationRepository;
use App\Security\EmailVerifier;
use App\Service\InvitationService;
use App\Service\LevelUp;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class AccountController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/account/invitation", name="account_invitation")
     */
    public function invitation(
        InvitationRepository $invitationRepository,
        InvitationService $invitationService
    ): Response {
        $invitationDuration = $invitationService->getInvitationDuration();

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
        InvitationService $invitationService,
        Request $request
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->isDisabled() || !$user->getUserClass()->getCanInvite()) {
            throw $this->createAccessDeniedException();
        }

        $userLimitReached = $invitationService->userLimitReached();
        $needToWait = $invitationService->needToWait($user);
        $openRegistration = $invitationService->getopenRegistration();
        $disabled = (
            $openRegistration ||
            $userLimitReached ||
            $needToWait
        );

        $form = $this->createForm(InvitationCreateType::class, null, ['disabled' => $disabled]);


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

        return $this->render('account/invitation_new.html.twig', [
            'form' => $form->createView(),
            'invitationDuration' => $invitationService->getInvitationDuration(),
            'userLimitReached' => $userLimitReached,
            'needToWait' => $needToWait,
            'openRegistration' => $openRegistration,
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
     * @Route("account/settings/email/edit", name="email_edit")
     */
    public function editUserEmail(Request $request, LevelUp $levelUp): Response
    {
        $oldEmail = $this->getUser()->getEmail();
        $form = $this->createFormBuilder($this->getUser())
            ->add('email', EmailType::class)
            ->add('update', SubmitType::class, [
                'label' => new TranslatableMessage('Update'),
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $editedUser = $form->getData();
            assert($editedUser instanceof User);
            if ($editedUser->getEmail() !== $oldEmail) {
                $editedUser->setIsVerified(false);
                $entityManager = $this->getDoctrine()->getManager();
                $levelUp->check($editedUser);
                $entityManager->persist($editedUser);
                $entityManager->flush();
                return $this->redirectToRoute('email_send');
            }
            return $this->redirectToRoute('account_settings');
        }

        return $this->render('account/email_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("account/settings/email/send", name="email_send")
     */
    public function sendUserEmail(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'email_verify',
            $user
        );
        $email = $user->getEmail();
        $this->addFlash('primary', "An email have been send to $email for validation");
        return $this->redirectToRoute('account_settings');
    }



    /**
     * @Route("account/settings/email/verify", name="email_verify")
     */
    public function verifyUserEmail(Request $request, LevelUp $levelUp): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('warning', $exception->getReason());

            return $this->redirectToRoute('account_settings');
        }

        $levelUp->checkUpdate($this->getUser());
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_homepage');
    }
}
