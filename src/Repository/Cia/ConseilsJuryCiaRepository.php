<?php

namespace App\Repository\Cia;

use App\Entity\Cia\ConseilsJuryCia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConseilsJuryCia>
 *
 * @method ConseilsJuryCia|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConseilsJuryCia|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConseilsJuryCia[]    findAll()
 * @method ConseilsJuryCia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConseilsJuryCiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConseilsJuryCia::class);
    }

    public function save(ConseilsJuryCia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConseilsJuryCia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ConseilsJuryCia[] Returns an array of ConseilsJuryCia objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ConseilsJuryCia
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
