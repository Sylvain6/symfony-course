<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use App\Repository\LivreRepository;
use App\Entity\Livre;

#[Route('/livre', name: 'app_livre_')]

final class LivreController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function livreList(LivreRepository $livreRepository): Response
    {
        return $this->render('livre/list.html.twig', [
            'livre_list' => $livreRepository->findAll(array()),
        ]);
    }

    #[Route('/{id}', name: 'id', requirements: ['id' => Requirement::DIGITS])]
    public function livreId(Livre $livre): Response
    {  
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }
}
