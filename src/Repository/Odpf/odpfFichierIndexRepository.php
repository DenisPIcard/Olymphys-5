<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfFichierIndex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OdpfFichierIndex>
 *
 * @method odpfFichierIndex|null find($id, $lockMode = null, $lockVersion = null)
 * @method odpfFichierIndex|null findOneBy(array $criteria, array $orderBy = null)
 * @method odpfFichierIndex[]    findAll()
 * @method odpfFichierIndex[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfFichierIndexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfFichierIndex::class);
    }

//    /**
//     * @return odpfFichierIndex[] Returns an array of odpfFichierIndex objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?odpfFichierIndex
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
