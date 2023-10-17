<?php

namespace App\Repository;

use App\Entity\Elevesinter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * @method Elevesinter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Elevesinter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Elevesinter[]    findAll()
 * @method Elevesinter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElevesinterRepository extends ServiceEntityRepository
{


    public function __construct(RequestStack $requestStack, ManagerRegistry $registry)
    {

        parent::__construct($registry, Elevesinter::class);
    }



}


