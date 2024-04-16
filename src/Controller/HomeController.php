<?php

namespace App\Controller;
use App\Entity\Anime;
use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                $route = $this->generateUrl('app_result_query', ['query' => $data . '&page=1']);
                $response = new RedirectResponse($route);
    
                // Retournez la redirection
                return $response;
            }
            return $this->render('home/index.html.twig', [
                'searchForm' => $form,
            ]);
        }
        return $this->render('home/index.html.twig', [
            'searchForm' => $form,
        ]);
    }
}
