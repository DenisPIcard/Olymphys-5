<?php

namespace App\Repository;

use App\Entity\Centrescia;
use App\Entity\Videosequipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Videosequipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Videosequipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Videosequipes[]    findAll()
 * @method Videosequipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideosequipesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Videosequipes::class);
    }
}