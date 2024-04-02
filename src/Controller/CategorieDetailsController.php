<?php

namespace App\Controller;

use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchFormType;
class CategorieDetailsController extends AbstractController
{
    #[Route('/categories/{id}', name: 'app_categorie_details')]

    public function index(ManagerRegistry $doctrine, int $id): Response
    {
        $category = $doctrine->getRepository(Categorie::class)->findOneBy(['id' => $id]);
        $animeInCategory = $category->getAnimes();
        $listAnime = [];
        foreach ($animeInCategory as $element) {
            $listAnime[] = $element;
        };
        $form2 = $this->createForm(SearchFormType::class);
        return $this->render('categorie_details/index.html.twig', [
            'listeAnime' => $listAnime,
            'categorie' => $category->getNom(),
            'searchForm' => $form2,
            
        ]);
    }
}
