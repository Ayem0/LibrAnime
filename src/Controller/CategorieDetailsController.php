<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchFormType;

class CategorieDetailsController extends AbstractController
{
    #[Route('/categories/{id}', name: 'app_categorie_details')]

    public function index(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $category = $doctrine->getRepository(Categorie::class)->findOneBy(['id' => $id]);
        $animeInCategory = $category->getAnimes();
        $listAnime = [];
        foreach ($animeInCategory as $element) {
            $listAnime[] = $element;
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
        return $this->render('categorie_details/index.html.twig', [
            'listeAnime' => $listAnime,
            'categorie' => $category->getNom(),
            'searchForm' => $form,
            
        ]);
    }
}
