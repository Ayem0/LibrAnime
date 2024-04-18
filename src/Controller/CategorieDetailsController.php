<?php

namespace App\Controller;
use App\Entity\Liste;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\SearchFormType;
use App\Form\CreateListFormType;

class CategorieDetailsController extends AbstractController
{
    #[Route('/categories/{id}', name: 'app_categorie_details')]

    public function index(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $category = $doctrine->getRepository(Categorie::class)->findOneBy(['id' => $id]);
        $animeInCategory = $category->getAnimes()->toArray();
        usort($animeInCategory, function($a, $b) {
            return strcmp($a->getNom(), $b->getNom());
        });
        $listAnime = $animeInCategory;

        $listsArray = [];
        $user = $this->getUser();
        if ( $user ) {
            $userLists = $user->getListes()->toArray();
            usort($userLists, function($a, $b) {
                $nomA = strtolower($a->getNom());
                $nomB = strtolower($b->getNom());
                return strcmp($nomA, $nomB);
            });
            $listsArray = $userLists;
        }
        
        $formsArray = [];
        foreach($listAnime as $anime) {
            $list = new Liste();
            $form2 = $this->createForm(CreateListFormType::class, $list);
            $form2View = $form2->createView();
            $formsArray[$anime->getId()] = $form2View;
        }



        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data . '&page=1&nsw=true']);
            }
        }

        return $this->render('categorie_details/index.html.twig', [
            'listeAnime' => $listAnime,
            'categorie' => $category->getNom(),
            'searchForm' => $form,
            'lists' => $listsArray,
            'createListForm' => $formsArray,
            
        ]);
    }
}
