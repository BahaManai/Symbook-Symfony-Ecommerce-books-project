<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Livres;
use App\Repository\CommandeRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/admin/stats/top-books', name: 'admin_top_books', methods: ['GET'])]
    public function topSellingBooks(Request $request, LigneCommandeRepository $ligneCommandeRepository): JsonResponse
    {
        $month = $request->query->getInt('month', date('m'));
        $year = $request->query->getInt('year', date('Y'));
        $data = $ligneCommandeRepository->getTopSellingBooksByMonth($month, $year);
        return $this->json($data);
    }

    #[Route('/admin/stats/orders', name: 'admin_orders_stats', methods: ['GET'])]
    public function ordersStats(Request $request, CommandeRepository $commandeRepository): JsonResponse
    {
        $period = $request->query->get('period', 'month');
        $data = $commandeRepository->getOrdersGroupedByPeriod($period);
        return $this->json($data);
    }

    #[Route('/admin/chart/data', name: 'admin_chart_data')]
    public function chartData(Request $request, LigneCommandeRepository $ligneRepo, CommandeRepository $commandeRepo): JsonResponse
    {
        $month = $request->query->getInt('month', date('m'));
        $year = $request->query->getInt('year', date('Y'));
        $period = $request->query->get('period', 'month');

        // Validation des paramètres
        if (!in_array($period, ['day', 'week', 'month'])) {
            $period = 'month';
        }

        $topBooks = $ligneRepo->getTopSellingBooksByMonth($month, $year);
        $ordersData = $commandeRepo->getOrdersOverTime($period);

        return $this->json([
            'topBooks' => $topBooks,
            'ordersData' => $ordersData,
        ]);
    }
}