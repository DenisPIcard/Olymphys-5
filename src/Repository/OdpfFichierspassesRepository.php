<?php

namespace App\Repository;

use App\Entity\Fichiersequipes;
use App\Entity\Odpf\OdpfFichierspasses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfFichierspasses|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfFichierspasses|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfFichierspasses[]    findAll()
 * @method OdpfFichierspasses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfFichierspassesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfFichierspasses::class);
    }

    /**
     */
    public function add(OdpfFichierspasses $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     */
    public function remove(OdpfFichierspasses $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return OdpfFichierspasses[] Returns an array of OdpfFichierspasses objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OdpfFichierspasses
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
