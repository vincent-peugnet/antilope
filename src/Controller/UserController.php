<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();


        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }


    /**
     * @Route("/user/{id}", name="user_show")
     */
    public function show(User $user): Response
    {

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

}
