<?php

namespace App\Controller;

use App\Entity\Invitation;
use App\Form\InvitationCreateType;
use App\Repository\InvitationRepository;
use DateInterval;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;


class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="account")
     */
    public function index(): Response
    {
        return $this->render('account/index.html.twig', [
        ]);
    }

    /**
     * @Route("/account/invitation", name="account_invitation")
     */
    public function invitation(InvitationRepository $invitationRepository): Response
    {
        $invitationDuration = new DateInterval(
            'PT' .$this->getParameter('app.invitationDuration'). 'H'
        );

        $usedInvitations = $invitationRepository->findUsedInvitations($this->getUser());

        $activeInvitations = $invitationRepository->findActiveInvitations(
            $this->getUser(),
            $invitationDuration
        );


        return $this->render('account/invitation.html.twig', [
            'usedInvitations' => $usedInvitations,
            'activeInvitations' => $activeInvitations,
            'invitationDuration' => $invitationDuration,
        ]);
    }


    /**
     * @Route("/account/invitation/new", name="account_invitation_new")
     */
    public function newInvitation(InvitationRepository $invitationRepository, Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();
        $canInvite = $user->getUserClass()->getCanInvite();
        if (!$canInvite) {
            throw $this->createAccessDeniedException();
        }


        $invitation = new Invitation();
        $form =$this->createForm(InvitationCreateType::class);

        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invitation = $form->getData();

            $invitation->setParent($user);
            
            $uuid = Uuid::v4();
            $code = $uuid->toBase58();
            $codeExist = $invitationRepository->findOneBy(['code' => $code]);
            if (!$codeExist) {
                $invitation->setCode($code);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invitation);
            $entityManager->flush();

            return $this->redirectToRoute('account_invitation');
        }

        $invitationDuration = new DateInterval(
            'PT' .$this->getParameter('app.invitationDuration'). 'H'
        );


        return $this->render('account/invitation_new.html.twig', [
            'form' => $form->createView(),
            'invitationDuration' => $invitationDuration,
        ]);
    }

}