<?php

namespace App\Repository;

use App\Entity\Cadeaux;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
/**
 * @method Cadeaux|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cadeaux|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cadeaux[]    findAll()
 * @method Cadeaux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CadeauxRepository extends ServiceEntityRepository
{

    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Cadeaux::class);

    }


}