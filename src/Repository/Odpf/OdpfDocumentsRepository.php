<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfDocuments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfDocuments|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfDocuments|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfDocuments[]    findAll()
 * @method OdpfDocuments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfDocumentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfDocuments::class);
    }

    // /**
    //  * @return OdpfDocuments[] Returns an array of OdpfDocuments objects
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
    public function findOneBySomeField($value): ?OdpfDocuments
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
