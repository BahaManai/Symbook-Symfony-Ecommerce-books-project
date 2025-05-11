<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use Doctrine\ORM\EntityManagerInterface;
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
        $filterBy = $request->query->get('filterBy', 'titre');
        $search = $request->query->get('search', '');

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
                'livres' => $livres,
                'filterBy' => $filterBy,
                'search' => $search

        ]);
    }


    #[Route('/client/livre/show/{id}', name: 'client_livre_show')]
    public function show(Livres $livre): Response

    {
        return $this->render('Client/detail.html.twig', [
            'livre' => $livre
        ]);
    }
    //ajouter au panier
    #[Route('/client/panier/ajouter/{id}', name: 'client_panier_ajouter')]
    public function ajouterAuPanier(Livres $livre, Request $request): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        $panier[$livre->getId()] = ($panier[$livre->getId()] ?? 0) + 1;
        $session->set('panier', $panier);

        $this->addFlash('success', 'Le livre "'.$livre->getTitre().'" a été ajouté au panier');
        return $this->redirectToRoute('client_livres');
    }
     //afficher le panier
    #[Route('/client/panier', name: 'client_panier')]
    public function panier(Request $request, LivresRepository $livresRepository): Response
    {
        $panier = $request->getSession()->get('panier', []);
        $livres = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $livre = $livresRepository->find($id);
            if ($livre) {
                $livres[] = [
                    'livre' => $livre,
                    'quantite' => $quantite,
                    'total' => $livre->getPrix() * $quantite
                ];
                $total += $livre->getPrix() * $quantite;
            }
        }

        return $this->render('Client/lignecommande.html.twig', [
            'items' => $livres,
            'total' => $total
        ]);
    }
    //modifier la quantite
    #[Route('/client/panier/modifier/{id}', name: 'client_panier_modifier')]
    public function modifierPanier(Livres $livre, Request $request): Response
    {
        $quantite = $request->request->getInt('quantite', 1);
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if ($quantite > 0) {
            $panier[$livre->getId()] = $quantite;
        } else {
            unset($panier[$livre->getId()]);
        }

        $session->set('panier', $panier);
        return $this->redirectToRoute('client_panier');
    }
    #[Route('/client/panier/supprimer/{id}', name: 'client_panier_supprimer')]
    public function supprimerDuPanier(Livres $livre, Request $request): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if (isset($panier[$livre->getId()])) {
            unset($panier[$livre->getId()]);
            $session->set('panier', $panier);
            $this->addFlash('success', 'Livre retiré du panier');
        }

        return $this->redirectToRoute('client_panier');
    }
}