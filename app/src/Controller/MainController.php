<?php

namespace App\Controller;

use App\Repository\PostsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(PostsRepository $postsRepository, UsersRepository $usersRepository): Response
    {
        $lastPost = $postsRepository->findOneBy([], ['id' => 'DESC']);
        $authors = $usersRepository->getUsersByPost(4);
        $posts = $postsRepository->findBy([], ['id' => 'desc'], 3);
        return $this->render('main/index.html.twig', compact('posts', 'authors', 'lastPost'));
    }

    #[Route('/mentions-legales', name: 'app_mentions')]
    public function mentions(): Response
    {
        return $this->render('main/mentions.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
