<?php

namespace App\Controller;

use App\Entity\Sharable;
use App\Form\Type\SharableType;
use App\Repository\SharableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SharableController extends AbstractController
{
    /**
     * @Route("/sharable", name="sharable")
     */
    public function index(Request $request, SharableRepository $sharableRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginatedSharables = $sharableRepository->getSharablePaginator($offset);
        
        return $this->render('sharable/index.html.twig', [
            'sharables' => $paginatedSharables,
            'previous' => $offset - $sharableRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginatedSharables), $offset + SharableRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    /**
     * @Route("/sharable/{id}", name="sharable_show", requirements={"id"="\d+"})
     */
    public function show(Sharable $sharable)
    {
        return $this->render('sharable/show.html.twig', [
            'sharable' => $sharable,
        ]);
    }



    /**
     * @Route("/sharable/{id}/edit", name="sharable_edit", requirements={"id"="\d+"})
     */
    public function edit(Sharable $sharable, Request $request): Response
    {
        // $this->denyAccessUnlessGranted('edit', $sharable);

        $form = $this->createForm(SharableType::class, $sharable);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sharable = $form->getData();
            
            //$sharable->addManagedBy($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sharable);
            $entityManager->flush();

            return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
        }

        return $this->render('sharable/edit.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
        ]);
    }

}
