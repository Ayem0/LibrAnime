<?php

namespace App\Controller;
use App\Form\SearchFormType;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class CategoriesController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $categories = $doctrine->getRepository(Categorie::class)->findAll();
        usort($categories, function($a, $b) {
            return strcmp($a->getNom(), $b->getNom());
        });

        $catgoriesCount = [];
        foreach ($categories as $categorie) {
            $animeInCategorie = $categorie->getAnimes();
            $catgoriesCount[$categorie->getId()] = count($animeInCategorie);
        }
        
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data, 'page' => 1]);
            }
        }
        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
            'animeInCategorie' => $catgoriesCount,
            'searchForm' => $form,
        ]);
    }
}
