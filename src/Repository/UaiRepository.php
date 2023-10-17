<?php

namespace App\Repository;

use App\Entity\Uai;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Uai|null find($id, $lockMode = null, $lockVersion = null)
 * @method Uai|null findOneBy(array $criteria, array $orderBy = null)
 * @method Uai[]    findAll()
 * @method Uai[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UaiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Uai::class);
    }

    // /**
    //  * @return Uai[] Returns an array of Uai objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Uai
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
