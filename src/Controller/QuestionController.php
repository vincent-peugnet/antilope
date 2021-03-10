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
use App\Entity\Question;
use App\Form\AnswerType;
use App\Security\Voter\QuestionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route("/question/{id}/answer", name="answer", requirements={"id"="\d+"})
     */
    public function answer(Question $question, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(QuestionVoter::ANSWER, $question);

        $form = $this->createForm(AnswerType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $answer = $form->getData();
            assert($answer instanceof Answer);
            $answer->setUser($this->getUser());
            $answer->setQuestion($question);
            $em->persist($answer);
            $em->flush();
            return $this->redirectToRoute('sharable_show', ['id' => $question->getSharable()->getId()]);
        }

        return $this->render('question/answer.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }
}
