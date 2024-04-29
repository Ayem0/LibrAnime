<?php

namespace App\Controller;
use App\Form\SearchFormType;
use App\Form\CreateListFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Anime;
use App\Entity\Categorie;
use App\Entity\Liste;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // REQUETE API
        $apiQuery = '
        query ($id: Int, $page: Int, $perPage: Int, $isAdult: Boolean, $excludedGenres: [String], $mediaType: MediaType, $sort: [MediaSort]) {
            Page(page: $page, perPage: $perPage) {
                pageInfo {
                    total
                    currentPage
                    lastPage
                    hasNextPage
                    perPage
                }
                media (id: $id, isAdult: $isAdult, genre_not_in: $excludedGenres, type: $mediaType sort: $sort) {
                    id
                    title {
                        romaji
                    }
                    description
                    coverImage {
                        large
        
                    }
                    startDate {
                        year
                 
                    }
                    endDate {
                        year
                    }
                    episodes
                    status
                    format
                    genres
                    trailer {
                        id
                        site
                        thumbnail
                    }
                }
            }
        }
        ';
        $variables = [
            "page" => 1,
            "perPage" => 5,
            "isAdult" => false,
            "excludedGenres" => ["Ecchi"],
            "mediaType" => "ANIME",
            "sort" => ["SCORE_DESC"],
        ];
        $http = new Client();
        $response = $http->post('https://graphql.anilist.co', [
            'json' => [
                'query' => $apiQuery,
                'variables' => $variables,
            ]
        ]);

        $popularVariables = [
            "page" => 1,
            "perPage" => 25,
            "isAdult" => false,
            "excludedGenres" => ["Ecchi"],
            "mediaType" => "ANIME",
            "sort" => ["POPULARITY_DESC"],
        ];
        $popularHttp = new Client();
        $popularResponse = $popularHttp->post('https://graphql.anilist.co', [
            'json' => [
                'query' => $apiQuery,
                'variables' => $popularVariables,
            ]
        ]);


        $trendingVariables = [
            "page" => 1,
            "perPage" => 10,
            "isAdult" => false,
            "excludedGenres" => ["Ecchi"],
            "mediaType" => "ANIME",
            "sort" => ["TRENDING_DESC"],
        ];
        $trendingHttp = new Client();
        $trendingResponse = $trendingHttp->post('https://graphql.anilist.co', [
            'json' => [
                'query' => $apiQuery,
                'variables' => $trendingVariables,
            ]
        ]);

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

        $topAnimes = [];
        $topForms = [];
        $popularAnimes = [];
        $popularForms = [];
        $trendingAnimes = [];
        $trendingForms = [];

        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            $animeData = $result['data']['Page']['media'];
            foreach ($animeData as $anime) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                
                $title = $anime['title']['romaji'];
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $startYear = $anime['startDate']['year'];
                    $endYear = $anime['endDate']['year'];
                    $episodes = $anime['episodes'];
                    $genres = null;
                    $format = null;
                    $status = $anime['status'];
                    $trailer = $anime['trailer'];
                    if ($anime['genres'] != null) {
                        $genres = $anime['genres'];
                    }
                    if ($anime['format'] != null) {
                        $format = $anime['format'];
                    }

                    $newAnime = new Anime();
                    $newAnime->setNom($title);
                    $newAnime->setImage($image);
                    $newAnime->setMalId($malId);
                    $newAnime->setSynopsis($synopsis);
                    $newAnime->setYear($startYear);
                    $newAnime->setEpisodes($episodes);
                    
                    if ($trailer != null) {
                        $trailerImg = $trailer['thumbnail'];
                        $trailerSite = $trailer['site'];
                        $trailerLink = $trailer['id'];
                        $trailerUrl = '';
                        if ($trailerSite === 'youtube') {
                            $trailerUrl = 'https://www.youtube.com/watch?v=' . $trailerLink;
                        } else {
                            $trailerUrl = 'https://www.dailymotion.com/video/' . $trailerLink;
                        }
                        $newAnime->setTrailerImg($trailerImg);
                        $newAnime->setTrailerUrl($trailerUrl);
                    }
                    
                    if ( $genres != null) {
                        for ($i = 0; $i < count($genres); $i++) {
                            // Votre code ici
                            $categorieName = $genres[$i];
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
                    }
                    $entityManager->persist($newAnime);
                    $entityManager->flush();
                    $topAnimes[] = $newAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $topForms[$newAnime->getId()] = $form2->createView();
                } else {
                    $topAnimes[] = $existingAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $topForms[$existingAnime->getId()] = $form2->createView();
                }
            }
            
        } else {
            // modifier ici pour faire la requete direct dans ma base de données
            echo 'requete echoue';
        }

        if ($popularResponse->getStatusCode() === 200) {
            $content = $popularResponse->getBody()->getContents();
            $result = json_decode($content, true);
            
            $animeData = $result['data']['Page']['media'];
            //var_dump($animeData);
            foreach ($animeData as $anime) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                
                $title = $anime['title']['romaji'];
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $startYear = $anime['startDate']['year'];
                    $endYear = $anime['endDate']['year'];
                    $episodes = $anime['episodes'];
                    $genres = null;
                    $format = null;
                    $status = $anime['status'];
                    $trailer = $anime['trailer'];
                    if ($anime['genres'] != null) {
                        $genres = $anime['genres'];
                    }
                    if ($anime['format'] != null) {
                        $format = $anime['format'];
                    }

                    $newAnime = new Anime();
                    $newAnime->setNom($title);
                    $newAnime->setImage($image);
                    $newAnime->setMalId($malId);
                    $newAnime->setSynopsis($synopsis);
                    $newAnime->setYear($startYear);
                    $newAnime->setEpisodes($episodes);
                    
                    if ($trailer != null) {
                        $trailerImg = $trailer['thumbnail'];
                        $trailerSite = $trailer['site'];
                        $trailerLink = $trailer['id'];
                        $trailerUrl = '';
                        if ($trailerSite === 'youtube') {
                            $trailerUrl = 'https://www.youtube.com/watch?v=' . $trailerLink;
                        } else {
                            $trailerUrl = 'https://www.dailymotion.com/video/' . $trailerLink;
                        }
                        $newAnime->setTrailerImg($trailerImg);
                        $newAnime->setTrailerUrl($trailerUrl);
                    }
                    
                    if ( $genres != null) {
                        for ($i = 0; $i < count($genres); $i++) {
                            // Votre code ici
                            $categorieName = $genres[$i];
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
                    }
                    $entityManager->persist($newAnime);
                    $entityManager->flush();
                    $popularAnimes[] = $newAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $popularForms[$newAnime->getId()] = $form2->createView();
                } else {
                    $popularAnimes[] = $existingAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $popularForms[$existingAnime->getId()] = $form2->createView();
                }
            }
        } else {
            // modifier ici pour faire la requete direct dans ma base de données
            echo 'requete echoue';
        }

        if ($trendingResponse->getStatusCode() === 200) {
            $content = $trendingResponse->getBody()->getContents();
            $result = json_decode($content, true);
            
            $animeData = $result['data']['Page']['media'];
            //var_dump($animeData);
            foreach ($animeData as $anime) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                
                $title = $anime['title']['romaji'];
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $startYear = $anime['startDate']['year'];
                    $endYear = $anime['endDate']['year'];
                    $episodes = $anime['episodes'];
                    $genres = null;
                    $format = null;
                    $status = $anime['status'];
                    $trailer = $anime['trailer'];
                    if ($anime['genres'] != null) {
                        $genres = $anime['genres'];
                    }
                    if ($anime['format'] != null) {
                        $format = $anime['format'];
                    }

                    $newAnime = new Anime();
                    $newAnime->setNom($title);
                    $newAnime->setImage($image);
                    $newAnime->setMalId($malId);
                    $newAnime->setSynopsis($synopsis);
                    $newAnime->setYear($startYear);
                    $newAnime->setEpisodes($episodes);
                    
                    if ($trailer != null) {
                        $trailerImg = $trailer['thumbnail'];
                        $trailerSite = $trailer['site'];
                        $trailerLink = $trailer['id'];
                        $trailerUrl = '';
                        if ($trailerSite === 'youtube') {
                            $trailerUrl = 'https://www.youtube.com/watch?v=' . $trailerLink;
                        } else {
                            $trailerUrl = 'https://www.dailymotion.com/video/' . $trailerLink;
                        }
                        $newAnime->setTrailerImg($trailerImg);
                        $newAnime->setTrailerUrl($trailerUrl);
                    }
                    
                    if ( $genres != null) {
                        for ($i = 0; $i < count($genres); $i++) {
                            // Votre code ici
                            $categorieName = $genres[$i];
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
                    }
                    $entityManager->persist($newAnime);
                    $entityManager->flush();
                    $trendingAnimes[] = $newAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $trendingForms[$newAnime->getId()] = $form2->createView();
                } else {
                    $trendingAnimes[] = $existingAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $trendingForms[$existingAnime->getId()] = $form2->createView();
                }
            }
        } else {
            // modifier ici pour faire la requete direct dans ma base de données
            echo 'requete echoue';
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
        return $this->render('home/index.html.twig', [
            'searchForm' => $form,
            'topAnimes' => $topAnimes,
            'topForms' => $topForms,
            'popularAnimes' => $popularAnimes,
            'popularForms' => $popularForms,
            'trendingAnimes' => $trendingAnimes,
            'trendingForms' => $trendingForms,
            'lists' => $listsArray
        ]);
    }
}
