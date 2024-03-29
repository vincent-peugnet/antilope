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

use App\Entity\ReportSharable;
use App\Entity\Sharable;
use App\Form\ReportSharableType;
use App\Security\Voter\ReportSharableVoter;
use App\Security\Voter\SharableVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ReportSharableController extends AbstractController
{

    /**
     * @Route("/sharable/{id}/report/new", name="sharable_report_new", requirements={"id"="\d+"})
     */
    public function new(
        Sharable $sharable,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(SharableVoter::REPORT, $sharable);
        $form = $this->createForm(ReportSharableType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reportSharable = $form->getData();
            assert($reportSharable instanceof ReportSharable);
            $reportSharable->setUser($this->getUser());
            $reportSharable->setSharable($sharable);
            $em->persist($reportSharable);
            $em->flush();

            // dispatch event

            return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
        }

        return $this->render('sharable/report/new.html.twig', [
            'form' => $form->createView(),
            'sharable' => $sharable,
        ]);
    }

    /**
     * @Route(
     *      "/sharable/{sharable_id}/report/{report_id}",
     *      name="sharable_report_show",
     *      requirements={"report_id"="\d+", "sharable_id"="\d+"}
     * )
     * @paramConverter("sharable", options={"mapping": {"sharable_id": "id"}})
     * @paramConverter("reportSharable", options={"mapping": {"report_id": "id"}})
     */
    public function show(
        Sharable $sharable,
        ReportSharable $reportSharable
    ): Response {
        if ($reportSharable->getSharable() !== $sharable) {
            throw $this->createNotFoundException('Report doest not match sharable');
        }
        $this->denyAccessUnlessGranted(ReportSharableVoter::VIEW, $reportSharable->getSharable());

        return $this->render('sharable/report/show.html.twig', [
            'reportSharable' => $reportSharable,
        ]);
    }

    /**
     * @Route("/sharable/{id}/report", name="sharable_report_index", requirements={"id"="\d+"})
     */
    public function index(Sharable $sharable): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::VIEW_REPORTS, $sharable);

        return $this->render('sharable/report/index.html.twig', [
            'sharable' => $sharable,
            'reports' => $sharable->getReports(),
        ]);
    }
}
