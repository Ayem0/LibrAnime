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

class ListDetailsController extends AbstractController
{
    #[Route('/list/{id}', name: 'app_list')]
    public function index(ManagerRegistry $doctrine, int $id): Response
    {
        $list = $doctrine->getRepository(Liste::class)->findOneBy(['id' => $id]);
        $animeInList = $list->getAnime();
        $listAnime = [];
        foreach ($animeInList as $element) {
            $listAnime[] = $element;
        };
        $form2 = $this->createForm(SearchFormType::class);
        return $this->render('list_details/index.html.twig', [
            'liste' => $list,
            'animeInList' => $listAnime,
            'searchForm'=> $form2,
        ]);
    }

    #[Route('/list/{liste<\d+>}/remove-anime/{anime<\d+>}', name: 'app_remove_anime_in_list')]
    public function removeAnimeInList(Liste $liste, Anime $anime, EntityManagerInterface $entityManager, ManagerRegistry $doctrine): Response
    {
        //$list = $doctrine->getRepository(Liste::class)->findOneBy(['id' => $id]);
        $user = $this->getUser();
        if ( $user ) {
            $liste->removeAnime($anime);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_list', ['id' => $liste->getId()]);
    }
}
