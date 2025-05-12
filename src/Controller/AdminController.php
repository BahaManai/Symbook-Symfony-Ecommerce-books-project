<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livres;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(CommandeRepository $commandeRepository, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $totalLivres = $em->getRepository(Livres::class)->count([]);
        $totalCommandes = $em->getRepository(Commande::class)->count([]);
        $commandesEnAttente = $em->getRepository(Commande::class)->count(['etat' => 'en cours']);
        $topClients = $userRepository->findTopClients();
        $recentCommandes = $commandeRepository->findRecentCommandes();
        $totalRevenu = $em->createQuery(
            'SELECT SUM(lc.quantite * lc.prixUnitaire) FROM App\Entity\LigneCommande lc'
        )->getSingleScalarResult();

        return $this->render('admin/dashbord.html.twig', [
            'topClients' => $topClients,
            'recentCommandes' => $recentCommandes,
            'totalLivres' => $totalLivres,
            'totalCommandes' => $totalCommandes,
            'commandesEnAttente' => $commandesEnAttente,
            'totalRevenu' => $totalRevenu ?? 0,
        ]);
    }
}
