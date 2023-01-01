<?php

namespace App\Repository;
//use Doctrine\ORM\EntityRepository;
use App\Entity\Centrescia;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
* @method Centrescia|null find($id, $lockMode = null, $lockVersion = null)
* @method Centrescia|null findOneBy(array $criteria, array $orderBy = null)
* @method Centrescia[]    findAll()
* @method Centrescia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    */
class CentresciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Centrescia::class);
    }


    public function getCentres(CentresciaRepository $cr): QueryBuilder
    {

        return $cr->createQueryBuilder('c')->select('c');
        //->where('e.lettre = :lettre')
        //->setParameter('lettre',$lettre);
    }


}