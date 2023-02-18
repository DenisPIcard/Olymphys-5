<?php

namespace App\Repository;

use App\Entity\Odpf\OdpfEditionsPassees;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfEditionsPassees|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfEditionsPassees|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfEditionsPassees[]    findAll()
 * @method OdpfEditionsPassees[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EditionsPasseesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfEditionsPassees::class);
    }

    // /**
    //  * @return EditionsPassees[] Returns an array of EditionsPassees objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EditionsPassees
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
