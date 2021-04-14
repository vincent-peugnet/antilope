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

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;

class MailCommand extends Command
{
    protected static $defaultName = 'app:mail';
    private MailerInterface $mailer;
    private string $emailAddress;
    private string $siteName;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameters)
    {
        $this->mailer = $mailer;
        $this->emailAddress = $parameters->get('app.email_address');
        $this->siteName = $parameters->get('app.site_name');

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Send a test mail to the given email address to test email setup')
            ->addArgument('emailAddress', InputArgument::REQUIRED, 'Email Recipient')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailAddress = $input->getArgument('emailAddress');

        $validator = Validation::createValidator();

        if (count($validator->validate($emailAddress, new Email())) !== 0) {
            $io->warning("$emailAddress is not a valid email adress");
            return Command::FAILURE;
        }


        $email = (new MimeEmail())
            ->from(new Address($this->emailAddress, $this->siteName))
            ->to($emailAddress)
            ->subject('Test Email from Antilope')
            ->text('Looks like email is well configured !')
            ->html('<p>Looks like email is well configured !</p>');

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success("An Email should have been send to $emailAddress, check tour mailbox !");

        return Command::SUCCESS;
    }
}
