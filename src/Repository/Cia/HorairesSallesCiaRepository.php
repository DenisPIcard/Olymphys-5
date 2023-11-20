<?php

namespace App\Repository\Cia;

use App\Entity\Cia\HorairesSallesCia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HorairesSallesCia>
 *
 * @method HorairesSallesCia|null find($id, $lockMode = null, $lockVersion = null)
 * @method HorairesSallesCia|null findOneBy(array $criteria, array $orderBy = null)
 * @method HorairesSallesCia[]    findAll()
 * @method HorairesSallesCia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HorairesSallesCiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HorairesSallesCia::class);
    }

//    /**
//     * @return HorairesSallesCia[] Returns an array of HorairesSallesCia objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HorairesSallesCia
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
