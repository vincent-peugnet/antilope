<?php

namespace App\Controller;

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
        ValidationRepository $validationRepository
    ): Response {
        return $this->render('home/index.html.twig', [
            'userCount' => $userRepository->count([]),
            'sharableCount' => $sharableRepository->count([]),
            'validationCount' => $validationRepository->count([]),
            'userLimit' => $this->getParameter('app.userLimit'),
            'openRegistration' => $this->getParameter('app.openRegistration'),
            'showHomeStats' => $this->getParameter('app.showHomeStats'),
        ]);
    }
}
