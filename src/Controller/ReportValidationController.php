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

use App\Entity\ReportValidation;
use App\Entity\Validation;
use App\Form\ReportValidationType;
use App\Security\Voter\ReportValidationVoter;
use App\Security\Voter\ValidationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ReportValidationController extends AbstractController
{

    /**
     * @Route("/validation/{id}/report/new", name="validation_report_new", requirements={"id"="\d+"})
     */
    public function new(
        Validation $validation,
        Request $request,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
    ): Response {
        $this->denyAccessUnlessGranted(ValidationVoter::REPORT, $validation);

        $form = $this->createForm(ReportValidationType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reportValidation = $form->getData();
            assert($reportValidation instanceof ReportValidation);
            $reportValidation->setUser($this->getUser());
            $reportValidation->setValidation($validation);
            $em->persist($reportValidation);
            $em->flush();

            // dispatch event

            return $this->redirectToRoute('sharable_validation', ['id' => $validation->getSharable()->getId()]);
        }

        return $this->render('validation/report/new.html.twig', [
            'form' => $form->createView(),
            'validation' => $validation,
        ]);
    }



    /**
     * @Route(
     *      "/validation/{validation_id}/report/{report_id}",
     *      name="validation_report_show",
     *      requirements={"report_id"="\d+", "validation_id"="\d+"}
     * )
     * @paramConverter("validation", options={"mapping": {"validation_id": "id"}})
     * @paramConverter("reportValidation", options={"mapping": {"report_id": "id"}})
     */
    public function show(
        Validation $validation,
        ReportValidation $reportValidation
    ): Response {
        if ($reportValidation->getValidation() !== $validation) {
            throw $this->createNotFoundException('Report doest not match validation');
        }
        $this->denyAccessUnlessGranted(ReportValidationVoter::VIEW, $reportValidation);

        return $this->render('validation/report/show.html.twig', [
            'reportValidation' => $reportValidation,
        ]);
    }
}
