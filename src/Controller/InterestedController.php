<?php

namespace App\Controller;

use App\Entity\Interested;
use App\Entity\Sharable;
use App\Form\InterestedType;
use App\Repository\InterestedRepository;
use App\Security\Voter\SharableVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function interest(Sharable $sharable, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::INTEREST, $sharable);

        $form = $this->createForm(InterestedType::class, new Interested);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $interested = $form->getData();
            $interested->setUser($this->getUser())
                ->setSharable($sharable);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($interested);
            $entityManager->flush();

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
    public function review(Interested $interested): Response
    {
        $sharable = $interested->getSharable();
        $this->denyAccessUnlessGranted(SharableVoter::EDIT, $sharable);

        if ($sharable->getInterestedMethod() === 3) {
            $interested->setReviewed(true);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($interested);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sharable_interested', ['id' => $sharable->getId()]);
    }
}
