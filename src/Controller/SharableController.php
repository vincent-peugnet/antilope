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

use App\Entity\Bookmark;
use App\Entity\Interested;
use App\Entity\Manage;
use App\Entity\Question;
use App\Entity\SharableSearch;
use App\Entity\Sharable;
use App\Entity\SharableContact;
use App\Entity\User;
use App\Entity\Validation;
use App\Event\QuestionEvent;
use App\Event\ValidationEvent;
use App\Form\QuestionType;
use App\Form\SharableContactType;
use App\Form\SharableSearchType;
use App\Form\SharableType;
use App\Form\ValidationType;
use App\Repository\BookmarkRepository;
use App\Repository\InterestedRepository;
use App\Repository\ManageRepository;
use App\Repository\SharableRepository;
use App\Repository\UserClassRepository;
use App\Repository\ValidationRepository;
use App\Security\Voter\SharableVoter;
use App\Service\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SharableController extends AbstractController
{
    /**
     * @Route("/sharable", name="sharable")
     */
    public function index(
        Request $request,
        SharableRepository $sharableRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $search = new SharableSearch();

        $form = $this->createForm(SharableSearchType::class, $search);
        $form->handleRequest($request);
        /** @var SharableSearch $search */
        $search = $form->getData();

        $sharables = $sharableRepository->getFilteredSharables($search, $user);

        $validatedSharables = $user->getValidations()->map(function (Validation $validation) {
            return $validation->getSharable();
        });
        $interestedSharables = $user->getInteresteds()->map(function (Interested $interested) {
            return $interested->getSharable();
        });
        $managedSharables = $user->getManages()->map(function (Manage $manage) {
            return $manage->getSharable();
        });
        $bookmarkedSharables = $user->getBookmarks()->map(function (Bookmark $bookmark) {
            return $bookmark->getSharable();
        });

        return $this->render('sharable/index.html.twig', [
            'sharables' => $sharables,
            'sharable' => new Sharable(),
            'total' => count($sharables),
            'searchForm' => $form->createView(),
            'validatedSharables' => $validatedSharables,
            'interestedSharables' => $interestedSharables,
            'managedSharables' => $managedSharables,
            'bookmarkedSharables' => $bookmarkedSharables,
            'search' => $search,
        ]);
    }

    /**
     * @Route("/sharable/{id}", name="sharable_show", requirements={"id"="\d+"})
     */
    public function show(
        Sharable $sharable,
        InterestedRepository $interestedRepository,
        ValidationRepository $validationRepository,
        ManageRepository $manageRepository,
        BookmarkRepository $bookmarkRepository
    ): Response {
        $this->denyAccessUnlessGranted(SharableVoter::VIEW, $sharable);

        $user = $this->getUser();
        assert($user instanceof User);

        $interested = $interestedRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);

        $validated = $validationRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);

        $manage = $manageRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);

        $bookmarked = $bookmarkRepository->findOneBy([
            'user' => $user->getId(),
            'sharable' => $sharable->getId()
        ]);

        return $this->render('sharable/show.html.twig', [
            'sharable' => $sharable,
            'interested' => $interested,
            'validated' => $validated,
            'manage' => $manage,
            'bookmarked' => $bookmarked,
        ]);
    }



    /**
     * @Route("/sharable/{id}/edit", name="sharable_edit", requirements={"id"="\d+"})
     */
    public function edit(
        Sharable $sharable,
        UserClassRepository $userClassRepository,
        FileUploader $fileUploader,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('edit', $sharable);

        $form = $this->createForm(SharableType::class, $sharable);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sharable = $form->getData();
            assert($sharable instanceof Sharable);

            $coverFile = $form->get('coverFile')->getData();
            if ($coverFile) {
                $cover = $fileUploader->upload($coverFile, FileUploader::COVER);
                if ($sharable->getCover()) {
                    $fileUploader->remove($sharable->getCover(), FileUploader::COVER);
                }
                $sharable->setCover($cover);
            }

            $sharable->setLastEditedAt(new DateTime());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sharable);
            $entityManager->flush();

            return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
        }

        return $this->render('sharable/edit.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
            'canAccessUserClass' => $userClassRepository->findBy(['access' => true]),
        ]);
    }

    /**
     * @Route("/sharable/{id}/contact", name="sharable_contact", requirements={"id"="\d+"})
     */
    public function contact(Sharable $sharable): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::CONTACT, $sharable);

        return $this->render('sharable/contact.html.twig', [
            'sharable' => $sharable,
        ]);
    }

    /**
     * @Route("/sharable/{id}/contact/add", name="sharable_contact_add", requirements={"id"="\d+"})
     */
    public function contactAdd(Sharable $sharable, Request $request): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::EDIT, $sharable);

        $form = $this->createForm(SharableContactType::class, new SharableContact());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sharableContact = $form->getData();
            assert($sharableContact instanceof SharableContact);
            $sharableContact->setSharable($sharable);

            $em = $this->getDoctrine()->getManager();
            $em->persist($sharableContact);
            $em->flush();

            return $this->redirectToRoute('sharable_contact', ['id' => $sharable->getId()]);
        }

        return $this->render('sharable/contact_add.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/sharable/{id}/validate", name="sharable_validate", requirements={"id"="\d+"})
     * @todo send Email to each managers managing validated sharable and when user is promoted
     */
    public function validate(
        Sharable $sharable,
        Request $request,
        InterestedRepository $interestedRepository,
        EventDispatcherInterface $dispatcher
    ): Response {
        $this->denyAccessUnlessGranted('validate', $sharable);

        $user = $this->getUser();
        assert($user instanceof User);
        $form = $this->createForm(ValidationType::class, new Validation());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $validation = $form->getData();

            $validation->setUser($user);
            $validation->setSharable($sharable);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($validation);

            $dispatcher->dispatch(new ValidationEvent($validation), ValidationEvent::NEW);

            $interested = $interestedRepository->findOneBy(['user' => $user, 'sharable' => $sharable]);
            if (!is_null($interested)) {
                $entityManager->remove($interested);
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
     * @Route("/sharable/{id}/activation", name="sharable_activation", requirements={"id"="\d+"})
     */
    public function activation(Sharable $sharable): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::EDIT, $sharable);
        $em = $this->getDoctrine()->getManager();
        $sharable->setDisabled($sharable->getDisabled() ? false : true);
        $em->persist($sharable);
        $em->flush();

        return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
    }

    /**
     * @Route("/sharable/{id}/bookmark", name="sharable_bookmark", requirements={"id"="\d+"})
     */
    public function bookmark(
        Sharable $sharable,
        BookmarkRepository $bookmarkRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted(SharableVoter::VIEW, $sharable);
        $bookmark = $bookmarkRepository->findOneBy(['user' => $this->getUser(), 'sharable' => $sharable]);
        if (!is_null($bookmark)) {
            $em->remove($bookmark);
        } else {
            $bookmark = new Bookmark();
            $bookmark->setSharable($sharable)
                ->setUser($this->getUser());
            $em->persist($bookmark);
        }
        $em->flush();

        return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
    }

    /**
     * @Route("/sharable/new", name="sharable_new")
     */
    public function new(
        UserClassRepository $userClassRepository,
        FileUploader $fileUploader,
        Request $request
    ): Response {
        $sharable = new Sharable();

        $this->denyAccessUnlessGranted('create', $sharable);

        $form = $this->createForm(SharableType::class, $sharable);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sharable = $form->getData();
            assert($sharable instanceof Sharable);

            $coverFile = $form->get('coverFile')->getData();
            if ($coverFile) {
                $cover = $fileUploader->upload($coverFile, FileUploader::COVER);
                if ($sharable->getCover()) {
                    $fileUploader->remove($sharable->getCover(), FileUploader::COVER);
                }
                $sharable->setCover($cover);
            }

            $manage = new Manage();
            $manage->setSharable($sharable)
                ->setUser($this->getUser())
                ->setContactable(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($sharable);
            $em->persist($manage);
            $em->flush();

            return $this->redirectToRoute('sharable_show', ['id' => $sharable->getId()]);
        }


        return $this->render('sharable/new.html.twig', [
            'form' => $form->createView(),
            'canAccessUserClass' => $userClassRepository->findBy(['access' => true]),
        ]);
    }
}
