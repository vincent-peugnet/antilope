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

namespace App\Service;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

class FileCatalogue extends MessageCatalogue
{
    protected array $messages = [];

    /**
     * {@inheritdoc}
     */
    public function addCatalogue(MessageCatalogueInterface $catalogue): void
    {
        parent::addCatalogue($catalogue);
        foreach ($catalogue->getResources() as $resource) {
            $this->messages[$resource->__toString()] = $catalogue->all()[$catalogue->getDomains()[0]];
        }
    }

    public function getByFile(): array
    {
        return $this->messages;
    }
}
