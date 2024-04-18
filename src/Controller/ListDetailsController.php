<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Entity\Liste;
use App\Form\SearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class ListDetailsController extends AbstractController
{
    #[Route('/list/{id}', name: 'app_list')]
    public function index(ManagerRegistry $doctrine, int $id, Request $request): Response
    {
        $list = $doctrine->getRepository(Liste::class)->findOneBy(['id' => $id]);
        $animeInList = $list->getAnime()->toArray();
        usort($animeInList, function($a, $b) {
            $nomA = strtolower($a->getNom());
            $nomB = strtolower($b->getNom());
            return strcmp($nomA, $nomB);
        });
        $listAnime = [];
        foreach ($animeInList as $element) {
            $listAnime[] = $element;
        };
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data . '&page=1&nsw=true']);
            }
        }
        return $this->render('list_details/index.html.twig', [
            'liste' => $list,
            'animeInList' => $listAnime,
            'searchForm'=> $form,
        ]);
    }

    #[Route('/list/{liste<\d+>}/remove-anime/{anime<\d+>}', name: 'app_remove_anime_in_list')]
    public function removeAnimeInList(Liste $liste, Anime $anime, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ( $user ) {
            $liste->removeAnime($anime);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_list', ['id' => $liste->getId()]);
    }
}
