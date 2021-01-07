<?php

namespace App\Controller;

use App\Entity\Manage;
use App\Entity\SharableSearch;
use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\Validation;
use App\Form\ManagerType;
use App\Form\ManageType;
use App\Form\SharableSearchType;
use App\Form\SharableType;
use App\Form\ValidationType;
use App\Repository\SharableRepository;
use App\Repository\UserClassRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use App\Security\Voter\SharableVoter;
use App\Security\Voter\UserVoter;
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
    public function index(Request $request, SharableRepository $sharableRepository, UserClassRepository $userClassRepository, UserRepository $userRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $visibleBy = $userClassRepository->findLowerthan($user->getUserClass());

        $search = new SharableSearch();

        $form = $this->createForm(SharableSearchType::class, $search);
        $form->handleRequest($request);
        /** @var SharableSearch $search */
        $search = $form->getData();



        if ($search->getManagedBy()) {
            $manager = $userRepository->find($search->getManagedBy());
            if ($manager) {
                $granted = $this->isGranted(UserVoter::VIEW_SHARABLES, $manager);
            } else {
                $granted = true;
            }
        } else {
            $granted = true;
        }

        if ($granted) {
            $sharables = $sharableRepository->getFilteredSharables($search, $visibleBy, $user);
        } else {
            $sharables = [];
        }


        $validatedSharables = $user->getValidations()->map(function (Validation $validation) {
            return $validation->getSharable();
        });

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
        $this->denyAccessUnlessGranted(SharableVoter::VIEW, $sharable);

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

        $form = $this->createForm(ManageType::class, null, ['managedBy' => $sharable->getManagedBy()]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manage = $form->getData();
            $manage->setSharable($sharable);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manage);
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

            foreach ($sharable->getManagedBy() as $manage) {
                $manager = $manage->getUser();
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
     * @Route("/sharable/{id}/validation", name="sharable_validation", requirements={"id"="\d+"})
     */
    public function validation(Sharable $sharable, ValidationRepository $repository): Response
    {
        $validations = $repository->findBy(['sharable' => $sharable->getId()], ['id' => 'DESC']);

        return $this->render('sharable/validation.html.twig', [
            'sharable' => $sharable,
            'validations' => $validations,
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
            
            $manage = new Manage();
            $manage->setSharable($sharable)
                ->setUser($this->getUser())
                ->setContactable(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($sharable);
            $em->persist($manage);
            $em->flush();

            return $this->redirectToRoute('sharable');
        }


        return $this->render('sharable/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
