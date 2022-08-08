<?php

namespace App\Repository;

use App\Entity\OdpfEquipesPassees;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfEquipesPassees|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfEquipesPassees|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfEquipesPassees[]    findAll()
 * @method OdpfEquipesPassees[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipesPasseesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfEquipesPassees::class);
    }

    // /**
    //  * @return EquipesPassees[] Returns an array of EquipesPassees objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EquipesPassees
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
