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

use App\Entity\Sharable;
use App\Form\GalleryType;
use App\Security\Voter\SharableVoter;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends AbstractController
{

    /**
     * @Route("/sharable/{id}/gallery", name="sharable_gallery", requirements={"id"="\d+"})
     */
    public function index(Sharable $sharable, Request $request, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::EDIT, $sharable);

        $form = $this->createForm(GalleryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                $fileUploader->upload($image, FileUploader::GALLERY, $sharable);
            }
            $this->redirectToRoute('sharable_gallery', ['id' => $sharable->getId()]);
        }

        $galleryAbsolutePath = $fileUploader->gallery($sharable);
        $fileSystem = new Filesystem();

        if ($fileSystem->exists($galleryAbsolutePath)) {
            $images = new Finder();
            $images->files()->in($galleryAbsolutePath);
        } else {
            $images = [];
        }


        return $this->render('sharable/gallery.html.twig', [
            'sharable' => $sharable,
            'form' => $form->createView(),
            'images' => $images,
            'galleryPath' => $sharable->getGalleryPath(),
        ]);
    }


    /**
     * @Route(
     *      "/sharable/{sharable_id}/gallery/{image_id}/delete",
     *      name="sharable_gallery_delete",
     *      requirements={"image_id"="[a-zA-Z0-9\-_.]+", "sharable_id"="\d+"}
     * )
     * @paramConverter("sharable", options={"mapping": {"sharable_id": "id"}})
     */
    public function delete(Sharable $sharable, string $image_id, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted(SharableVoter::EDIT, $sharable);
        $file = $fileUploader->gallery($sharable) . '/' . $image_id;
        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($file)) {
            throw $this->createNotFoundException("Image does not exist in gallery");
        } else {
            $fileSystem->remove($file);
            return $this->redirectToRoute('sharable_gallery', ['id' => $sharable->getId()]);
        }
    }
}
