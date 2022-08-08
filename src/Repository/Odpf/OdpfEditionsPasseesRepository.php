<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfEditionsPassees;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfEditionsPassees|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfEditionsPassees|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfEditionsPassees[]    findAll()
 * @method OdpfEditionsPassees[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfEditionsPasseesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfEditionsPassees::class);
    }

    // /**
    //  * @return OdpfEditionsPassees[] Returns an array of OdpfEditionsPassees objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OdpfEditionsPassees
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
