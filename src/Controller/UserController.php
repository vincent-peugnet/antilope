<?php

namespace App\Controller;

use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserContact;
use App\Form\UserContactType;
use App\Form\UserType;
use App\Security\Voter\UserVoter;
use App\Service\LevelUp;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/user/{id}", name="user_show", requirements={"id"="\d+"})
     */
    public function show(User $user, LevelUp $levelUp): Response
    {
        if ($user === $this->getUser()) {
            $user = $levelUp->checkUpdate($user);
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'sharable' => new Sharable(),
        ]);
    }

    /**
     * @Route("/user/{id}/edit", name="user_edit", requirements={"id"="\d+"})
     */
    public function edit(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $user);


        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_show', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/{id}/contact", name="user_contact", requirements={"id"="\d+"})
     */
    public function contact(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::CONTACT, $user);

        $repository = $this->getDoctrine()->getRepository(UserContact::class);
        $userContacts = $repository->findBy(['user' => $user->getId()]);

        return $this->render('user/contact.html.twig', [
            'user' => $user,
            'userContacts' => $userContacts,
        ]);
    }

    /**
     * @Route("/user/{id}/contact/add", name="user_contact_add", requirements={"id"="\d+"})
     */
    public function contactAdd(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $user);

        $form = $this->createForm(UserContactType::class, new UserContact());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userContact = $form->getData();

            $userContact->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userContact);
            $entityManager->flush();

            return $this->redirectToRoute('user_contact', ['id' => $user->getId()]);
        }



        return $this->render('user/contact_add.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
