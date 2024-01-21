<?php

namespace App\Repository;

use App\Entity\RecommandationsJuryCN;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecommandationsJuryCN>
 *
 * @method RecommandationsJuryCN|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecommandationsJuryCN|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecommandationsJuryCN[]    findAll()
 * @method RecommandationsJuryCN[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecommandationsJuryCnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecommandationsJuryCN::class);
    }

//    /**
//     * @return RecommandationsJuryCN[] Returns an array of RecommandationsJuryCN objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RecommandationsJuryCN
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
