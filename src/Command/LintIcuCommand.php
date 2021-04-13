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

use App\Service\FileCatalogue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\Reader\TranslationReaderInterface;

class LintIcuCommand extends Command
{
    protected static $defaultName = 'lint:icu';
    protected const PATH = 'translations';
    protected const LINE_OFFSET = 4;

    protected TranslationReaderInterface $reader;

    public function __construct(TranslationReaderInterface $reader)
    {
        parent::__construct();
        $this->reader = $reader;
    }

    protected function configure(): void
    {
        $this->setDescription('Check translation files for ICU messages syntax error');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $success = 0;
        $failures = 0;

        foreach (TranslationExtractCommand::LOCALES as $locale) {
            $cat = new FileCatalogue($locale);
            $this->reader->read(self::PATH, $cat);
            foreach ($cat->getByFile() as $file => $messages) {
                $line = self::LINE_OFFSET;
                foreach ($messages as $key => $message) {
                    try {
                        $formatter = new \MessageFormatter($locale, $message);
                        $success++;
                    } catch (\IntlException $e) {
                        $failures++;
                        $msg = $e->getMessage();
                        $io->writeln("<error> ERROR </error> in $file($line): $msg");
                    }
                    $line += substr_count($message, "\n") + 1;
                }
            }
        }
        $total = $success + $failures;
        if ($failures === 0) {
            $io->success("All $success ICU strings contain valid syntax.");
            return Command::SUCCESS;
        } else {
            $io->error("$failures of $total ICU strings contain invalid syntax.");
            return Command::FAILURE;
        }
    }
}
