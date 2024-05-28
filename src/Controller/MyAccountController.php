<?php

namespace App\Controller;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class MyAccountController extends AbstractController
{
    #[Route('/my-account', name: 'app_my_account')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $listCount = count($user->getListes());

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data, 'page' => 1]);
            }
        }
        return $this->render('my_account/index.html.twig', [
            'searchForm' => $form,
            'listCount' => $listCount,
        ]);
    }
}
