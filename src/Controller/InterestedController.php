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

use App\Entity\Interested;
use App\Entity\Sharable;
use App\Event\InterestedEvent;
use App\Form\InterestedType;
use App\Repository\InterestedRepository;
use App\Security\Voter\InterestedVoter;
use App\Security\Voter\SharableVoter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InterestedController extends AbstractController
{


    /**
     * @Route("/sharable/{id}/interested", name="sharable_interested", requirements={"id"="\d+"})
     */
    public function index(Sharable $sharable, Request $request, InterestedRepository $interestedRepository): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::INTERESTED, $sharable);

        $interesteds = $interestedRepository->findBy(['sharable' => $sharable->getId()], ['id' => 'DESC']);

        return $this->render('interested/index.html.twig', [
            'sharable' => $sharable,
            'interesteds' => $interesteds,
        ]);
    }

    /**
     * @Route("/sharable/{id}/interest", name="sharable_interest", requirements={"id"="\d+"})
     */
    public function interest(Sharable $sharable, Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::INTEREST, $sharable);

        $interested = new Interested();
        $interested->setSharable($sharable);
        $form = $this->createForm(InterestedType::class, $interested);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $interested = $form->getData();
            $interested->setUser($this->getUser())
                ->setSharable($sharable);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($interested);
            $entityManager->flush();

            $dispatcher->dispatch(new InterestedEvent($interested), InterestedEvent::NEW);

            return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
        }

        return $this->render('interested/interest.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/interested/{id}/review", name="interested_review", requirements={"id"="\d+"})
     */
    public function review(Interested $interested, EventDispatcherInterface $dispatcher): Response
    {
        $sharable = $interested->getSharable();
        $this->denyAccessUnlessGranted(SharableVoter::EDIT, $sharable);

        if ($sharable->getInterestedMethod() === 3) {
            $interested->setReviewed(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($interested);
            $entityManager->flush();

            $dispatcher->dispatch(new InterestedEvent($interested), InterestedEvent::REVIEWED);
        }

        return $this->redirectToRoute('sharable_interested', ['id' => $sharable->getId()]);
    }

    /**
     * @Route("/interested/{id}/edit", name="interested_edit", requirements={"id"="\d+"})
     */
    public function edit(Interested $interested, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(InterestedVoter::EDIT, $interested);
        $form = $this->createForm(InterestedType::class, $interested);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $interested = $form->getData();
            $interested->setLastEditedAt(new DateTime());
            $em->persist($interested);
            $em->flush();
            return $this->redirectToRoute('sharable_show', ['id' => $interested->getSharable()->getId()]);
        }

        return $this->render('interested/edit.html.twig', [
            'interested' => $interested,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/interested/{id}/delete", name="interested_delete", requirements={"id"="\d+"})
     */
    public function delete(Interested $interested, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted(InterestedVoter::DELETE, $interested);
        $em->remove($interested);
        $em->flush();
        return $this->redirectToRoute('sharable_show', ['id' => $interested->getSharable()->getId()]);
    }
}
