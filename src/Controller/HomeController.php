<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $form = $this->createForm(SearchFormType::class);
        return $this->render('home/index.html.twig', [
            'searchForm' => $form,
        ]);
    }
}
