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

use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserClassCommand extends Command
{
    protected static $defaultName = 'app:userclass';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Setup a default user class')
            ->setHelp('You need at least one user class for Antilope to work.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userClassRepository = $this->entityManager->getRepository(UserClass::class);
        assert($userClassRepository instanceof UserClassRepository);
        $count = $userClassRepository->count([]);
        $io->writeln("$count user class(es) were found.");
        if ($count > 0) {
            $io->success('At least one userclass is set, minimum is reached for the app to work.');
            return Command::SUCCESS;
        }



        $helper = $this->getHelper('question');
        $question = new Question(
            'Please enter the name of the default user class (this can be changed later): ',
            'basic_user'
        );

        $userClassName = $helper->ask($input, $output, $question);

        if (empty($userClassName) || !is_string($userClassName) || strlen($userClassName) > 256) {
            $io->warning('User Class name must be a string of less than 256 caracters');
            return Command::FAILURE;
        }

        $userClass = new UserClass();
        $userClass
            ->setName($userClassName)
            ->setRank(55)
            ->setMaxParanoia(0)
        ;
        $this->entityManager->persist($userClass);
        $this->entityManager->flush();

        $io->success('a default user class have been created');

        return Command::SUCCESS;
    }
}
