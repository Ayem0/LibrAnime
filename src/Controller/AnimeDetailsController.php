<?php

namespace App\Controller;

use GuzzleHttp\Client;
use App\Entity\Anime;
use App\Entity\Categorie;
use App\Entity\Liste;
use App\Entity\Format;
use App\Entity\Season;
use App\Entity\Status;
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
                                day
                                month
                                year

                            }
                            endDate {
                                day
                                month
                                year
                        
                            }
                            episodes
                            status
                            format
                            source 
                            duration
                            season
                            popularity
                            averageScore
                            trending
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
                            day
                            month
                            year
                        }
                        endDate {
                            day
                            month
                            year
                        }
                        episodes
                        status
                        format
                        season
                        source
                        duration
                        popularity
                        averageScore
                        trending
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
        
        
        
        // $relationsArray = $anime->getRecommendedTo();

        
        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            $result = json_decode($content, true);
            
            $recommendations = $result['data']['Media']['recommendations']['nodes'];
            $relations = $result['data']['Media']['relations']['nodes'];
            //var_dump($recommendations);
            //var_dump($relations);

            foreach ($recommendations as $truc) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                $selected = $truc['mediaRecommendation'];
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
                            $source = $selected['source'];
                            $duration = $selected['duration'];
        
                            $startDay = $selected['startDate']['day'];
                            $startMonth = $selected['startDate']['month'];
                            $startYear = $selected['startDate']['year'];
                            
                            $startDateString = $startDay . '/' . $startMonth . '/' . $startYear;
                            $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);
        
                            $endDay = $selected['endDate']['day'];
                            $endMonth = $selected['endDate']['month'];
                            $endYear = $selected['endDate']['year'];
                            
                            $endDateString = $endDay . '/' . $endMonth . '/' . $endYear;
                            $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);
        
                            $episodes = $selected['episodes'];
        
                            $popularityScore = $selected['popularity'];
                            $averageScore = $selected['averageScore'];
                            $trendingScore = $selected['trending'];
        
                            $status = $selected['status'];
                            $format = $selected['format'];
                            $season = $selected['season'];
        
                            $genres = null;
                            $trailer = $selected['trailer'];
                            if (isset($selected['genres'])) {
                                $genres = $selected['genres'];
                            }
        
                            
        
                            $newAnime = new Anime();
                            $newAnime->setNom($title);
                            $newAnime->setImage($image);
                            $newAnime->setMalId($malId);
                            $newAnime->setSynopsis($synopsis);
                            $newAnime->setEpisodes($episodes);
                            $newAnime->setPopularityScore($popularityScore);
                            $newAnime->setAverageScore($averageScore);
                            $newAnime->setTrendingScore($trendingScore);
                            
                            $newAnime->setSource($source);
                            $newAnime->setDuration($duration);
        
                            if ( $endDate != false) {
                                $newAnime->setEndDate($endDate);
                            }
        
                            if ( $startDate != false) {
                                $newAnime->setStartDate($startDate);
                            }
                            
                            if ($format != null) {
                                $formatEntity = $entityManager->getRepository(Format::class)->findOneBy(['nom' => $format]);
                                $newAnime->setFormat($formatEntity);
                            }
                            if ($season != null) {
                                $seasonEntity = $entityManager->getRepository(Season::class)->findOneBy(['nom' => $season]);
                                $newAnime->setSeason($seasonEntity);
                            }
                            if ($status != null) {
                                $statusEntity = $entityManager->getRepository(Status::class)->findOneBy(['nom' => $status]);
                                $newAnime->setstatus($statusEntity);
                            }
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
                                    $newAnime->addCategorie($existingCategorie);
                                }
                            }
                            $entityManager->persist($newAnime);
                            $entityManager->flush();
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
                        $source = $relation['source'];
                        $duration = $relation['duration'];

                        $startDay = $relation['startDate']['day'];
                        $startMonth = $relation['startDate']['month'];
                        $startYear = $relation['startDate']['year'];
                        
                        $startDateString = $startDay . '/' . $startMonth . '/' . $startYear;
                        $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);

                        $endDay = $relation['endDate']['day'];
                        $endMonth = $relation['endDate']['month'];
                        $endYear = $relation['endDate']['year'];
                        
                        $endDateString = $endDay . '/' . $endMonth . '/' . $endYear;
                        $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);

                        $episodes = $relation['episodes'];

                        $popularityScore = $relation['popularity'];
                        $averageScore = $relation['averageScore'];
                        $trendingScore = $relation['trending'];

                        $status = $relation['status'];
                        $format = $relation['format'];
                        $season = $relation['season'];

                        $genres = null;
                        $trailer = $relation['trailer'];
                        if (isset($relation['genres'])) {
                            $genres = $relation['genres'];
                        }

                        

                        $newAnime = new Anime();
                        $newAnime->setNom($title);
                        $newAnime->setImage($image);
                        $newAnime->setMalId($malId);
                        $newAnime->setSynopsis($synopsis);
                        $newAnime->setEpisodes($episodes);
                        $newAnime->setPopularityScore($popularityScore);
                        $newAnime->setAverageScore($averageScore);
                        $newAnime->setTrendingScore($trendingScore);
                        
                        $newAnime->setSource($source);
                        $newAnime->setDuration($duration);

                        if ( $endDate != false) {
                            $newAnime->setEndDate($endDate);
                        }

                        if ( $startDate != false) {
                            $newAnime->setStartDate($startDate);
                        }
                        
                        if ($format != null) {
                            $formatEntity = $entityManager->getRepository(Format::class)->findOneBy(['nom' => $format]);
                            $newAnime->setFormat($formatEntity);
                        }
                        if ($season != null) {
                            $seasonEntity = $entityManager->getRepository(Season::class)->findOneBy(['nom' => $season]);
                            $newAnime->setSeason($seasonEntity);
                        }
                        if ($status != null) {
                            $statusEntity = $entityManager->getRepository(Status::class)->findOneBy(['nom' => $status]);
                            $newAnime->setstatus($statusEntity);
                        }
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
                                $newAnime->addCategorie($existingCategorie);
                            }
                        }
                        $entityManager->persist($newAnime);
                        $entityManager->flush();
                        
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
