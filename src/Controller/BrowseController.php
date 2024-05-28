<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Form\CreateListFormType;
use App\Repository\AnimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchFormType;
use App\Form\FilterFormType;
use App\Entity\Liste;
use Doctrine\ORM\EntityManagerInterface;

class BrowseController extends AbstractController
{
    #[Route('/browse', name: 'app_browse')]
    public function index(Request $request, AnimeRepository $repository, EntityManagerInterface $entityManager): Response
    {

        

        $listsArray = [];
        $user = $this->getUser();
        if ( $user ) {
            $userLists = $user->getListes()->toArray();
            usort($userLists, function($a, $b) {
                $nomA = strtolower($a->getNom());
                $nomB = strtolower($b->getNom());
                return strcmp($nomA, $nomB);
            });
            foreach ($userLists as $element) {
                $listsArray[] = $element;
            };
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

        
        
        $data = new SearchData();
        $data->page = $request->get('page', 1);
        $filterForm = $this->createForm(FilterFormType::class, $data);
        $filterForm->handleRequest($request);
        $animes = $repository->findSearch($data);
        
        $listForms = [];
        foreach ($animes as $anime) {
            $list = new Liste();
            $form2 = $this->createForm(CreateListFormType::class, $list);
            $listForms[$anime->getId()] = $form2->createView();
        }
        if (isset($form2)) {
            $form2->handleRequest($request);
            if ($form2->isSubmitted() && $form2->isValid()) {
                $list->setUserId($user);
                $entityManager->persist($list);
                $entityManager->flush();
                
                return $this->redirect($request->headers->get('referer'));
            }
        }
        return $this->render('browse/index.html.twig', [
            'animes' => $animes,
            'lists' => $listsArray,
            'searchForm' => $form,
            'filterForm' =>$filterForm->createView(),
            'listForm' => $listForms,
        ]);
    }
}
