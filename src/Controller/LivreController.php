<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class LivreController extends AbstractController
{
    #[Route('/selections', name: 'selection_livre', methods: ['GET'])]
    public function selection(Request $request, LivreRepository $livreRepository): Response
    {
        $recherche = trim((string) $request->query->get('q', ''));

        return $this->render('selection.html.twig', [
            'livres' => $recherche !== ''
                ? $livreRepository->searchByTitre($recherche)
                : $livreRepository->findAll(),
            'recherche' => $recherche,
        ]);
    }

    #[Route('/livre', name: 'app_livre_index', methods: ['GET'])]
    public function index(LivreRepository $livreRepository): Response
    {
        return $this->render('livre/index.html.twig', [
            'livres' => $livreRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/livre/new', name: 'app_livre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $livre = new Livre();
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $livre->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($livre);
                $entityManager->flush();

                $this->addFlash('success', 'Le livre a bien été créé.');

                return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('error', 'Le formulaire contient des erreurs.');
        }

        return $this->render('livre/new.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[Route('/livre/{id}', name: 'app_livre_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Livre $livre, EmpruntRepository $empruntRepository, MembreRepository $membreRepository): Response
    {
        return $this->render('livre/show.html.twig', [
            'livre' => $livre,
            'empruntEnCours' => $empruntRepository->findEmpruntEnCours($livre),
            'membres' => $membreRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/livre/{id}/edit', name: 'app_livre_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Le livre a bien été modifié.');

                return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
            }

            $this->addFlash('error', 'Le formulaire contient des erreurs.');
        }

        return $this->render('livre/edit.html.twig', [
            'livre' => $livre,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/livre/{id}', name: 'app_livre_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Livre $livre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livre->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livre);
            $entityManager->flush();

            $this->addFlash('success', 'Le livre a bien été supprimé.');
        } else {
            $this->addFlash('error', 'Impossible de supprimer ce livre.');
        }

        return $this->redirectToRoute('app_livre_index', [], Response::HTTP_SEE_OTHER);
    }
}