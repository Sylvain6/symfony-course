<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LivreController extends AbstractController
{
    #[Route('/selections', name: 'selection_livre')]
    public function selection(LivreRepository $livreRepository): Response
    {
        return $this->render('selection.html.twig', [
            'livres' => $livreRepository->findAll(),
        ]);
    }

    #[Route('/livre/{id}', name: 'livre_show')]
    public function show(Livre $livre): Response
    {
        return $this->render('livre_show.html.twig', [
            'livre' => $livre,
        ]);
    }
}