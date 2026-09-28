<?php

namespace App\DataFixtures;

use App\Entity\Livre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $livre = new Livre();
            $livre->setTitre('Livre n°' . $i);
            $livre->setAuteur('Auteur ' . $i);
            $livre->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($livre);
        }

        $manager->flush();
    }
}