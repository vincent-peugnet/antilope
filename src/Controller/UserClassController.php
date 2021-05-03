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
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
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
        $userClasses = $userClassRepository->findAll();

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
    public function delete(UserClass $userClass, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(UserClassVoter::DELETE, $userClass);

        $form = $this->createForm(UserClassDeleteType::class, $userClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$userClass->getSharables()->isEmpty()) {
                $sharableTarget = $form->get('target')->getData();
                assert($sharableTarget instanceof UserClass);
                foreach ($userClass->getSharables() as $sharable) {
                    $sharable->setVisibleBy($sharableTarget);
                    $em->persist($sharable);
                }
                $em->flush();
            }
            if (!$userClass->getUsers()->isEmpty()) {
                $userTarget = is_null($userClass->getPrev()) ? $userClass->getNext() : $userClass->getPrev();
                foreach ($userClass->getUsers() as $user) {
                    $user->setUserClass($userTarget);
                    $em->persist($user);
                }
                $em->flush();
            }

            $prev = $userClass->getPrev();
            $next = $userClass->getNext();
            $em->getConnection()->beginTransaction();
            try {
                $userClass->setNext(null);
                if (!is_null($prev)) {
                    $prev->setNext(null);
                    $em->persist($prev);
                    $em->flush();
                    $prev->setNext($next);
                }
                $em->remove($userClass);
                $em->flush();
                $em->getConnection()->commit();
            } catch (ConnectionException $e) {
                $em->getConnection()->rollBack();
                throw $e;
            }

            return $this->redirectToRoute('userclass_show', ['id' => $userClass->getId()]);
        }

        return $this->render('user_class/delete.html.twig', [
            'userClass' => $userClass,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/userclass/{id}/edit", name="userclass_edit", requirements={"id"="\d+"})
     */
    public function edit(UserClass $userClass, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(UserClassVoter::EDIT, $userClass);

        $form = $this->createForm(UserClassType::class, $userClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userClass = $form->getData();
            assert($userClass instanceof UserClass);
            if (!$form->get('visibleBy')->getData() && !$userClass->getSharables()->isEmpty()) {
                foreach ($userClass->getSharables() as $sharable) {
                    $sharable->setVisibleBy(null);
                    $em->persist($sharable);
                }
                $em->flush();
                $count = $userClass->getSharables()->count();
                $this->addFlash('warning', "$count sharable(s) had their parameter visibleBy settings being removed");
            }

            $userClass->setLastEditedAt(new DateTime());
            $em->persist($userClass);
            $em->flush();

            return $this->redirectToRoute('userclass_show', ['id' => $userClass->getId()]);
        }

        return $this->render('user_class/edit.html.twig', [
            'userClass' => $userClass,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/userclass/new", name="userclass_new")
     */
    public function new(
        Request $request,
        UserClassRepository $userClassRepository,
        EntityManagerInterface $em
    ): Response {
        $userClass = new UserClass();
        $this->denyAccessUnlessGranted(UserClassVoter::CREATE, $userClass);

        $form = $this->createForm(UserClassType::class, $userClass);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userClass = $form->getData();
            assert($userClass instanceof UserClass);
            $replaced = $userClassRepository->findOneBy(['next' => $userClass->getNext()]);
            $em->getConnection()->beginTransaction();
            try {
                if (!is_null($replaced)) {
                    $replaced->setNext(null);
                    $em->persist($replaced);
                    $em->flush();
                    $replaced->setNext($userClass);
                }
                $em->persist($userClass);
                $em->flush();
                $em->getConnection()->commit();
            } catch (ConnectionException $e) {
                $em->getConnection()->rollBack();
                throw $e;
            }

            return $this->redirectToRoute('userclass_show', ['id' => $userClass->getId()]);
        }

        return $this->render('user_class/new.html.twig', [
            'userClass' => $userClass,
            'form' => $form->createView(),
        ]);
    }
}
