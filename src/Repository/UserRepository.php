<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $edition;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);

    }

    public function getProfautorisation(UserRepository $er): QueryBuilder//Liste des prof sans autorisation photos
    {
        $roles = ['ROLE_PROF', 'ROLE_USER'];


        $qb = $er->createQueryBuilder('p');
        $qb1 = $er->createQueryBuilder('u')
            ->Where('u.autorisationphotos is null')
            ->andWhere($qb->expr()->like('u.roles', ':roles'))
            ->setParameter('roles', 'a:2:{i:0;s:9:"ROLE_PROF";i:1;s:9:"ROLE_USER";}')
            ->orWhere($qb->expr()->like('u.roles', ':roles'))
            ->setParameter('roles', '%i:0;s:9:"ROLE_PROF";i:2;s:9:"ROLE_USER";%')
            ->addOrderBy('u.nom', 'ASC');

        return $qb1;
    }

    public function getProfesseur(UserRepository $er): QueryBuilder//Liste des profs
    {

        $qb = $er->createQueryBuilder('p');
        $qb1 = $er->createQueryBuilder('u')
            ->andWhere($qb->expr()->like('u.roles', ':roles'))
            ->setParameter('roles', '%i:0;s:9:"ROLE_PROF";i:2;s:9:"ROLE_USER";%')
            ->orWhere($qb->expr()->like('u.roles', ':role'))
            ->setParameter('role', '%a:2:{i:0;s:9:"ROLE_PROF";i:1;s:9:"ROLE_USER";}%')
            ->addOrderBy('u.nom', 'ASC');
        //dd($qb1);
        return $qb1;
    }

    public function getHote(UserRepository $er): QueryBuilder
    {//listes de membres du comité (pour la table equipes selectionnes)

        $qb = $er->createQueryBuilder('p');
        $queryBuilder = $er->createQueryBuilder('h')
            ->select('h')
            ->andWhere($qb->expr()->like('h.roles', ':roles'))
            ->setParameter('roles', 'a:2:{i:0;s:11:"ROLE_COMITE";i:1;s:9:"ROLE_USER";}')
            ->addOrderBy('h.nom', 'ASC');

        return $queryBuilder;
    }

    public function getInterlocuteur(UserRepository $er): QueryBuilder
    {//listes de membres du comité (pour la table equipes selectionnes)

        $qb = $er->createQueryBuilder('p');
        $queryBuilder = $er->createQueryBuilder('h')
            ->select('h')
            ->andWhere($qb->expr()->like('h.roles', ':roles'))
            ->setParameter('roles', 'a:2:{i:0;s:9:"ROLE_JURY";i:1;s:9:"ROLE_USER";}')
            ->addOrderBy('h.nom', 'ASC');

        return $queryBuilder;

    }

    public function getEquipes(UserRepository $er): QueryBuilder
    {


    }
}
