<?php

namespace App\Repository;

use App\Entity\Coefficients;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Coefficients|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coefficients|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coefficients[]    findAll()
 * @method Coefficients[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoefficientsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Coefficients::class);
    }

}

