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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TranslationCheckCommand extends Command
{
    protected static $defaultName = 'translation:check';

    protected function configure()
    {
        $this
            ->setDescription('Checks translation files')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Check all locales')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $debugCmd = $this->getApplication()->find('debug:translation');
        $args = [
            '--domain' => TranslationExtractCommand::DOMAIN,
            '--only-missing' => true,
        ];
        $failures = [];
        $end = $input->getOption('all') ? sizeof(TranslationExtractCommand::LOCALES) : 1;
        for ($i = 0; $i < $end; $i++) {
            $args['locale'] = TranslationExtractCommand::LOCALES[$i];
            $out = new BufferedOutput();
            $returnCodes[] = $debugCmd->run(new ArrayInput($args), $out);
            $content = $out->fetch();
            if (str_contains($content, 'missing')) {
                $failures[] = TranslationExtractCommand::LOCALES[$i];
            }
            $io->write($content);
        }
        if (sizeof($failures) === 0) {
            $io->success('The translations are up to date');
            return Command::SUCCESS;
        } else {
            $vars = implode(', ', $failures);
            $io->error("Some translations are not up to date: $vars. Run 'translation:extract' and correct the values");
            return Command::FAILURE;
        }
    }
}
