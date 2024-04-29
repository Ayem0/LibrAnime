<?php

namespace App\Controller;

use GuzzleHttp\Client;
use App\Entity\Anime;
use App\Entity\Categorie;
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
        $malId = $anime->getMalId();
        
        $apiQuery = '
        query ($id: Int, $perPage: Int, $sort: [RecommendationSort]) {
            Media (id: $id) {
                format
                recommendations (perPage: $perPage, sort: $sort) {
                    nodes {
                        mediaRecommendation  {
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
                relations {
                    nodes {
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
        }
        ';
        $variables = [
            "id" => $malId,
            "perPage" => 25,
            "sort" => ["RATING_DESC"],
        ];
        $http = new Client();
        $response = $http->post('https://graphql.anilist.co', [
            'json' => [
                'query' => $apiQuery,
                'variables' => $variables,
            ]
        ]);

        $recommendationsArray = [];
        $relationsArray = [];


        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            
            $recommendations = $result['data']['Media']['recommendations']['nodes'];
            $relations = $result['data']['Media']['relations']['nodes'];
            //var_dump($recommendations);
            //var_dump($relations);

            foreach ($recommendations as $animeSelected) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                $selected = $animeSelected['mediaRecommendation'];
                if ($selected != null) {
                    $badGenres = ['Hentai', 'Ecchi'];
                    $badFormats = ['MANGA', 'NOVEL', 'ONE_SHOT'];

                    $isValid = true;
                    if (isset($selected['genres'])) {
                        $genres = $selected['genres'];
                        foreach ($genres as $genre) {
                            if (in_array($genre, $badGenres)) {
                                $isValid = false;
                                break;
                            }
                        }
                    }
                    if (isset($selected['format'])) {
                        $format = $selected['format'];
                        if (in_array($format, $badFormats)) {
                            $isValid = false;
                        }
                    }

                    if ($isValid === true) {
                        $malId = $selected['id'];
                        $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                        if (!$existingAnime) {
                            // SI PAS DANS DB
                            $title = $selected['title']['romaji'];
                            $image = $selected['coverImage']['large'];
                            $synopsis = $selected['description'];
                            $startYear = $selected['startDate']['year'];
                            $endYear = $selected['endDate']['year'];
                            $episodes = $selected['episodes'];
                            $status = $selected['status'];
                            $trailer = $selected['trailer'];

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
                            $entityManager->persist($newAnime);
                            $entityManager->flush();

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
                            $recommendationsArray[] = $newAnime;
                        } else {
                            $recommendationsArray[] = $existingAnime;
                        }
                    }              
                }
            }

            foreach ($relations as $relation) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                $badGenres = ['Hentai', 'Ecchi'];
                $badFormats = ['MANGA', 'NOVEL', 'ONE_SHOT'];

                $isValid = true;
                if (isset($relation['genres'])) {
                    $genres = $relation['genres'];
                    foreach ($genres as $genre) {
                        if (in_array($genre, $badGenres)) {
                            $isValid = false;
                            break;
                        }
                    }
                }
                if (isset($relation['format'])) {
                    $format = $relation['format'];
                    if (in_array($format, $badFormats)) {
                        $isValid = false;
                    }
                }

                if ($isValid === true) {
                
                    $malId = $relation['id'];
                    $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                    if (!$existingAnime) {
                        // SI PAS DANS DB
                        $title = $relation['title']['romaji'];
                        $image = $relation['coverImage']['large'];
                        $synopsis = $relation['description'];
                        $startYear = $relation['startDate']['year'];
                        $endYear = $relation['endDate']['year'];
                        $episodes = $relation['episodes'];
                        $status = $relation['status'];
                        $trailer = $relation['trailer'];

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
                        $entityManager->persist($newAnime);
                        $entityManager->flush();

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

                        $relationsArray[] = $newAnime;

                    } else {
                        $relationsArray[] = $existingAnime;
                    }
                }
            }
        }

        $recommendationsForms = [];
        $relationsForms = [];
        foreach($recommendationsArray as $element) {
            $list = new Liste();
            $form2 = $this->createForm(CreateListFormType::class, $list);
            $form2View = $form2->createView();
            $recommendationsForms[$element->getId()] = $form2View;
        }

        foreach($relationsArray as $element) {
            $list = new Liste();
            $form2 = $this->createForm(CreateListFormType::class, $list);
            $form2View = $form2->createView();
            $relationsForms[$element->getId()] = $form2View;
        }

        $categories = $anime->getCategorie();
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
        
        $searchForm = $this->createForm(SearchFormType::class);
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = strval($searchForm->get('search')->getData());
            if ($searchForm->isSubmitted() && $searchForm->isValid()) {
                $data = strval($searchForm->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data, 'page' => 1]);
            }
        }

        return $this->render('anime_details/index.html.twig', [
            'anime' => $anime,
            'categories'=> $categories,
            'searchForm' => $searchForm,
            'listForm' => $listForm->createView(),
            'lists' => $listsArray,
            'relations' => $relationsArray,
            'recommendations' => $recommendationsArray,
            'relationsForms' => $relationsForms,
            'recommendationsForms' => $recommendationsForms,
        ]);
    }
}
