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

use App\Entity\UserClass;
use App\Form\UserClassDeleteType;
use App\Form\UserClassType;
use App\Repository\UserClassRepository;
use App\Security\Voter\UserClassVoter;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserClassController extends AbstractController
{
    /**
     * @Route("/userclass", name="userclass")
     */
    public function index(UserClassRepository $userClassRepository): Response
    {
        $userClasses = $userClassRepository->findBy([], ['rank' => 'ASC']);

        return $this->render('user_class/index.html.twig', [
            'userClass' => new UserClass(),
            'userClasses' => $userClasses,
        ]);
    }

    /**
     * @Route("/userclass/{id}", name="userclass_show", requirements={"id"="\d+"})
     */
    public function show(UserClass $userClass): Response
    {
        return $this->render('user_class/show.html.twig', [
            'userClass' => $userClass,
        ]);
    }

    /**
     * @Route("/userclass/{id}/delete", name="userclass_delete", requirements={"id"="\d+"})
     */
    public function delete(UserClass $userClass, Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserClassVoter::DELETE, $userClass);

        $form = $this->createForm(UserClassDeleteType::class, $userClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userClass = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($userClass);
            $entityManager->flush();

            $this->redirectToRoute('userclass_show', ['id' => $userClass->getId()]);
        }

        return $this->render('user_class/delete.html.twig', [
            'userClass' => $userClass,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/userclass/{id}/edit", name="userclass_edit", requirements={"id"="\d+"})
     */
    public function edit(UserClass $userClass, Request $request): Response
    {
        $this->denyAccessUnlessGranted(UserClassVoter::EDIT, $userClass);

        $form = $this->createForm(UserClassType::class, $userClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userClass = $form->getData();
            $userClass->setLastEditedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userClass);
            $entityManager->flush();

            $this->redirectToRoute('userclass_show', ['id' => $userClass->getId()]);
        }

        return $this->render('user_class/edit.html.twig', [
            'userClass' => $userClass,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/userclass/new", name="userclass_new")
     */
    public function new(Request $request): Response
    {
        $userClass = new UserClass();
        $this->denyAccessUnlessGranted(UserClassVoter::CREATE, $userClass);

        $form = $this->createForm(UserClassType::class, $userClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userClass = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userClass);
            $entityManager->flush();

            $this->redirectToRoute('userclass_show', ['id' => $userClass->getId()]);
        }

        return $this->render('user_class/new.html.twig', [
            'userClass' => $userClass,
            'form' => $form->createView(),
        ]);
    }
}
