<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\Anime;
use App\Form\SearchFormType;
use App\Form\CreateListFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;

class ResultController extends AbstractController
{
    #[Route('/result/{query}', name: 'app_result_query')]
    public function index(Request $request, EntityManagerInterface $entityManager, $query): Response
    {
        // requete api
        $client = HttpClient::create();
        $response = $client->request('GET', "https://api.jikan.moe/v4/anime?q=$query");

        $animesToShow = [];
        $pages = '';
        $nbResult = 0;
        $formArray = [];

        $user = $this->getUser();
        $userLists = $user->getListes();
        $listsArray = [];
        foreach ($userLists as $element) {
            $listsArray[] = $element;
        };
        $listsArray = array_reverse($listsArray);

      
        // si bon
        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $result = json_decode($content, true);
            $animeData = $result['data'];
            $pages = $result['pagination']['last_visible_page'];
            $nbResult = $result['pagination']['items']['total'];
            foreach ($animeData as $anime) {
                $title = $anime['title'];

                $list = new Liste();
                $form2 = $this->createForm(CreateListFormType::class, $list);
                $form2View = $form2->createView();
                $formArray[$title] = $form2View;

                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['nom' => $title]);

                if (!$existingAnime) {
                    $newAnime = new Anime();
                    $newAnime->setNom($title);
                    $entityManager->persist($newAnime);
                    $animesToShow[] = $newAnime;
                } else {
                    if (in_array($existingAnime, $animesToShow, false) == false) {
                        $animesToShow[] = $existingAnime;
                    }
                }
                $entityManager->flush();
            }
        }
        else {
            echo 'La requête API a échoué.';
        }
        
        if (isset($form2)) {
            $form2->handleRequest($request);
            if ($form2->isSubmitted() && $form2->isValid()) {
                $list->setUserId($user);
                $entityManager->persist($list);
                $entityManager->flush();
                return  $this->redirectToRoute('app_result_query', ['query' => $query ]);
            }
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
        return $this->render('result/index.html.twig', [
            'searchForm' => $form,
            'animes' => $animesToShow,
            'pages' => $pages,
            'query' => $query,
            'nbResult' => $nbResult,
            'form2' => $formArray,
            'lists'=> $listsArray
        ]);
    }
    #[Route('/list/{liste<\d+>}/add-anime/{anime<\d+>}', name: 'app_add_anime_in_list')]
    public function addAnimeInList(Liste $liste, Anime $anime, Request $request, EntityManagerInterface $entityManager): Response
    {
        $liste->addAnime($anime);
        $entityManager->persist($liste);
        $entityManager->flush();
        return $this->redirect($request->headers->get('referer'));
    }
}
