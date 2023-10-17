<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Repartprix;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Repartprix|null find($id, $lockMode = null, $lockVersion = null)
 * @method Repartprix|null findOneBy(array $criteria, array $orderBy = null)
 * @method Repartprix[]    findAll()
 * @method Repartprix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepartprixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repartprix::class);
    }
}


