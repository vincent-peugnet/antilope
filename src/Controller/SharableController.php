<?php

namespace App\Controller;

use App\Entity\Sharable;
use App\Entity\Validation;
use App\Form\ManagerType;
use App\Form\SharableType;
use App\Form\ValidationType;
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
            'sharable' => new Sharable(),
        ]);
    }

    /**
     * @Route("/sharable/{id}", name="sharable_show", requirements={"id"="\d+"})
     */
    public function show(Sharable $sharable)
    {
        $this->denyAccessUnlessGranted('view', $sharable);

        return $this->render('sharable/show.html.twig', [
            'sharable' => $sharable,
        ]);
    }



    /**
     * @Route("/sharable/{id}/edit", name="sharable_edit", requirements={"id"="\d+"})
     */
    public function edit(Sharable $sharable, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $sharable);

        $form = $this->createForm(SharableType::class, $sharable);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sharable = $form->getData();
            

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

    /**
     * @Route("/sharable/{id}/managers", name="sharable_managers", requirements={"id"="\d+"})
     */
    public function managers(Sharable $sharable, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $sharable);

        $form = $this->createForm(ManagerType::class, null, ['managedBy' => $sharable->getManagedBy()]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $user = $form->getData()['managedBy'];

            $sharable->addManagedBy($user);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sharable);
            $entityManager->flush();

            return $this->redirectToRoute('sharable_managers', ['id' => $sharable->getId()]);

        }

        return $this->render('sharable/managers.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
        ]);

    }


    /**
     * @Route("/sharable/{id}/validate", name="sharable_validate", requirements={"id"="\d+"})
     */
    public function validate(Sharable $sharable, Request $request): Response
    {
        $this->denyAccessUnlessGranted('validate', $sharable);

        $validation = new Validation();
        $form =$this->createForm(ValidationType::class);

        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $validation = $form->getData();

            $validation->setUser($this->getUser());
            $validation->setSharable($sharable);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($validation);
            $entityManager->flush();

            return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
        }

        return $this->render('sharable/validate.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/sharable/new", name="sharable_new")
     */
    public function new(Request $request): Response
    {
        $sharable = new Sharable();

        $this->denyAccessUnlessGranted('create', $sharable);

        $form = $this->createForm(SharableType::class, $sharable);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sharable = $form->getData();
            
            $sharable->addManagedBy($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sharable);
            $entityManager->flush();

            return $this->redirectToRoute('sharable');
        }


        return $this->render('sharable/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
