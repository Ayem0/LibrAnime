<?php

namespace App\Controller;

use App\Form\CreateListFormType;
use App\Entity\Liste;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class MyListsController extends AbstractController
{
    #[Route('/my-lists', name: 'app_my_lists')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $list = new Liste();
        $form = $this->createForm(CreateListFormType::class, $list);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $list->setUserId($user);
            $entityManager->persist($list);
            $entityManager->flush();
            // do anything else you need here, like send an email
        }

        if ($user ) {
            $lists = $user->getListes();
            $listsArray = [];
            foreach ($lists as $element) {
                $listsArray[] = $element;
            };
            $listsArray = array_reverse($listsArray);
        }

        return $this->render('my_lists/index.html.twig', [
            'listes'=> $listsArray,
            'form'=>$form
        ]);
    }
    
    #[Route('/my-lists/remove/{id}', name: 'app_remove_list')]
    public function removeList(EntityManagerInterface $entityManager, Liste $list): Response
    {
        $user = $this->getUser();
        if ($list->getUserId() === $user) {
            $user->removeListe($list); 
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_my_lists');
    }
}
