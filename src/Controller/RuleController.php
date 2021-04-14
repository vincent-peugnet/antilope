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

use App\Entity\Rule;
use App\Form\RuleType;
use App\Repository\RuleRepository;
use App\Security\Voter\RuleVoter;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rule")
 */
class RuleController extends AbstractController
{
    /**
     * @Route("/", name="rule_index", methods={"GET"})
     */
    public function index(RuleRepository $ruleRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $rulesPagination = $paginator->paginate(
            $ruleRepository->findAllQuery(),
            $request->query->getInt('page', 1),
            $this->getParameter('app.result_per_page')
        );

        $rulesPagination->setCustomParameters(['align' => 'center']);

        return $this->render('rule/index.html.twig', [
            'rulesPagination' => $rulesPagination,
        ]);
    }

    /**
     * @Route("/new", name="rule_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $rule = new Rule();
        $this->denyAccessUnlessGranted(RuleVoter::CREATE, $rule);

        $form = $this->createForm(RuleType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rule);
            $entityManager->flush();

            return $this->redirectToRoute('rule_show', ['id' => $rule->getId()]);
        }

        return $this->render('rule/new.html.twig', [
            'rule' => $rule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="rule_show", methods={"GET"})
     */
    public function show(Rule $rule): Response
    {
        $this->denyAccessUnlessGranted(RuleVoter::VIEW, $rule);
        return $this->render('rule/show.html.twig', [
            'rule' => $rule,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="rule_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Rule $rule): Response
    {
        $this->denyAccessUnlessGranted(RuleVoter::EDIT, $rule);

        $form = $this->createForm(RuleType::class, $rule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rule->setLastEditedAt(new DateTime());
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($rule);
            $manager->flush();

            return $this->redirectToRoute('rule_show', ['id' => $rule->getId()]);
        }

        return $this->render('rule/edit.html.twig', [
            'rule' => $rule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="rule_delete")
     */
    public function delete(Request $request, Rule $rule): Response
    {
        $this->denyAccessUnlessGranted(RuleVoter::DELETE, $rule);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($rule);
        $entityManager->flush();

        return $this->redirectToRoute('rule_index');
    }
}
