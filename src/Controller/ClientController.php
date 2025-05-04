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
                'livres' => $livres,
                'filterBy' => $filterBy, // Ajoutez filterBy ici
                'search' => $search     // Ajoutez search ici

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
    #[Route('/client/panier/ajouter/{id}', name: 'client_panier_ajouter')]
    public function ajouterAuPanier(Livres $livre, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Recherche d'une commande "en attente" pour l'utilisateur
        $commande = $em->getRepository(Commande::class)->findOneBy([
            'user' => $user,
            'etat' => 'en cours',
        ]);

        // Création de la commande si elle n'existe pas encore
        if (!$commande) {
            $commande = new Commande();
            $commande->setUser($user);
            $commande->setEtat('en cours');
            $commande->setNumero(uniqid('CMD-')); // Génère un numéro unique
            $commande->setDateCommande(new \DateTime());
            $commande->setModePaiement(""); // Vide pour l’instant
            $commande->setEtatPaiement(false); // Paiement non effectué

            $em->persist($commande);
            $em->flush();
        }

        // Vérifie si le livre est déjà dans le panier
        $ligneExistante = null;
        foreach ($commande->getLignesCommande() as $ligne) {
            if ($ligne->getLivre()->getId() === $livre->getId()) {
                $ligneExistante = $ligne;
                break;
            }
        }

        if ($ligneExistante) {
            $ligneExistante->setQuantite($ligneExistante->getQuantite() + 1);
        } else {
            $ligne = new LigneCommande();
            $ligne->setLivre($livre)
                ->setQuantite(1)
                ->setPrixUnitaire($livre->getPrix())
                ->setCommande($commande);

            $em->persist($ligne);
        }

        $em->flush();

        $this->addFlash('info', 'Livre ajouté au panier.');

        // 🔁 Redirige vers la page précédente (pas vers le panier !)
        return $this->redirectToRoute('client_livres', ['id' => $livre->getId()]);
    }





    #[Route('/client/panier', name: 'client_panier')]
    public function panier(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Cherche une commande en cours pour cet utilisateur
        $commande = $em->getRepository(Commande::class)->findOneBy([
            'user' => $user,
            'etat' => 'en cours',
        ]);

        // Si aucune commande en cours n'est trouvée, on renvoie une commande vide
        if (!$commande) {
            $commande = new Commande();
        }

        // Passer la commande avec ses lignes de commande à la vue
        return $this->render('Client/lignecommande.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Modifier la quantité dans le panier
 #[Route('/client/panier/modifier/{id}', name: 'client_panier_modifier')]
    public function modifierQuantite(Request $request, LigneCommande $ligne, EntityManagerInterface $em): Response
    {
        $quantite = $request->request->getInt('quantite');

        // Si la quantité est 0, supprime la ligne
        if ($quantite < 1) {
            $em->remove($ligne);
            $this->addFlash('success', 'Article retiré du panier.');
        } else {
            $ligne->setQuantite($quantite);
        }

        $em->flush();
        return $this->redirectToRoute('client_panier'); // Redirige vers le panier
    }

    // Supprimer un article du panier
    #[Route('/client/panier/supprimer/{id}', name: 'client_panier_supprimer')]
    public function supprimerLigne(LigneCommande $ligne, EntityManagerInterface $em): Response
    {
        $em->remove($ligne);
        $em->flush();
        $this->addFlash('success', 'Article retiré du panier');
        return $this->redirectToRoute('client_panier');
    }
}
