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
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Translation\MessageCatalogueInterface;

class TranslationExtractCommand extends Command
{
    protected static $defaultName = 'translation:extract';
    public const FORMAT = 'php';
    public const DOMAIN = 'messages';
    public const LOCALES = [
        'en',
        'fr',
    ];

    protected function configure(): void
    {
        $this
            ->setDescription('Extracts translation files')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Extract all locales');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $updateCmd = $this->getApplication()->find('translation:update');
        $failures = [];
        $args = [
            '--output-format' => self::FORMAT,
            '--domain' => self::DOMAIN,
            '--prefix' => '',
            '--force' => true,
        ];
        $end = $input->getOption('all') ? sizeof(self::LOCALES) : 1;
        for ($i = 0; $i < $end; $i++) {
            $locale = self::LOCALES[$i];
            $args['locale'] = $locale;
            $code = $updateCmd->run(new ArrayInput($args), new NullOutput());
            if ($code == Command::FAILURE) {
                array_push($failures, $locale);
            } else {
                $file = sprintf(
                    'translations/%s%s.%s.%s',
                    self::DOMAIN,
                    MessageCatalogueInterface::INTL_DOMAIN_SUFFIX,
                    $locale,
                    self::FORMAT
                );
                $content = include $file;
                ksort($content, SORT_NATURAL | SORT_FLAG_CASE);
                file_put_contents($file, "<?php\n\nreturn " . var_export($content, true) . ";\n");
                exec("vendor/bin/phpcbf $file");
            }
        }
        if (sizeof($failures) === 0) {
            // phpcs:ignore Generic.Files.LineLength
            $io->success('The translations have been correcty extracted. Edit the new values and remove the untranslated ones.');
            return Command::SUCCESS;
        } else {
            $locales = implode(', ', $failures);
            $io->error("Some translations have been incorrectly extracted: $locales.");
            return Command::FAILURE;
        }
    }
}
