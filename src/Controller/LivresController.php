<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Form\LivreType;
use App\Repository\LivresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class LivresController extends AbstractController
{
    #[Route('/admin/livres', name: 'app_livres')]
    public function allbooks(LivresRepository $livresRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $livresRepository->createQueryBuilder('l')
            ->orderBy('l.id', 'DESC')
            ->getQuery();
        $livres = $paginator->paginate($query, $request->query->getInt('page', 1), 10);
        return $this->render('admin/livres/index.html.twig', [
            'livres' => $livres
        ]);
    }

    #[Route('/admin/livres/create', name: 'app_livres_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $livre = new Livres();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('livres_images_directory'),
                        $newFilename
                    );
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : '.$e->getMessage());
                    return $this->render('admin/livres/create.html.twig', [
                        'f' => $form,
                        'mode' => 'create'
                    ]);
                }

                $livre->setImage($newFilename);
            }

            $em->persist($livre);
            $em->flush();
            $this->addFlash('success', 'Un livre a été ajouté avec succès');
            return $this->redirectToRoute('app_livres');
        }

        return $this->render('admin/livres/create.html.twig', [
            'f' => $form,
            'mode' => 'create'
        ]);
    }

    #[Route('/admin/livres/show/{id}', name: 'app_livres_show')]
    public function show(Livres $livre): Response
    {
        return $this->render('Admin/livres/detail.html.twig', [
            'livre' => $livre
        ]);
    }

    #[Route('/admin/livres/delete/{id}', name: 'app_livres_delete')]
    public function delete(EntityManagerInterface $em, Livres $livre): Response
    {
        // Supprimer l'image du disque si elle existe
        if ($livre->getImage()) {
            $imagePath = $this->getParameter('livres_images_directory').'/'.$livre->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $em->remove($livre);
        $em->flush();
        $this->addFlash('danger', 'Livre supprimé avec succès');
        return $this->redirectToRoute('app_livres');
    }

    #[Route('/admin/livres/update/{id}', name: 'app_livres_update')]
    public function update(Request $request, Livres $livre, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                // Supprimer l'ancienne image si elle existe
                if ($livre->getImage()) {
                    $oldImagePath = $this->getParameter('livres_images_directory').'/'.$livre->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('livres_images_directory'),
                        $newFilename
                    );
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : '.$e->getMessage());
                    return $this->render('admin/livres/create.html.twig', [
                        'f' => $form,
                        'mode' => 'edit'
                    ]);
                }

                $livre->setImage($newFilename);
            }

            $em->flush();
            $this->addFlash('success', 'Le livre a été modifié avec succès.');
            return $this->redirectToRoute('app_livres');
        }

        return $this->render('admin/livres/create.html.twig', [
            'f' => $form,
            'mode' => 'edit'
        ]);
    }
}