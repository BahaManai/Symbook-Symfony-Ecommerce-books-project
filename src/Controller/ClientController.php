<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Livres;
use App\Repository\LivresRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class ClientController extends AbstractController
{#[Route('/client/livres', name: 'client_livres')]
    public function all(Request $request, LivresRepository $livresRepository, PaginatorInterface $paginator): Response
    {
        $filterBy = $request->query->get('filterBy', 'titre');  // Valeur par défaut 'titre'
        $search = $request->query->get('search', '');  // Valeur par défaut vide

        if ($filterBy && $search) {
            $livres = $livresRepository->search($filterBy, $search);
        } else {
            $livres = $livresRepository->findAll();
        }

        $livres = $paginator->paginate(
            $livres,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('Client/livres.html.twig', [
            'livres' => $livres
        ]);
    }


    #[Route('/client/livre/show/{id}', name: 'client_livre_show')]
    public function show(Livres $livre): Response
        // Param convertor
    {
        return $this->render('Client/detail.html.twig', [
            'livre' => $livre
        ]);
    }
}