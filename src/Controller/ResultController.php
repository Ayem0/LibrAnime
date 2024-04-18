<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\Categorie;
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
        $response = $client->request('GET', "https://api.jikan.moe/v4/anime?q=$query&sfw=true&genres_exclude=9,49,12");

        $animesToShow = [];
        $pages = '';
        $nbResult = 0;
        $formArray = [];
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

       
      
        // si bon
        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();
            $result = json_decode($content, true);
            $animeData = $result['data'];
            $pages = $result['pagination']['last_visible_page'];
            $nbResult = $result['pagination']['items']['total'];
            foreach ($animeData as $anime) {
                $title = $anime['title'];
                $mailId = $anime['mal_id'];
                $image = $anime['images']['jpg']['image_url'];
                $trailerUrl = $anime['trailer']['url'];
                $trailerImg = $anime['trailer']['images']['image_url'];
                $synopsis = $anime['synopsis'];
                $year = $anime['year'];
                $episodes = $anime['episodes'];
                $demographics = $anime['demographics']; 
                $genres = $anime['genres'];

                $list = new Liste();
                $form2 = $this->createForm(CreateListFormType::class, $list);
                $form2View = $form2->createView();
                $formArray[$title] = $form2View;

                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['nom' => $title]);

                if (!$existingAnime) {
                    $newAnime = new Anime();
                    $newAnime->setNom($title);
                    $newAnime->setImage($image);
                    $newAnime->setMalId($mailId);
                    $newAnime->setTrailerImg($trailerImg);
                    $newAnime->setTrailerUrl($trailerUrl);
                    $newAnime->setSynopsis($synopsis);
                    $newAnime->setYear($year);
                    $newAnime->setEpisodes($episodes);
                    for ($i = 0; $i < count($demographics); $i++) {
                        // Votre code ici
                        $categorieName = $demographics[$i]['name'];
                        $existingCategorie = $entityManager->getRepository(Categorie::class)->findOneBy(['nom' => $categorieName]);
                        if (!$existingCategorie) {
                            $newCategorie = new Categorie();
                            $newCategorie->setNom($categorieName);
                            $entityManager->persist($newCategorie);
                            $entityManager->flush();
                            $newAnime->addCategorie($newCategorie);
                        } else {
                            $newAnime->addCategorie($existingCategorie);
                        }
                    }
                    for ($i = 0; $i < count($genres); $i++) {
                        // Votre code ici
                        $categorieName = $genres[$i]['name'];
                        $existingCategorie = $entityManager->getRepository(Categorie::class)->findOneBy(['nom' => $categorieName]);
                        if (!$existingCategorie) {
                            $newCategorie = new Categorie();
                            $newCategorie->setNom($categorieName);
                            $entityManager->persist($newCategorie);
                            $entityManager->flush();
                            $newAnime->addCategorie($newCategorie);
                        } else {
                            $newAnime->addCategorie($existingCategorie);
                        }
                    }
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
            // modifier ici pour faire la requete direct dans ma base de données
            echo 'La requête API a échoué.';
        }
        
        if (isset($form2)) {
            if (isset($form2)) {
                $form2->handleRequest($request);
                if ($form2->isSubmitted() && $form2->isValid()) {
                    $list->setUserId($user);
                    $entityManager->persist($list);
                    $entityManager->flush();
                    
                    return  $this->redirectToRoute('app_result_query', ['query' => $query ]);
                }
            }
        }
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = strval($searchForm->get('search')->getData());
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $data = strval($searchForm->get('search')->getData());
                
                return  $this->redirectToRoute('app_result_query', ['query' => $data . '&page=1']);
            }
        }
        return $this->render('result/index.html.twig', [
            'searchForm' => $searchForm,
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
