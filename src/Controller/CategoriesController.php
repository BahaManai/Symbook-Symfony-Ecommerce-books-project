<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategorieType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoriesController extends AbstractController
{
    #[Route('/admin/categories', name: 'admin_categories')]
    public function index(CategoriesRepository $rep): Response
    {
        $categories = $rep->findAll();
        return $this->render('Admin/categories/index.html.twig', [
            'Categories' => $categories,
        ]);
    }

    #[Route('/admin/categories/create', name: 'admin_categories_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categories();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Une catégorie a été ajoutée avec succès');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('Admin/categories/create.html.twig', [
            'f' => $form,
            'mode' => 'create'
        ]);
    }

    #[Route('/admin/categories/update/{id}', name: 'admin_categories_update')]
    public function update(Request $request, EntityManagerInterface $em, Categories $categorie): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Une catégorie a été modifiée avec succès');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('Admin/categories/create.html.twig', [
            'f' => $form,
            'mode' => 'edit'
        ]);
    }

    #[Route('/admin/categories/delete/{id}', name: 'admin_categories_delete')]
    public function delete(EntityManagerInterface $em, Categories $categorie): Response
    {
        $em->remove($categorie);
        $em->flush();
        $this->addFlash('danger', 'Catégorie supprimée avec succès');
        return $this->redirectToRoute('admin_categories');
    }
}