<?php

namespace App\Service;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Membre;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class EmpruntService
{
    public function __construct(
        private EmpruntRepository $empruntRepository,
        private EntityManagerInterface $em,
        private Security $security,
    ) {}

    public function estDisponible(Livre $livre): bool
    {
        return $this->empruntRepository->findEmpruntEnCours($livre) === null;
    }

    public function emprunter(Livre $livre, ?Membre $membre = null): Emprunt
    {
        $utilisateur = $this->getUtilisateur();

        // Sans membre précisé, c'est l'utilisateur connecté qui emprunte
        $membre ??= $utilisateur;

        // Règle : seul l'admin peut emprunter pour quelqu'un d'autre
        if (!$this->estAdmin() && $membre->getId() !== $utilisateur->getId()) {
            throw new \LogicException('Tu ne peux pas emprunter un livre à la place d\'un autre membre.');
        }

        // Règle : un livre déjà emprunté ne peut pas l'être une deuxième fois
        if (!$this->estDisponible($livre)) {
            throw new \LogicException('Ce livre est déjà emprunté.');
        }

        $emprunt = new Emprunt();
        $emprunt->setLivre($livre);
        $emprunt->setMembre($membre);
        $emprunt->setDateEmprunt(new \DateTimeImmutable());

        $this->em->persist($emprunt);
        $this->em->flush();

        return $emprunt;
    }

    public function rendre(Livre $livre): void
    {
        $emprunt = $this->empruntRepository->findEmpruntEnCours($livre);

        if ($emprunt === null) {
            throw new \LogicException('Ce livre n\'est pas emprunté.');
        }

        // Règle : seul l'admin peut rendre l'emprunt d'un autre membre
        if (!$this->estAdmin() && $emprunt->getMembre()->getId() !== $this->getUtilisateur()->getId()) {
            throw new \LogicException('Tu ne peux rendre que tes propres emprunts.');
        }

        $emprunt->setDateRetour(new \DateTimeImmutable());
        $this->em->flush();
    }

    private function getUtilisateur(): Membre
    {
        $user = $this->security->getUser();

        if (!$user instanceof Membre) {
            throw new \LogicException('Tu dois être connecté.');
        }

        return $user;
    }

    private function estAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}