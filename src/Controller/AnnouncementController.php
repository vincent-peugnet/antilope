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

use App\Entity\Announcement;
use App\Form\AnnouncementType;
use App\Repository\AnnouncementRepository;
use App\Security\Voter\AnnouncementVoter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/announcement")
 */
class AnnouncementController extends AbstractController
{
    /**
     * @Route("/", name="announcement")
     */
    public function index(AnnouncementRepository $announcementRepository): Response
    {
        $this->denyAccessUnlessGranted(AnnouncementVoter::CREATE, new Announcement());
        $announcements = $announcementRepository->findNotPublished();

        return $this->render('announcement/index.html.twig', [
            'announcements' => $announcements,
            'announcement' => new Announcement(),
        ]);
    }

    /**
     * @Route("/new", name="announcement_new")
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $announcement = new Announcement();
        $this->denyAccessUnlessGranted(AnnouncementVoter::CREATE, $announcement);

        $form = $this->createForm(AnnouncementType::class, $announcement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $announcement = $form->getData();
            assert($announcement instanceof Announcement);
            $em->persist($announcement);
            $em->flush();

            if ($announcement->getPublishedAt() > new DateTime()) {
                return $this->redirectToRoute('announcement');
            } else {
                return $this->redirectToRoute('app_homepage');
            }
        }


        return $this->render('announcement/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("{id}/edit", name="announcement_edit", requirements={"id"="\d+"})
     */
    public function edit(Announcement $announcement, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AnnouncementVoter::EDIT, $announcement);
        $form = $this->createForm(AnnouncementType::class, $announcement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $announcement = $form->getData();
            assert($announcement instanceof Announcement);
            $em->persist($announcement);
            $em->flush();

            if ($announcement->getPublishedAt() > new DateTime()) {
                return $this->redirectToRoute('announcement');
            } else {
                return $this->redirectToRoute('app_homepage');
            }
        }


        return $this->render('announcement/edit.html.twig', [
            'form' => $form->createView(),
            'announcement' => $announcement,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="announcement_delete", requirements={"id"="\d+"})
     */
    public function delete(Announcement $announcement, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AnnouncementVoter::DELETE, $announcement);
        $em->remove($announcement);
        $em->flush();

        return $this->redirectToRoute('announcement');
    }
}
