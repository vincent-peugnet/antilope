<?php

namespace App\Controller;

use App\Entity\UserClass;
use App\Repository\UserClassRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserClassController extends AbstractController
{
    /**
     * @Route("/userclass", name="userclass")
     */
    public function index(UserClassRepository $userClassRepository)
    {
        $userClasses = $userClassRepository->findAll();

        return $this->render('user_class/index.html.twig', [
            'userClasses' => $userClasses,
        ]);
    }
}
