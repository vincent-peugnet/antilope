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
 * @author Nicolas Peugnet <n.peugnet@free.fr>
 * @copyright 2021 Nicolas Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckEnvCommand extends Command
{
    protected static $defaultName = 'app:check:env';
    protected const VARS = [
        'APP_SECRET',
        'DATABASE_URL',
        'MAILER_DSN',
    ];

    protected function configure()
    {
        $this
            ->setDescription('Check if the environment is correctly configured')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $undefineds = [];
        foreach (self::VARS as $var) {
            if (empty($_ENV[$var])) {
                array_push($undefineds, $var);
            }
        }
        if (sizeof($undefineds) === 0) {
            $io->success('The environment is correctly configured');
            return Command::SUCCESS;
        } else {
            $vars = implode(', ', $undefineds);
            $io->error("Some variables are undefined: $vars");
            return Command::FAILURE;
        }
    }
}
