<?php

namespace App\Controller;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MyAccountController extends AbstractController
{
    #[Route('/my-account', name: 'app_my_account')]
    public function index(): Response
    {
        $form = $this->createForm(SearchFormType::class);
        return $this->render('my_account/index.html.twig', [
            'searchForm' => $form,
        ]);
    }
}
