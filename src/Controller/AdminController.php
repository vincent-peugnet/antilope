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

namespace App\Controller;

use JakubOnderka\PhpParallelLint\RunTimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(ParameterBagInterface $parameters): Response
    {
        $appParameters = array_filter($parameters->all(), function (string $key) {
            return (substr($key, 0, 4) === "app.");
        }, ARRAY_FILTER_USE_KEY);

        return $this->render('admin/index.html.twig', [
            'appParameters' => $appParameters,
        ]);
    }
    /**
     * @Route("/admin/opcache_reset", name="opcache_reset")
     */
    public function opcacheReset(): Response
    {
        if (opcache_reset()) {
            return new Response('success');
        } else {
            throw new RunTimeException('fail to reset OPcache');
        }
    }
}
