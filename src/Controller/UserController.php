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
use App\Entity\User;
use App\Entity\UserContact;
use App\Entity\UserSearch;
use App\Form\RoleType;
use App\Form\UserContactType;
use App\Form\UserSearchType;
use App\Form\UserType;
use App\Repository\BookmarkRepository;
use App\Repository\UserRepository;
use App\Security\Voter\UserContactVoter;
use App\Security\Voter\UserVoter;
use App\Service\FileUploader;
use App\Service\LevelUp;
use DateTime;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $search = new UserSearch();
        $form = $this->createForm(UserSearchType::class, $search);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();
        }

        $usersPagination = $paginator->paginate(
            $userRepository->filterQuery($search),
            $request->query->getInt('page', 1),
            $this->getParameter('app.result_per_page')
        );
        $usersPagination->setCustomParameters(['align' => 'center']);

        return $this->render('user/index.html.twig', [
            'usersPagination' => $usersPagination,
            'form' => $form->createView(),
            'search' => $search,
        ]);
    }


    /**
     * @Route("/user/{id}", name="user_show", requirements={"id"="\d+"})
     */
    public function show(User $user, LevelUp $levelUp): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

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
    public function edit(User $user, FileUploader $fileUploader, Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);


        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            assert($user instanceof User);
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $avatar = $fileUploader->upload($avatarFile, FileUploader::AVATAR);
                if ($user->getAvatar()) {
                    $fileUploader->remove($user->getAvatar(), FileUploader::AVATAR);
                }
                $user->setAvatar($avatar);
            }

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
    public function contact(User $user, ParameterBagInterface $parameters): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::CONTACT, $user);

        $repository = $this->getDoctrine()->getRepository(UserContact::class);
        $userContacts = $repository->findBy(['user' => $user->getId()]);

        return $this->render('user/contact.html.twig', [
            'user' => $user,
            'userContacts' => $userContacts,
            'contactEditDelay' => (int) $parameters->get('app.contact_edit_delay'),
            'contactForgetDelay' => (int) $parameters->get('app.contact_forget_delay'),
        ]);
    }

    /**
     * @Route("/user/{id}/contact/add", name="user_contact_add", requirements={"id"="\d+"})
     */
    public function contactAdd(User $user, Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

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

    /**
     * @Route("/contact/user/{id}/edit", name="user_contact_edit", requirements={"id"="\d+"})
     */
    public function contactEdit(UserContact $userContact, Request $request)
    {
        $user = $userContact->getUser();
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);
        $this->denyAccessUnlessGranted(UserContactVoter::EDIT, $userContact);

        $form = $this->createForm(UserContactType::class, $userContact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userContact = $form->getData();
            assert($userContact instanceof UserContact);
            $userContact->setLastEditedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userContact);
            $entityManager->flush();

            return $this->redirectToRoute('user_contact', ['id' => $user->getId()]);
        }

        return $this->render('user/contact_edit.html.twig', [
            'user' => $user,
            'userContact' => $userContact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/contact/user/{id}/forget", name="user_contact_forget", requirements={"id"="\d+"})
     */
    public function contactForget(UserContact $userContact, EntityManagerInterface $em)
    {
        $user = $userContact->getUser();
        if ($this->isGranted(UserVoter::EDIT, $user) && $this->isGranted(UserContactVoter::FORGET, $userContact)) {
            $userContact->setForgottenAt(new DateTime());
            $em->persist($userContact);
            $em->flush();
            return $this->redirectToRoute('user_contact', ['id' => $user->getId()]);
        } else {
            $this->createAccessDeniedException();
        }
    }

    /**
     * @Route("/contact/user/{id}/delete", name="user_contact_delete", requirements={"id"="\d+"})
     */
    public function contactDelete(UserContact $userContact, EntityManagerInterface $em)
    {
        $user = $userContact->getUser();
        if ($this->isGranted(UserVoter::EDIT, $user) && $this->isGranted(UserContactVoter::DELETE, $userContact)) {
            $em->remove($userContact);
            $em->flush();
            return $this->redirectToRoute('user_contact', ['id' => $user->getId()]);
        } else {
            $this->createAccessDeniedException();
        }
    }

    /**
     * @Route("/user/{id}/role", name="user_role", requirements={"id"="\d+"})
     */
    public function role(User $user, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::ROLE, $user);
        $form = $this->createForm(RoleType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            assert($user instanceof User);

            $em->persist($user);
            $em->flush();
        }

        return $this->render('user/role.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
