<?php

namespace App\Repository;

use App\Entity\VerouClassement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VerouClassement>
 *
 * @method VerouClassement|null find($id, $lockMode = null, $lockVersion = null)
 * @method VerouClassement|null findOneBy(array $criteria, array $orderBy = null)
 * @method VerouClassement[]    findAll()
 * @method VerouClassement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VerouClassementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VerouClassement::class);
    }

//    /**
//     * @return VerouClassement[] Returns an array of VerouClassement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VerouClassement
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
