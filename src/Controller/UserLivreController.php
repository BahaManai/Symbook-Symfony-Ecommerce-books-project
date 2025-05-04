<?php

namespace App\Controller;

use App\Entity\Livres;
use App\Repository\LivresRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserLivreController extends AbstractController
{
    #[Route('/user/livre', name: 'app_user_livre')]
    public function all(LivresRepository $livresRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $livres = $livresRepository->findAll();
        $livres = $paginator->paginate($livresRepository->findAll(), $request->query->getInt('page', 1), 12);
        return $this->render('user/livres.html.twig', [
            'livres' => $livres
        ]);
    }

    #[Route('/admin/livres/show/{id}', name: 'app_livres_show')]
    public function show(Livres $livre): Response
        // Param convertor
    {
        return $this->render('/user/detail.html.twig', [
            'livre' => $livre
        ]);
    }
}
