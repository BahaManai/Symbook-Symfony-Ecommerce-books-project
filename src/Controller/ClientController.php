<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\EmailService;
use App\Entity\Livres;
use App\Repository\LivresRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ClientController extends AbstractController
{
    #[Route('/client/livres', name: 'client_livres')]
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

        $this->addFlash('success', 'Le livre "' . $livre->getTitre() . '" a été ajouté au panier');
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
   //fonction pour choisir le mode de paiement
    #[Route('/client/checkout', name: 'client_checkout', methods: ['GET'])]
    public function checkout(Request $request): Response
    {
        return $this->render('client/checkout.html.twig');
    }

    #[Route('/client/checkout', name: 'client_checkout_submit', methods: ['GET', 'POST'])]
    public function submitCheckout(
        Request                $request,
        LivresRepository       $livresRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response
    {
        $panier = $request->getSession()->get('panier', []);
        $paymentMethod = $request->request->get('payment_method');

        $commande = new Commande();
        $commande->setNumero('CMD-' . uniqid());
        $commande->setDateCommande(new \DateTime());
        $commande->setEtat('en cours');
        $commande->setUser($this->getUser());

        foreach ($panier as $id => $quantite) {
            $livre = $livresRepository->find($id);
            if ($livre) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setLivre($livre)
                    ->setQuantite($quantite)
                    ->setPrixUnitaire($livre->getPrix());
                $commande->addLignesCommande($ligneCommande);
                $entityManager->persist($ligneCommande);
            }
        }

        if ($paymentMethod === 'cash') {
            $commande->setModePaiement('Cash');
            $commande->setEtatPaiement(true);

            $entityManager->persist($commande);
            $entityManager->flush();
            $request->getSession()->remove('panier');
            $emailService->sendCommandeConfirmation($commande);
            $this->addFlash('success', 'Commande confirmée ! Un email récapitulatif vous a été envoyé.');
            return $this->redirectToRoute('client_livres');

        } else {
            $commande->setModePaiement('Stripe');
            $commande->setEtatPaiement(true);

            $entityManager->persist($commande);
            $entityManager->flush();

            \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

            $lineItems = [];
            foreach ($panier as $id => $quantite) {
                $livre = $livresRepository->find($id);
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'EUR',
                        'product_data' => ['name' => $livre->getTitre()],
                        'unit_amount' => (int) round($livre->getPrix() * 100),
                    ],
                    'quantity' => $quantite,
                ];
            }
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('client_checkout_success', [
                    'commande_id' => $commande->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('client_panier', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return $this->redirect($session->url);
        }
    }
    #[Route('/client/checkout/success', name: 'client_checkout_success')]
    public function checkoutSuccess(
        Request $request,
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        EmailService $emailService
    ): Response
    {
        if ($request->query->has('commande_id')) {
            $commande = $commandeRepository->find($request->query->get('commande_id'));

            if ($commande && $commande->getModePaiement() === 'Stripe') {
                $commande->setEtatPaiement(true);
                $entityManager->flush();
                $request->getSession()->remove('panier');

                // Envoi de l'email de confirmation
                $emailService->sendCommandeConfirmation($commande);

                $this->addFlash('success', 'Paiement confirmé ! Un email récapitulatif vous a été envoyé.');
            }
        }

        return $this->redirectToRoute('client_livres');
    }

    #[Route('/Clinet/historiqueCommande', name: 'historiqueCommande')]
    public function historiqueCommande(EntityManagerInterface $em): Response {
        $user = $this->getUser();

        $commandes = $em->getRepository(Commande::class)->findBy(
            ['user' => $user],
            ['dateCommande' => 'ASC']
        );

        return $this->render('client/historiqueCommande.html.twig', [
            'commandes' => $commandes
        ]);
    }


}