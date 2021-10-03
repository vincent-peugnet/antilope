<?php

namespace App\Controller;

use App\Entity\ReportSharable;
use App\Entity\Sharable;
use App\Form\ReportSharableType;
use App\Security\Voter\SharableVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportSharableController extends AbstractController
{

    /**
     * @Route("/sharable/{id}/report", name="sharable_report_new", requirements={"id"="\d+"})
     */
    public function new(
        Sharable $sharable,
        Request $request,
        EntityManagerInterface $em,
        EventDispatcherInterface $dispatcher
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

        return $this->render('report_sharable/new.html.twig', [
            'form' => $form->createView(),
            'sharable' => $sharable,
        ]);
    }
}
