<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/admin/categories', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{
    public function __construct(private readonly SluggerInterface $slugger)
    {}
    
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'controller_name' => 'CategoriesController',
        ]);
    }

    #[Route('/ajouter', name: 'add')]
    public function addCategories(Request $request, EntityManagerInterface $em): Response
    {
        $Category = new Categories();
        $form = $this->createForm(CategoriesFormType::class, $Category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $Category->setSlug(strtolower($this->slugger->slug($Category->getName())));
            $em->persist($Category);
            $em->flush();

            $this->addFlash('success', 'La catégorie a été ajoutée avec succès');
            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/categories/add.html.twig', [
            'form' => $form,
        ]);
    }
}
