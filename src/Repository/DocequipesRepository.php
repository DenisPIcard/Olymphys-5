<?php

namespace App\Repository;

use App\Entity\Docequipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Docequipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Docequipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Docequipes[]    findAll()
 * @method Docequipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocequipesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Docequipes::class);
    }

}