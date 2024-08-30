<?php

namespace App\Controller\Profile;

use App\Entity\Posts;
use App\Form\PostsFormType;
use App\Repository\UsersRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile/posts', name: 'profile_posts_')]
class PostsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/posts/index.html.twig', [
            'controller_name' => 'PostsController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addPosts(
        Request $request, 
        EntityManagerInterface $em,
        SluggerInterface $slugger, 
        UsersRepository $usersRepository,
        PictureService $pictureService
    ): Response
    {
        $post = new Posts();
        $form = $this->createForm(PostsFormType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug(strtolower($slugger->slug($post->getTitle())));
            $post->setUsers($this->getUser());
            $picture = $form->get('featuredImage')->getData();
            $image = $pictureService->square($picture, 'article', 300);
            $post->setFeaturedImage($image);
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'L\'article été ajouté avec succès');
            return $this->redirectToRoute('profile_posts_index');
        }

        return $this->render('profile/posts/add.html.twig', [
            'form' => $form,
        ]);
    }
}
