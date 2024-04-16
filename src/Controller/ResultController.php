<?php

namespace App\Controller;
use App\Entity\Anime;
use App\Form\SearchFormType;
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
        // Effectuer la requête API avec HttpClient
        $client = HttpClient::create();
        $response = $client->request('GET', "https://api.jikan.moe/v4/anime?q=$query");

        $animesToShow = [];
        $pages = '';

        // Vérifier si la requête a réussi
        if ($response->getStatusCode() === 200) {
            // Récupérer le contenu de la réponse (les données JSON)
            $content = $response->getContent();

            // Convertir les données JSON en tableau associatif
            $result = json_decode($content, true);
            $animeData = $result['data'];
            $pages = $result['pagination']['last_visible_page'];
            foreach ($animeData as $anime) {
                // Récupérer le titre de l'anime depuis les données de l'API
                $title = $anime['title'];
                
                // Rechercher l'anime dans la base de données
                $existingAnime = $entityManager->getRepository(Anime::class)->findOneBy(['nom' => $title]);
            
                // Si l'anime n'existe pas dans la base de données, le créer et l'ajouter à $animesToShow
                if (!$existingAnime) {
                    $newAnime = new Anime();
                    $newAnime->setNom($title);
                    // Vous pouvez également configurer d'autres propriétés de l'entité Anime ici
                    $entityManager->persist($newAnime);
                    $animesToShow[] = $newAnime;
                } else {
                    $animesToShow[] = $existingAnime;
                }
            }
            $entityManager->flush();
        }
        else {
            // Afficher un message d'erreur si la requête a échoué
            echo 'La requête API a échoué.';
        }
        
        // Créer le formulaire de recherche
        $form = $this->createForm(SearchFormType::class);

        return $this->render('result/index.html.twig', [
            'searchForm' => $form->createView(),
            'animes' => $animesToShow,
            'pages' => $pages,
            'query' => $query
        ]);
    }
}
