<?php

namespace App\Repository;

use App\Entity\Emprunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Emprunt>
 */
class EmpruntRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emprunt::class);
    }

public function findEmpruntEnCours(\App\Entity\Livre $livre): ?\App\Entity\Emprunt
{
    return $this->createQueryBuilder('e')
        ->andWhere('e.livre = :livre')
        ->andWhere('e.dateRetour IS NULL')
        ->setParameter('livre', $livre)
        ->getQuery()
        ->getOneOrNullResult();
}
//    public function findOneBySomeField($value): ?Emprunt
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
