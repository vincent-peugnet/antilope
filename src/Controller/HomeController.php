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
    public function index(SharableRepository $sharableRepository, UserRepository $userRepository, ValidationRepository $validationRepository): Response
    {
        $userCount = $userRepository->count([]);
        $sharableCount = $userRepository->count([]);
        $validationCount = $validationRepository->count([]);
        $userLimit = $this->getParameter('app.userLimit');

        return $this->render('home/index.html.twig', [
            'userCount' => $userCount,
            'sharableCount' => $sharableCount,
            'validationCount' => $validationCount,
            'userLimit' => $userLimit,
        ]);
    }
}
