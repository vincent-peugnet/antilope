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

use App\Entity\Sharable;
use App\Repository\InterestedRepository;
use App\Repository\QuestionRepository;
use App\Repository\SharableRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function index(
        SharableRepository $sharableRepository,
        UserRepository $userRepository,
        ValidationRepository $validationRepository,
        QuestionRepository $questionRepository
    ): Response {
        return $this->render('home/index.html.twig', [
            'userCount' => $userRepository->count([]),
            'sharableCount' => $sharableRepository->count([]),
            'validationCount' => $validationRepository->count([]),
            'questionCount' => $questionRepository->count([]),
            'userLimit' => $this->getParameter('app.user_limit'),
            'openRegistration' => $this->getParameter('app.open_registration'),
            'showHomeStats' => $this->getParameter('app.show_home_stats'),
            'activeUsers' => $userRepository->findRecentlyActive(60),
            'lastValidations' => $validationRepository->findBy([], ['sendAt' => 'DESC'], 5),
            'sharable' => new Sharable(),
        ]);
    }
}
