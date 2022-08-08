<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfEquipesPassees;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfEquipesPassees|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfEquipesPassees|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfEquipesPassees[]    findAll()
 * @method OdpfEquipesPassees[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfEquipesPasseesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfEquipesPassees::class);
    }

    // /**
    //  * @return OdpfEquipesPassees[] Returns an array of OdpfEquipesPassees objects
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
    public function findOneBySomeField($value): ?OdpfEquipesPassees
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
