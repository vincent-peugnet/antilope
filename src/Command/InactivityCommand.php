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
use App\Repository\UserRepository;
use App\Service\Inactivity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InactivityCommand extends Command
{
    protected static $defaultName = 'app:inactivity';
    private Inactivity $inactivity;
    private UserRepository $userRepository;

    public function __construct(Inactivity $inactivity, UserRepository $userRepository)
    {
        $this->inactivity = $inactivity;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('disable all users that where inactive too long')
            ->addOption('dry', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('dry')) {
            $io->info('You have selected Dry run option');
        }

        $users = $this->userRepository->findAllActives();
        if ($input->getOption('dry')) {
            $users = array_filter($users, function (User $user) {
                return $this->inactivity->check($user);
            });
            $total = count($users);
            $io->success("$total users need to be disabled");
        } else {
            $total = 0;
            foreach ($users as $user) {
                if ($this->inactivity->checkUpdate($user)) {
                    $total++;
                }
            }
            $io->success("$total users have been disabled");
        }

        return Command::SUCCESS;
    }
}
