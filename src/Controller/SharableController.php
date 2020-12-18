<?php

namespace App\Controller;

use App\Entity\Sharable;
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
}
