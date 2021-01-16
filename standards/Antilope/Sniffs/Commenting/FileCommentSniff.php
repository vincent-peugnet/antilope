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
 * PHP version 7.2
 *
 * @package Antilope
 * @author Nicolas Peugnet <n.peugnet@free.fr>
 * @copyright 2020-2021 Nicolas Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace Antilope\Sniffs\Commenting;

use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff as PEARFileCommentSniff;

/**
 * Parses and verifies the doc comments for files.
 *
 * The primary goal of this sniff is to enforce the license and copyright
 * infos to be at the top of each source file.
 * It is an extention of PEAR's FileComment sniff with less strict rules.
 * Only the @package, @author, @copyright and @license tags are required.
 */
class FileCommentSniff extends PEARFileCommentSniff
{
    /**
     * Tags in correct order and related info.
     *
     * @var array
     */
    protected $tags = [
        '@category'   => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        '@package'    => [
            'required'       => true,
            'allow_multiple' => false,
        ],
        '@subpackage' => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        '@author'     => [
            'required'       => true,
            'allow_multiple' => true,
        ],
        '@copyright'  => [
            'required'       => true,
            'allow_multiple' => true,
        ],
        '@license'    => [
            'required'       => true,
            'allow_multiple' => false,
        ],
        '@version'    => [
            'required'       => false,
            'allow_multiple' => false,
        ],
        '@link'       => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        '@see'        => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        '@since'      => [
            'required'       => false,
            'allow_multiple' => true,
        ],
        '@deprecated' => [
            'required'       => false,
            'allow_multiple' => false,
        ],
    ];
}
