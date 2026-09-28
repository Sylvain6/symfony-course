<?php

namespace App\DataFixtures;

use App\Entity\Livre;
use App\Entity\Membre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $livre = new Livre();
            $livre->setTitre('Livre n°' . $i);
            $livre->setAuteur('Auteur ' . $i);
            $livre->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($livre);
        }

        foreach (['Alice', 'Bob', 'Chloé'] as $nom) {
            $membre = new Membre();
            $membre->setNom($nom);
            $membre->setEmail(strtolower($nom) . '@test.fr');
            $membre->setPassword($this->hasher->hashPassword($membre, 'password'));

            $manager->persist($membre);
        }
        $admin = new Membre();
        $admin->setNom('Admin');
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin'));
        $manager->persist($admin);

        $manager->flush();
    }
}