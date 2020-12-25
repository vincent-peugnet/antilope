<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SignUpType;
use App\Repository\UserClassRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class SignUpController extends AbstractController
{
    /**
     * @Route("/signup", name="sign_up")
     */
    public function index(
        Request $request,
        UserClassRepository $userClassRepository,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        $user = new User();

        $needCode = !$this->getParameter('app.openRegistration');
        $form = $this->createForm(SignUpType::class, $user, ['needCode' => $needCode]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $userClass = $userClassRepository->findOneBy([], ['rank' => 'ASC']);
            $user->setUserClass($userClass);


            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );        }


        return $this->render('sign_up/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
