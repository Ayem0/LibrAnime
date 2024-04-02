<?php

namespace App\Controller;
use App\Form\SearchFormType;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoriesController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Categorie::class)->findAll();
        $form2 = $this->createForm(SearchFormType::class);
        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
            'searchForm' => $form2,
        ]);
    }
}
