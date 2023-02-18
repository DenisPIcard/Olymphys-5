<?php

namespace App\Repository;

use App\Entity\Edition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Edition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Edition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Edition[]    findAll()
 * @method Edition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
