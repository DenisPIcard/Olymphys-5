<?php

namespace App\Repository;

use App\Entity\Edition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * EditionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below. cope coller
 */
class EditionRepository extends ServiceEntityRepository

{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Edition::class);
    }


    public function getEdition(EditionRepository $er): QueryBuilder
    {

        return $er->createQueryBuilder('e')->select('e');

    }

    public function getLastEdition(EditionRepository $er): QueryBuilder
    {
        $edition = $er->findOneBy([], ['id' => 'desc']);
        $lastid = $edition->getId();
        return $er->createQueryBuilder('e')->select('e')
            ->where('e.id=:lastid')
            ->setParameter('lastid', $lastid);

    }


}
