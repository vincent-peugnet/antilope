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
 * PHP version 7.2
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Controller;

use App\Entity\Manage;
use App\Entity\Sharable;
use App\Form\ManageType;
use App\Repository\InterestedRepository;
use App\Security\Voter\ManageVoter;
use App\Security\Voter\SharableVoter;
use App\Security\Voter\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManageController extends AbstractController
{

        /**
     * @Route("/sharable/{id}/managers", name="sharable_managers", requirements={"id"="\d+"})
     */
    public function managers(Sharable $sharable, Request $request, InterestedRepository $interestedRepo): Response
    {
        $this->denyAccessUnlessGranted('edit', $sharable);
        $user = $this->getUser();
        $filteredManages = $sharable->getManagedBy()->filter(function (Manage $manage) use ($user) {
            return $manage->getUser() === $user;
        });
        $userManage = $filteredManages->first();

        $manage = new Manage();
        $manage->setSharable($sharable);
        $form = $this->createForm(ManageType::class, $manage);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manage = $form->getData();
            assert($manage instanceof Manage);
            $manage->setContactable(false);
            $manage->setConfirmed(false);

            $intrested = $interestedRepo->findOneBy(
                ['user' => $manage->getUser()->getId(),
                'sharable' => $sharable->getId()]
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manage);
            if ($intrested) {
                $entityManager->remove($intrested);
            }
            $entityManager->flush();

            return $this->redirectToRoute('sharable_managers', ['id' => $sharable->getId()]);
        }

        return $this->render('manage/index.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
            'userManage' => $userManage,
        ]);
    }

    /**
     * @Route("/manage/{id}/uncontactable", name="manage_uncontactable", requirements={"id"="\d+"})
     */
    public function manageUnContactable(Manage $manage): Response
    {
        $this->denyAccessUnlessGranted(ManageVoter::HIDE_CONTACT, $manage);

        $manage->setContactable(false);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($manage);
        $entityManager->flush();

        return $this->redirectToRoute('sharable_contact', ['id' => $manage->getSharable()->getId()]);
    }


    /**
     * @Route("/manage/{id}/contactable", name="manage_contactable", requirements={"id"="\d+"})
     */
    public function manageContactable(Manage $manage): Response
    {
        $this->denyAccessUnlessGranted(ManageVoter::SHOW_CONTACT, $manage);

        $manage->setContactable(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($manage);
        $entityManager->flush();

        return $this->redirectToRoute('sharable_contact', ['id' => $manage->getSharable()->getId()]);
    }

    /**
     * @Route("/manage/{id}/remove", name="manage_remove", requirements={"id"="\d+"})
     */
    public function removeManage(Manage $manage): Response
    {
        $this->denyAccessUnlessGranted(ManageVoter::REMOVE, $manage);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($manage);
        $entityManager->flush();
        if ($this->isGranted(SharableVoter::VIEW, $manage->getSharable())) {
            return $this->redirectToRoute('sharable_show', ['id' => $manage->getSharable()->getId()]);
        } else {
            return $this->redirectToRoute('sharable');
        }
    }

    /**
     * @Route("/manage/{id}/confirm", name="manage_confirm", requirements={"id"="\d+"})
     */
    public function confirmManage(Manage $manage): Response
    {
        $this->denyAccessUnlessGranted(ManageVoter::CONFIRM, $manage);
        $manage->setConfirmed(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($manage);
        $entityManager->flush();
        return $this->redirectToRoute('sharable_show', ['id' => $manage->getSharable()->getId()]);
    }
}
