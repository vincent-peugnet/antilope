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

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameters;

    public function __construct(
        VerifyEmailHelperInterface $helper,
        MailerInterface $mailer,
        EntityManagerInterface $manager,
        ParameterBagInterface $parameters
    ) {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
        $this->parameters = $parameters;
    }

    public function sendEmailConfirmation(
        string $verifyEmailRouteName,
        UserInterface $user
    ): void {
        assert($user instanceof User);
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            $user->getEmail()
        );

        $email = new TemplatedEmail();
        $email->from(new Address($this->parameters->get('app.emailAddress'), $this->parameters->get('app.siteName')))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('email/confirmation_email.html.twig')
        ;

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAt'] = $signatureComponents->getExpiresAt();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        assert($user instanceof User);
        $this->verifyEmailHelper->validateEmailConfirmation(
            $request->getUri(),
            (string) $user->getId(),
            $user->getEmail()
        );

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
