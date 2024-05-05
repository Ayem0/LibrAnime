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
use App\Entity\Format;
use App\Entity\Season;
use App\Entity\Status;

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
                    source
                    duration
                    format
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
        ';
        $variables = [
            "page" => 1,
            "perPage" => 3,
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
            "perPage" => 8,
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
            "perPage" => 25,
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

        $apiQueryPopular = '
        query ($id: Int, $page: Int, $perPage: Int, $isAdult: Boolean, $excludedGenres: [String], $mediaType: MediaType, $sort: [MediaSort], $seasonYear: Int, $season: MediaSeason) {
            Page(page: $page, perPage: $perPage) {
                pageInfo {
                    total
                    currentPage
                    lastPage
                    hasNextPage
                    perPage
                }
                media (id: $id, isAdult: $isAdult, genre_not_in: $excludedGenres, type: $mediaType sort: $sort, seasonYear: $seasonYear, season: $season) {
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
        ';
        $popularThisSeasonVariables = [
            "page" => 1,
            "seasonYear" => 2024,
            "season" => "SPRING",
            "perPage" => 8,
            "isAdult" => false,
            "excludedGenres" => ["Ecchi"],
            "mediaType" => "ANIME",
            "sort" => ["POPULARITY_DESC"],
        ];
        $popularThisSeasonHttp = new Client();
        $popularThisSeasonResponse = $popularThisSeasonHttp->post('https://graphql.anilist.co', [
            'json' => [
                'query' => $apiQueryPopular,
                'variables' => $popularThisSeasonVariables,
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
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    $title = $anime['title']['romaji'];
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $source = $anime['source'];
                    $duration = $anime['duration'];

                    $startDay = $anime['startDate']['day'];
                    $startMonth = $anime['startDate']['month'];
                    $startYear = $anime['startDate']['year'];
                    
                    $startDateString = $startDay . '/' . $startMonth . '/' . $startYear;
                    $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);

                    $endDay = $anime['endDate']['day'];
                    $endMonth = $anime['endDate']['month'];
                    $endYear = $anime['endDate']['year'];
                    
                    $endDateString = $endDay . '/' . $endMonth . '/' . $endYear;
                    $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);

                    $episodes = $anime['episodes'];

                    $popularityScore = $anime['popularity'];
                    $averageScore = $anime['averageScore'];
                    $trendingScore = $anime['trending'];

                    $status = $anime['status'];
                    $format = $anime['format'];
                    $season = $anime['season'];

                    $genres = null;
                    $trailer = $anime['trailer'];
                    if (isset($anime['genres'])) {
                        $genres = $anime['genres'];
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
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    $title = $anime['title']['romaji'];
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $source = $anime['source'];
                    $duration = $anime['duration'];

                    $startDay = $anime['startDate']['day'];
                    $startMonth = $anime['startDate']['month'];
                    $startYear = $anime['startDate']['year'];
                    
                    $startDateString = $startDay . '/' . $startMonth . '/' . $startYear;
                    $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);

                    $endDay = $anime['endDate']['day'];
                    $endMonth = $anime['endDate']['month'];
                    $endYear = $anime['endDate']['year'];
                    
                    $endDateString = $endDay . '/' . $endMonth . '/' . $endYear;
                    $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);

                    $episodes = $anime['episodes'];

                    $popularityScore = $anime['popularity'];
                    $averageScore = $anime['averageScore'];
                    $trendingScore = $anime['trending'];

                    $status = $anime['status'];
                    $format = $anime['format'];
                    $season = $anime['season'];

                    $genres = null;
                    $trailer = $anime['trailer'];
                    if (isset($anime['genres'])) {
                        $genres = $anime['genres'];
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
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    $title = $anime['title']['romaji'];
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $source = $anime['source'];
                    $duration = $anime['duration'];

                    $startDay = $anime['startDate']['day'];
                    $startMonth = $anime['startDate']['month'];
                    $startYear = $anime['startDate']['year'];
                    
                    $startDateString = $startDay . '/' . $startMonth . '/' . $startYear;
                    $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);

                    $endDay = $anime['endDate']['day'];
                    $endMonth = $anime['endDate']['month'];
                    $endYear = $anime['endDate']['year'];
                    
                    $endDateString = $endDay . '/' . $endMonth . '/' . $endYear;
                    $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);

                    $episodes = $anime['episodes'];

                    $popularityScore = $anime['popularity'];
                    $averageScore = $anime['averageScore'];
                    $trendingScore = $anime['trending'];

                    $status = $anime['status'];
                    $format = $anime['format'];
                    $season = $anime['season'];

                    $genres = null;
                    $trailer = $anime['trailer'];
                    if (isset($anime['genres'])) {
                        $genres = $anime['genres'];
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

        if ($popularThisSeasonResponse->getStatusCode() === 200) {
            $content = $popularThisSeasonResponse->getBody()->getContents();
            $result = json_decode($content, true);
            
            $animeData = $result['data']['Page']['media'];
            //var_dump($animeData);
            foreach ($animeData as $anime) {
                // RECUPERE L'ID DE LANIME ET VERIFIE SI DANS LA DB
                $malId = $anime['id'];
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['mal_id' => $malId]);
                if (!$existingAnime) {
                    // SI PAS DANS DB
                    $title = $anime['title']['romaji'];
                    $image = $anime['coverImage']['large'];
                    $synopsis = $anime['description'];
                    $source = $anime['source'];
                    $duration = $anime['duration'];

                    $startDay = $anime['startDate']['day'];
                    $startMonth = $anime['startDate']['month'];
                    $startYear = $anime['startDate']['year'];
                    
                    $startDateString = $startDay . '/' . $startMonth . '/' . $startYear;
                    $startDate = \DateTime::createFromFormat('d/m/Y', $startDateString);

                    $endDay = $anime['endDate']['day'];
                    $endMonth = $anime['endDate']['month'];
                    $endYear = $anime['endDate']['year'];
                    
                    $endDateString = $endDay . '/' . $endMonth . '/' . $endYear;
                    $endDate = \DateTime::createFromFormat('d/m/Y', $endDateString);

                    $episodes = $anime['episodes'];

                    $popularityScore = $anime['popularity'];
                    $averageScore = $anime['averageScore'];
                    $trendingScore = $anime['trending'];

                    $status = $anime['status'];
                    $format = $anime['format'];
                    $season = $anime['season'];

                    $genres = null;
                    $trailer = $anime['trailer'];
                    if (isset($anime['genres'])) {
                        $genres = $anime['genres'];
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
                    $popularThisSeasonAnimes[] = $newAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $popularThisSeasonForms[$newAnime->getId()] = $form2->createView();
                } else {
                    $popularThisSeasonAnimes[] = $existingAnime;
                    // LISTE DES FORMULAIRES
                    $list = new Liste();
                    $form2 = $this->createForm(CreateListFormType::class, $list);
                    $popularThisSeasonForms[$existingAnime->getId()] = $form2->createView();
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

        if (isset($form2)) {
            $form2->handleRequest($request);
            if ($form2->isSubmitted() && $form2->isValid()) {
                $list->setUserId($user);
                $entityManager->persist($list);
                $entityManager->flush();
                
                return $this->redirectToRoute('app_home');
            }
        }
        $season = $entityManager->getRepository(Season::class)->findOneBy(['nom' => 'SPRING']);
        



        return $this->render('home/index.html.twig', [
            'searchForm' => $form,
            'topAnimes' => $topAnimes,
            'topForms' => $topForms,
            'popularAnimes' => $popularAnimes,
            'popularForms' => $popularForms,
            'trendingAnimes' => $trendingAnimes,
            'trendingForms' => $trendingForms,
            'lists' => $listsArray,
            'popularThisSeasonAnimes' => $popularThisSeasonAnimes,
            'popularThisSeasonForms' => $popularThisSeasonForms,
            'season' => $season
        ]);
    }
}
