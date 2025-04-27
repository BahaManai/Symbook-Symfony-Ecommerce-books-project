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

final class LivresController extends AbstractController
{
    #[Route('/admin/livres', name: 'app_livres')]
    public function all(LivresRepository $livresRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $livres=$livresRepository->findAll();
        $livres = $paginator->paginate($livresRepository->findAll(), $request->query->getInt('page', 1), 10);
        return $this->render('admin/livres/index.html.twig', [
            'livres' => $livres
        ]);
    }
    /* #[Route('/admin/livres/create', name: 'app_livres_create')]
    public function create(EntityManagerInterface $em): Response
    {
        $livre=new Livres();
        $livre->setTitre('Le Seigneur des Anneaux');
        $livre->setIsbn('978-2266144342');
        $livre->setSlug('le-seigneur-des-anneaux');
        $livre->setImage('images/seigneur-anneaux.jpg');
        $livre->setResume('Une épopée fantastique dans un monde remplié de magie, de héros et de créatures mythiques.');
        $livre->setEditeur('Christian Bourgois');
        $livre->setDateedition(new \DateTime('1954-07-29'));
        $livre->setPrix(19.99);
        $em->persist($livre);
        $em->flush();
        return new Response("Livre ".$livre->getId()." créé avec succès");
    }*/

    #[Route('/admin/livres/create', name: 'app_livres_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $livre = new Livres();
        //afficher le formulaire
        $form = $this->createForm(LivreType::class, $livre);
        // Traitement de données issues
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //dd($categorie);
            $em->persist($livre);
            $em->flush();
            $this->addFlash('success', 'Un livre a été ajouté avec succés');
            return $this->redirectToRoute('app_livres');
        }
        return $this->render('admin/livres/create.html.twig', [
            'f' => $form,
            'mode' => 'create'
        ]);
    }
    #[Route('/admin/livres/show', name: 'app_livres_showAll')]
    public function showAll(LivresRepository $livresRepository): Response
    {
        $livres=$livresRepository->findAll();
        if (!$livres)
        {
            throw $this->createNotFoundException("Aucun livre trouvé");
        }
        dd($livres);
    }
    #[Route('/admin/livres/show/{id}', name: 'app_livres_show')]
    public function show(Livres $livre): Response
    // Param convertor
    {
        return $this->render('admin/livres/detail.html.twig', [
            'livre' => $livre
        ]);
    }
    #[Route('/admin/livres/show2', name: 'app_livres_show2')]
    public function show2(LivresRepository $livresRepository): Response
    {
        $livre=$livresRepository->findOneBy(['titre'=>'Le Seigneur des Anneaux']);
        if (!$livre)
        {
            throw $this->createNotFoundException("Livre non trouvé");
        }
        dd($livre);
    }
    #[Route('/admin/livres/show3', name: 'app_livres_show3')]
    public function show3(LivresRepository $livresRepository): Response
    {
        $livres=$livresRepository->findBy(['titre' => 'Le Seigneur des Anneaux'],
        ['prix' => 'ASC']);
        if (!$livres)
        {
            throw $this->createNotFoundException("Livre non trouvé");
        }
        dd($livres);
    }
    #[Route('/admin/livres/delete/{id}', name: 'app_livres_delete')]
    public function delete(EntityManagerInterface $em, Livres $livre): Response
    {
        $em->remove($livre);
        $em->flush();
        $this->addFlash('danger', 'Livre supprimé avec succés');
        return $this->redirectToRoute('app_livres');
    }
    #[Route('/admin/livres/update/{id}', name: 'app_livres_update')]
    public function update(Request $request, Livres $livre, EntityManagerInterface $em): Response
    {
        // The $livre is automatically loaded by Symfony (ParamConverter)
        // Create the form linked to the existing book
        $form = $this->createForm(LivreType::class, $livre);
        // Handle the form request
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // No need to persist again because $livre is already managed by Doctrine
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