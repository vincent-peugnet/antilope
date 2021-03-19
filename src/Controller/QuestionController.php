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
use App\Event\QuestionEvent;
use App\Form\AnswerType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Security\Voter\QuestionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route("/question", name="question")
     */
    public function index(QuestionRepository $questionRepository)
    {
        return $this->render('question/index.html.twig', [
            'questions' => $questionRepository->findAllVisible($this->getUser()),
        ]);
    }

    /**
     * @Route("/question/{id}/show", name="question_show", requirements={"id"="\d+"})
     */
    public function show(
        Question $question,
        Request $request,
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(QuestionVoter::VIEW, $question);

        $form = $this->createForm(AnswerType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $answer = $form->getData();
            assert($answer instanceof Answer);
            $answer->setUser($this->getUser());
            $answer->setQuestion($question);
            $em->persist($answer);
            $em->flush();

            $dispatcher->dispatch(new QuestionEvent($question), QuestionEvent::ANSWERED);

            return $this->redirectToRoute('sharable_show', ['id' => $question->getSharable()->getId()]);
        }

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/question/{id}/delete", name="question_delete", requirements={"id"="\d+"})
     */
    public function delete(Question $question, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(QuestionVoter::DELETE, $question);
        $em->remove($question);
        $em->flush();
        return $this->redirectToRoute('sharable_show', ['id' => $question->getSharable()->getId()]);
    }

    /**
     * @Route("/question/{id}/edit", name="question_edit", requirements={"id"="\d+"})
     */
    public function edit(Question $question, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted(QuestionVoter::EDIT, $question);

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData();
            assert($question instanceof Question);
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('sharable_show', ['id' => $question->getSharable()->getId()]);
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }
}
