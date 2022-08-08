<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfMemoires;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfMemoires|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfMemoires|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfMemoires[]    findAll()
 * @method OdpfMemoires[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfMemoiresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfMemoires::class);
    }

    // /**
    //  * @return OdpfMemoires[] Returns an array of OdpfMemoires objects
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
    public function findOneBySomeField($value): ?OdpfMemoires
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
