<?php

namespace App\Controller;

use App\Form\CreateListFormType;
use App\Form\SearchFormType;
use App\Entity\Liste;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class MyListsController extends AbstractController
{
    #[Route('/my-lists', name: 'app_my_lists')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $list = new Liste();
        $form = $this->createForm(CreateListFormType::class, $list);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $list->setUserId($user);
            $entityManager->persist($list);
            $entityManager->flush();
            // do anything else you need here, like send an email
        }
        
        $listsArray = [];

        if ($user) {
            $lists = $user->getListes()->getValues(); 
            usort($lists, function($a, $b) {
                $nomA = strtolower($a->getNom());
                $nomB = strtolower($b->getNom());
                return strcmp($nomA, $nomB);
            });
            $listsArray = $lists;
        }
        $listsCount = [];
        foreach ($listsArray as $element) {
            $animeInList = $element->getAnime();
            $listsCount[$element->getId()] = count($animeInList);
        }

        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = strval($searchForm->get('search')->getData());
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $data = strval($searchForm->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data, 'page' => 1]);
            }
        }
        return $this->render('my_lists/index.html.twig', [
            'lists'=> $listsArray,
            'form'=>$form,
            'searchForm' => $searchForm,
            'listsCount' => $listsCount
        ]);
    }
    
    #[Route('/my-lists/remove/{id}', name: 'app_remove_list')]
    public function removeList(EntityManagerInterface $entityManager, Liste $list): Response
    {
        $user = $this->getUser();
        if ($list->getUserId() === $user) {
            $user->removeListe($list); 
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_my_lists');
    }
}
