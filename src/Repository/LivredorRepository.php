<?php

namespace App\Repository;

use App\Entity\Livredor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Livredor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livredor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livredor[]    findAll()
 * @method Livredor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivredorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livredor::class);
    }

    // /**
    //  * @return Livredorprofs[] Returns an array of Livredorprofs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Livredorprofs
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
