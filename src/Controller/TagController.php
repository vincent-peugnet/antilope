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

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use App\Security\Voter\TagVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin/tag")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="tag_index")
     */
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tag' => new Tag(),
            'tags' => $tagRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="tag_new")
     */
    public function new(Request $request)
    {
        $tag = new Tag();
        $this->denyAccessUnlessGranted(TagVoter::CREATE, $tag);

        $form = $this->createForm(TagType::class, $tag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="tag_edit", requirements={"id"="\d+"})
     */
    public function edit(Tag $tag, Request $request): Response
    {
        $this->denyAccessUnlessGranted(TagVoter::EDIT, $tag);

        $form = $this->createForm(TagType::class, $tag);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tag);
            $entityManager->flush();
            return $this->redirectToRoute('tag_index');
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="tag_delete", requirements={"id"="\d+"})
     */
    public function delete(Tag $tag): Response
    {
        $this->denyAccessUnlessGranted(TagVoter::DELETE, $tag);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($tag);
        $entityManager->flush();
        return $this->redirectToRoute('tag_index');
    }
}
