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


    /**
     * @Route("/userclass/{id}", name="userclass_show")
     */
    public function show(UserClass $userClass, UserRepository $userRepository): Response
    {
        $users = $userRepository->findByUserClass($userClass);
        return $this->render('user_class/show.html.twig', [
            'userClass' => $userClass,
            'users' => $users,
        ]);
    }

}
