<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Entity\Liste;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchFormType;
use App\Form\CreateListFormType;
use Doctrine\ORM\EntityManagerInterface;

class AnimeDetailsController extends AbstractController
{
    #[Route('/anime/details/{id}', name: 'app_anime_details')]
    public function index(EntityManagerInterface $entityManager,ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $anime = $doctrine->getRepository(Anime::class)->findOneBy(['id' => $id]);
        $categories = $anime->getCategorie();
        $categoriesArray = [];
        $listsArray = [];
        $user = $this->getUser();
        if ( $user ) {
            $userLists = $user->getListes()->toArray();
            usort($userLists, function($a, $b) {
                return strcmp($a->getNom(), $b->getNom());
            });
            foreach ($userLists as $element) {
                $listsArray[] = $element;
            };
        }
        $list = new Liste();
        $listForm = $this->createForm(CreateListFormType::class, $list);
        if (isset($listForm)) {
            $listForm->handleRequest($request);
            if ($listForm->isSubmitted() && $listForm->isValid()) {
                $list->setUserId($user);
                $entityManager->persist($list);
                $entityManager->flush();
                
                return  $this->redirectToRoute('app_anime_details', ['id' => $id ]);
            }
        }
        foreach ($categories as $element) {
            $categoriesArray[] = $element;
        };
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = strval($searchForm->get('search')->getData());
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $data = strval($searchForm->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data . '&page=1&nsw=true']);
            }
        }
        return $this->render('anime_details/index.html.twig', [
            'anime' => $anime,
            'categories'=> $categoriesArray,
            'searchForm' => $searchForm,
            'listForm' => $listForm->createView(),
            'lists' => $listsArray,
        ]);
    }
}
