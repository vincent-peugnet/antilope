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

use App\Entity\User;
use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class AdminCommand extends Command
{
    protected static $defaultName = 'app:admin';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add or remove admin privilege to an user')
            ->setHelp('This is helpfull to create your first admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);


        $userRepository = $this->entityManager->getRepository(User::class);
        assert($userRepository instanceof UserRepository);

        if ($userRepository->count([]) === 0) {
            $io->warning('There are no user in your database, please register an user first.');
            return Command::FAILURE;
        }

        $helper = $this->getHelper('question');
        $question = new Question(
            'Choose an user to grant or remove admin privileges by indicating the corresponding user ID: '
        );
        $userId = $helper->ask($input, $output, $question);

        if (!is_numeric($userId)) {
            $io->warning('User ID should be a number');
            return Command::FAILURE;
        }

        $user = $userRepository->findOneBy(['id' => $userId]);
        if (!is_null($user)) {
            $username = $user->getUsername();
            $user->setAdmin(!$user->isAdmin());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $isadmin = $user->isAdmin() ? 'obtained' : 'lost';

            $io->success("user $username of ID $userId have $isadmin admin privilege");
            return Command::SUCCESS;
        } else {
            $io->error("user with ID $userId not founded");
            return Command::SUCCESS;
        }
    }
}
