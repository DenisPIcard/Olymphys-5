<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfCategorie[]    findAll()
 * @method OdpfCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfCategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfCategorie::class);
    }

    // /**
    //  * @return OdpfCategorie[] Returns an array of OdpfCategorie objects
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
    public function findOneBySomeField($value): ?OdpfCategorie
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
