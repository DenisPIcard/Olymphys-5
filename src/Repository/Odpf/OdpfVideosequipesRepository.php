<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfVideosequipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OdpfVideosequipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfVideosequipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfVideosequipes[]    findAll()
 * @method OdpfVideosequipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfVideosequipesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OdpfVideosequipes::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(OdpfVideosequipes $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(OdpfVideosequipes $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return OdpfVideosequipes[] Returns an array of OdpfVideosequipes objects
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
    public function findOneBySomeField($value): ?OdpfVideosequipes
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
