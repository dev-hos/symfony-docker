<?php

namespace App\Controller\Admin;

use App\Entity\Keywords;
use App\Form\KeywordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/keywords', name: 'admin_keywords_')]
class KeywordsController extends AbstractController
{
    public function __construct(private readonly SluggerInterface $slugger)
    {}

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/keywords/index.html.twig', [
            'controller_name' => 'KeywordsController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addKeywords(Request $request, EntityManagerInterface $em): Response
    {
        $keyword = new Keywords();
        $form = $this->createForm(KeywordFormType::class, $keyword);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $keyword->setSlug(strtolower($this->slugger->slug($keyword->getName())));
            $em->persist($keyword);
            $em->flush();

            $this->addFlash('success', 'La clé a été ajoutée avec succès');
            return $this->redirectToRoute('admin_keywords_index');
        }
        return $this->render('admin/keywords/add.html.twig', [
            'form' => $form
        ]);
    }
}
