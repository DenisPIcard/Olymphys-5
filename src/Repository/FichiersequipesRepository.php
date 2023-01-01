<?php

namespace App\Repository;

use App\Entity\Fichiersequipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
* @extends ServiceEntityRepository<Fichiersequipes>
*
* @method Fichiersequipes|null find($id, $lockMode = null, $lockVersion = null)
* @method Fichiersequipes|null findOneBy(array $criteria, array $orderBy = null)
* @method Fichiersequipes[]    findAll()
* @method Fichiersequipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class FichiersequipesRepository extends ServiceEntityRepository
{
    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Fichiersequipes::class);
        $this->requestStack = $requestStack;
    }

    private ManagerRegistry $doctrine;


    public function getEquipesNatSansMemoire(FichiersequipesRepository $er): QueryBuilder
    {  //A reprendre ne donne pas le bon résultat
        return $qb1 = $er->createQueryBuilder('f')->select('f')
            ->leftJoin('f.equipe', 'e')
            ->where('e.selectionnee  =:selectionnee')
            ->setParameter('selectionnee', TRUE)
            ->andWhere('f.typefichier =:memoire')
            ->setParameter('memoire', NULL)
            ->andWhere('f.edition =:edition')
            ->setParameter('edition', $er->requestStack->getSession()->get('edition'))
            ->orWhere('f.typefichier>:type')
            ->setParameter('type', 1)
            ->orderBy('e.lettre', 'ASC');

    }

    public function getEquipesInterSansMemoire(FichiersequipesRepository $er): QueryBuilder
    {  //A reprendre ne donne pas le bon résultat
        $qb1 = $er->createQueryBuilder('f')->select('f')
            ->leftJoin('f.equipe', 'e')
            ->andWhere('f.typefichier =:memoire')
            ->setParameter('memoire', NULL)
            ->andWhere('f.edition =:edition')
            ->setParameter('edition', $er->requestStack->getSession()->get('edition'))
            ->orWhere('f.typefichier>:type')
            ->setParameter('type', 1)
            ->addOrderBy('e.centre', 'ASC')
            ->addOrderBy('e.numero', 'ASC');
        return $qb1;
    }
}