<?php

namespace App\Repository\Odpf;


use App\Entity\Odpf\OdpfImagescarousels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfImagescarousels|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfImagescarousels|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfImagescarousels[]    findAll()
 * @method OdpfImagescarousels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfImagescarouselsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfImagescarousels::class);
    }

    // /**
    //  * @return Imagescarousels[] Returns an array of Imagescarousels objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Imagescarousels
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
