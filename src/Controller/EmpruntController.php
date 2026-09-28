<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\EmpruntRepository;
use App\Repository\MembreRepository;
use App\Service\EmpruntService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EmpruntController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/emprunts', name: 'app_admin_emprunts', methods: ['GET'])]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        return $this->render('emprunt/index.html.twig', [
            'titre' => 'Tous les emprunts',
            'emprunts' => $empruntRepository->findBy([], ['dateEmprunt' => 'DESC']),
        ]);
    }

    #[Route('/mes-emprunts', name: 'app_mes_emprunts', methods: ['GET'])]
    public function mesEmprunts(EmpruntRepository $empruntRepository): Response
    {
        return $this->render('emprunt/index.html.twig', [
            'titre' => 'Mes emprunts',
            'emprunts' => $empruntRepository->findBy(['membre' => $this->getUser()], ['dateEmprunt' => 'DESC']),
        ]);
    }

    #[Route('/livre/{id}/emprunter', name: 'app_livre_emprunter', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function emprunter(Livre $livre, Request $request, MembreRepository $membreRepository, EmpruntService $empruntService): Response
    {
        $membre = null;

        // Seul l'admin peut choisir un autre membre
        if ($this->isGranted('ROLE_ADMIN') && $request->request->get('membre')) {
            $membre = $membreRepository->find($request->request->get('membre'));
        }

        try {
            $emprunt = $empruntService->emprunter($livre, $membre);
            $this->addFlash('success', $livre->getTitre() . ' emprunté par ' . $emprunt->getMembre()->getNom() . '.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectBack($request, $livre);
    }

    #[Route('/livre/{id}/rendre', name: 'app_livre_rendre', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function rendre(Livre $livre, Request $request, EmpruntService $empruntService): Response
    {
        try {
            $empruntService->rendre($livre);
            $this->addFlash('success', $livre->getTitre() . ' a été rendu.');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectBack($request, $livre);
    }

    private function redirectBack(Request $request, Livre $livre): Response
    {
        $referer = $request->headers->get('referer');

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('app_livre_show', ['id' => $livre->getId()]);
    }
}