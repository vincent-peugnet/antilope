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

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use UnexpectedValueException;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public const AVATAR = '/uploads/avatar';
    public const TYPES = [self::AVATAR];

    public function __construct(SluggerInterface $slugger, ParameterBagInterface $parameters)
    {
        $this->targetDirectory = $parameters->get('kernel.project_dir') . '/public';
        $this->slugger = $slugger;
    }

    /**
     * @param string $type should be listed in fileUploader constants
     * @return string new name of uploaded file
     */
    public function upload(UploadedFile $file, string $type, string $customName = ''): string
    {
        if (!in_array($type, self::TYPES)) {
            throw new UnexpectedValueException('type should be a value in ' . implode(' | ', self::TYPES));
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($this->targetDirectory . $type, $fileName);

        return $fileName;
    }

    /**
     * Used to remove an old uploaded file
     */
    public function remove(string $fileName, string $type): void
    {
        if (!in_array($type, self::TYPES)) {
            throw new UnexpectedValueException('type should be a value in ' . implode(' | ', self::TYPES));
        }
        $fileSystem = new Filesystem();
        $filePath = $this->targetDirectory . $type . '/' . $fileName;
        if ($fileSystem->exists($filePath)) {
            $fileSystem->remove($filePath);
        }
    }
}
