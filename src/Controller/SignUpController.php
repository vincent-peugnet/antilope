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
use App\Event\InvitationEvent;
use App\Form\RegistrationFormType;
use App\Form\SignUpType;
use App\Repository\InvitationRepository;
use App\Repository\UserClassRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as EventDispatcherEventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SignUpController extends AbstractController
{
    private bool $userLimitReached = false;
    private bool $needCode = false;


    /**
     * @Route("/signup", name="sign_up")
     */
    public function register(
        Request $request,
        EmailVerifier $emailVerifier,
        UserPasswordHasherInterface $userPasswordHasherInterface,
        UserRepository $userRepository,
        UserClassRepository $userClassRepository,
        InvitationRepository $invitationRepository,
        EventDispatcherEventDispatcherInterface $dispatcher
    ): Response {

        // Check user limit
        $userLimit = (int) $this->getParameter('app.user_limit');
        if (!empty($userLimit)) {
            $userCount = $userRepository->count([]);
            if ($userCount >= $userLimit) {
                $this->userLimitReached = true;
            }
        }
        // Check if app is registrations are open
        $this->needCode = !$this->getParameter('app.open_registration');


        $user = new User();
        $form = $this->createForm(
            SignUpType::class,
            $user,
            ['needCode' => $this->needCode, 'userLimitReached' => $this->userLimitReached]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // stop user creation if no user class exist
            $userClass = $userClassRepository->findFirst();
            if (is_null($userClass)) {
                throw $this->createNotFoundException('No User Class Defined');
            }
            $user->setUserClass($userClass);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            if ($this->needCode) {
                $code = $form->get('code')->getData();
                $invitation = $invitationRepository->findOneBy(['code' => $code]);
                $invitation->setChild($user);
                $entityManager->persist($invitation);
                $entityManager->flush();
                $dispatcher->dispatch(new InvitationEvent($invitation), InvitationEvent::USED);
            } else {
                $entityManager->flush();
            }


            // Send validation email
            $emailVerifier->sendEmailConfirmation(
                'email_verify',
                $user
            );
            $email = $user->getEmail();
            $this->addFlash('primary', "An email have been send to $email for validation");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('sign_up/index.html.twig', [
            'form' => $form->createView(),
            'userLimitReached' => $this->userLimitReached,
        ]);
    }
}
