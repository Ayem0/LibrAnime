<?php

namespace App\Controller;

use App\Data\AnimeListData;
use App\Entity\Anime;
use App\Entity\Liste;
use App\Form\SearchFormType;
use App\Form\FilterAnimeListType;
use App\Repository\AnimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class ListDetailsController extends AbstractController
{
    #[Route('/list/{id}', name: 'app_list')]
    public function index(AnimeRepository $repository,EntityManagerInterface $doctrine, int $id, Request $request): Response
    {
        $data = new AnimeListData();
        $data->page = $request->get('page', 1);
        $filterForm = $this->createForm(FilterAnimeListType::class, $data);
        $filterForm->handleRequest($request);
        $animes = $repository->findAnimesInList($data,$id);
        $list = $doctrine->getRepository(Liste::class)->findOneBy(['id' => $id]);

        // Searchbar
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = strval($form->get('search')->getData());
            if ($form->isSubmitted() && $form->isValid()) {
                $data = strval($form->get('search')->getData());
                return  $this->redirectToRoute('app_result_query', ['query' => $data, 'page' => 1]);
            }
        }
        return $this->render('list_details/index.html.twig', [
            'liste' => $list,
            'animes' => $animes,
            'searchForm'=> $form,
            'filterForm' => $filterForm
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
