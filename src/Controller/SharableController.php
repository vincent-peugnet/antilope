<?php

namespace App\Controller;

use App\Entity\SharableSearch;
use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
use App\Entity\Validation;
use App\Form\ManagerType;
use App\Form\SharableSearchType;
use App\Form\SharableType;
use App\Form\ValidationType;
use App\Repository\SharableRepository;
use App\Repository\UserClassRepository;
use App\Service\LevelUp;
use App\Service\SharePoints;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class SharableController extends AbstractController
{
    /**
     * @Route("/sharable", name="sharable")
     */
    public function index(Request $request, SharableRepository $sharableRepository, UserClassRepository $userClassRepository): Response
    {
        $validatedSharables = [];
        $sharables = [];
        /** @var User $user */
        $user = $this->getUser();
        $visibleBy = $userClassRepository->findLowerthan($user->getUserClass());

        $search = new SharableSearch();

        $form = $this->createForm(SharableSearchType::class, $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData();


            $validatedSharables = $user->getValidations()->map(function (Validation $validation)
            {
                return $validation->getSharable();
            });

            $sharables = $sharableRepository->getFilteredSharables($search, $visibleBy, $validatedSharables, $user);
        }

        return $this->render('sharable/index.html.twig', [
            'sharables' => $sharables,
            'sharable' => new Sharable(),
            'total' => count($sharables),
            'form' => $form->createView(),
            'validatedSharables' => $validatedSharables,
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
            $sharable->setLastEditedAt(new DateTime());

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
     * @todo send Email to each managers managing validated sharable and when user is promoted
     */
    public function validate(Sharable $sharable, Request $request, LevelUp $levelUp, SharePoints $sharePointAlgo): Response
    {
        $this->denyAccessUnlessGranted('validate', $sharable);

        $validation = new Validation();
        $form =$this->createForm(ValidationType::class);

        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $validation = $form->getData();

            $validation->setUser($this->getUser());
            $validation->setSharable($sharable);

            $sharePoints = $sharePointAlgo->calculate($this->getUser(), $sharable);

            $entityManager = $this->getDoctrine()->getManager();

            foreach ($sharable->getManagedBy() as $manager) {
                $manager->addShareScore($sharePoints);
                $checkedManager = $levelUp->check($manager);
                $entityManager->persist($checkedManager);
            }
            
            $entityManager->persist($validation);

            $user = $levelUp->check($this->getUser());
            if ($user !== $this->getUser()) {
                $entityManager->persist($user);
            }

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
