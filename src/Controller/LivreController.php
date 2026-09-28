<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Entity\Emprunt;
use App\Form\Livre1Type;
use App\Form\EmpruntType;
use App\Repository\LivreRepository;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livre')]
final class LivreController extends AbstractController
{
    #[Route(name: 'app_livre_index', methods: ['GET'])]
    public function index(LivreRepository $livreRepository): Response
    {
        return $this->render('livre/index.html.twig', [
            'livres' => $livreRepository->findAll(),
            'error' => '',
        ]);
    }

    #[Route('/new', name: 'app_livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $form = $this->createForm(Livre1Type::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($livre);
            $entityManager->flush();

            return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livre/new.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livre_show', methods: ['GET'])]
    public function show(Livre $livre): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Livre1Type::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livre_delete', methods: ['POST'])]
    public function delete(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();
        }

        return $this->render('livre/index.html.twig', [
            'error' => 'Le livre est déjà emprunté.',
        ]);
    }

    #[Route('/{id}/emprunter', name: 'app_livre_emprunter', methods: ['GET'])]
    public function emprunter(Request $request, EntityManagerInterface $entityManager, Livre $livre, EmpruntRepository $empruntRepository, LivreRepository $livreRepository): Response
    {
        $emprunts = $livre->getEmprunts();
        foreach ($emprunts as $emprunt_) {
            if ($emprunt_->getDateRetourPrevu() != null) {
                return $this->render('livre/index.html.twig', [
                    'livres' => $livreRepository->findAll(),
                    'error' => 'Le livre est déjà emprunté.',
                ]);
            }
        }
        $emprunt = new Emprunt();
        $emprunt->setLivre($livre);
        $emprunt->setMembre($this->getUser());
        $emprunt->setDateEmprunt(date_create_immutable('now'));
        $emprunt->setDateRetourPrevu(date_create_immutable('+14 days'));
        $entityManager->persist($emprunt);
        $entityManager->flush();
        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $empruntRepository->findAll(),
        ]);
    }
}
