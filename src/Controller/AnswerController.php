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

use App\Entity\Answer;
use App\Form\AnswerType;
use App\Security\Voter\AnswerVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnswerController extends AbstractController
{
    /**
     * @Route("/answer/{id}/delete", name="answer_delete", requirements={"id"="\d+"})
     */
    public function delete(Answer $answer, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(AnswerVoter::DELETE, $answer);

        $em->remove($answer);
        $em->flush();

        return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
    }
    /**
     * @Route("/answer/{id}/edit", name="answer_edit", requirements={"id"="\d+"})
     */
    public function edit(Answer $answer, EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted(AnswerVoter::EDIT, $answer);

        $form = $this->createForm(AnswerType::class, $answer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $answer = $form->getData();
            assert($answer instanceof Answer);
            $answer->setUser($this->getUser());
            $em->persist($answer);
            $em->flush();

            return $this->redirectToRoute('question_show', ['id' => $answer->getQuestion()->getId()]);
        }

        return $this->render('answer/edit.html.twig', [
            'form' => $form->createView(),
            'question' => $answer->getQuestion(),
            'answer' => $answer,
        ]);
    }
}
