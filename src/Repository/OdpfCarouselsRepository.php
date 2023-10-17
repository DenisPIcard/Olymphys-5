<?php

namespace App\Repository;

use App\Entity\Odpf\OdpfCarousels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OdpfCarousels>
 *
 * @method OdpfCarousels|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfCarousels|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfCarousels[]    findAll()
 * @method OdpfCarousels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfCarouselsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfCarousels::class);
    }

    public function add(OdpfCarousels $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OdpfCarousels $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return OdpfCarousels[] Returns an array of OdpfCarousels objects
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

//    public function findOneBySomeField($value): ?OdpfCarousels
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
