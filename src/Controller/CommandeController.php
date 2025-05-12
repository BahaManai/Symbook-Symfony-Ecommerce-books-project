<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/commandes')]
class CommandeController extends AbstractController
{
    #[Route('', name: 'admin_commande_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();

        return $this->render('Admin/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/{id}/complete', name: 'admin_commande_complete')]
    public function complete(Commande $commande, EntityManagerInterface $em): RedirectResponse
    {
        if ($commande->getEtat() === 'en cours') {
            $commande->setEtat('complétée');
            $em->flush();
            $this->addFlash('success', 'Commande marquée comme complétée.');
        } else {
            $this->addFlash('error', 'Impossible de compléter cette commande.');
        }

        return $this->redirectToRoute('admin_commande_index');
    }
}

