<?php

namespace App\Controller;

use App\Entity\Manage;
use App\Entity\Sharable;
use App\Form\ManageType;
use App\Repository\InterestedRepository;
use App\Security\Voter\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManageController extends AbstractController
{

        /**
     * @Route("/sharable/{id}/managers", name="sharable_managers", requirements={"id"="\d+"})
     */
    public function managers(Sharable  $sharable, Request $request, InterestedRepository $interestedRepo): Response
    {
        $this->denyAccessUnlessGranted('edit', $sharable);
        $manage = new Manage();
        $manage->setSharable($sharable);
        $form = $this->createForm(ManageType::class, $manage);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manage = $form->getData();
            assert($manage instanceof Manage);
            $manage->setContactable(false);

            $intrested = $interestedRepo->findOneBy(
                ['user' => $manage->getUser()->getId(),
                'sharable' => $sharable->getId()]
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manage);
            if ($intrested) {
                $entityManager->remove($intrested);
            }
            $entityManager->flush();

            return $this->redirectToRoute('sharable_managers', ['id' => $sharable->getId()]);
        }

        return $this->render('manage/index.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/manage/{id}/uncontactable", name="manage_uncontactable", requirements={"id"="\d+"})
     */
    public function manageUnContactable(Manage $manage): Response
    {
        $user = $manage->getUser();
        $sharable = $manage->getSharable();
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        if (!$sharable->getSharableContacts()->isEmpty() || $sharable->getContactableManagers()->count() > 1) {
            $manage->setContactable(false);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sharable_contact', ['id' => $sharable->getId()]);
    }


    /**
     * @Route("/manage/{id}/contactable", name="manage_contactable", requirements={"id"="\d+"})
     */
    public function manageContactable(Manage $manage): Response
    {
        $user = $manage->getUser();
        $sharable = $manage->getSharable();
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        if ($user->getUserContacts()->count() > 0) {
            $manage->setContactable(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($manage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sharable_contact', ['id' => $sharable->getId()]);
    }



}
