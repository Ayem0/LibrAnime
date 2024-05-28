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
        $userId = $this->getUser();



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

        if ( !$list) {
            return $this->render('list_details/error.html.twig', [
                'searchForm'=> $form,
            ]);
        }

        if ( $list->getUserId() !== $userId) {
            return $this->render('list_details/error.html.twig', [
                'searchForm'=> $form,
            ]);
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
        $userId = $this->getUser();
        $listUserId = $liste->getUserId();

        if ( $userId == $listUserId) {

            $liste->removeAnime($anime);
            $entityManager->flush();

            return $this->redirectToRoute('app_list', ['id' => $liste->getId()]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/list/{liste<\d+>}/add-anime/{anime<\d+>}', name: 'app_add_anime_in_list')]
    public function addAnimeInList(Liste $liste, Anime $anime, Request $request, EntityManagerInterface $entityManager): Response
    {
        $userId = $this->getUser();
        $listUserId = $liste->getUserId();

        if ( $userId == $listUserId) {

            $liste->addAnime($anime);
            $entityManager->persist($liste);
            $entityManager->flush();

            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirectToRoute('app_home');
        }
    }
}
