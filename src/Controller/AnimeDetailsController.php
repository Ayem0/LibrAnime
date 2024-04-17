<?php

namespace App\Controller;

use App\Entity\Anime;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchFormType;

class AnimeDetailsController extends AbstractController
{
    #[Route('/anime/details/{id}', name: 'app_anime_details')]
    public function index(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $anime = $doctrine->getRepository(Anime::class)->findOneBy(['id' => $id]);
        $categories = $anime->getCategorie();
        $categoriesArray = [];
        foreach ($categories as $element) {
            $categoriesArray[] = $element;
        };
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data . '&page=1&nsw=true']);
            }
        }
        return $this->render('anime_details/index.html.twig', [
            'anime' => $anime,
            'categories'=> $categoriesArray,
            'searchForm' => $form,
        ]);
    }
}
