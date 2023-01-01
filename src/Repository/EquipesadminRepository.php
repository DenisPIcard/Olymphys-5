<?php

namespace App\Repository;


use App\Entity\Docequipes;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method Equipesadmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipesadmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipesadmin[]    findAll()
 * @method Equipesadmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipesadminRepository extends ServiceEntityRepository
{
    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, Equipesadmin::class);
        $this->requestStack = $requestStack;

    }


    public function getEquipeInter(EquipesadminRepository $er): QueryBuilder
    {

        return $er->createQueryBuilder('e')
            ->addOrderBy('e.edition', 'DESC')
            ->addOrderBy('e.centre', 'ASC')
            ->addOrderBy('e.numero', 'ASC');


    }

    public function getEquipeDeposeMemoiresInter(EquipesadminRepository $er): QueryBuilder
    {

        return $er->createQueryBuilder('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $er->edition)
            ->addOrderBy('e.centre', 'ASC')
            ->addOrderBy('e.numero', 'ASC');


    }

    public function getEquipeNa(EquipesadminRepository $er): QueryBuilder
    {

        return $er->createQueryBuilder('e')->select('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $er->edition)
            ->andwhere('e.selectionnee= TRUE')
            ->orderBy('e.lettre', 'ASC');


    }

    public function getEquipesNatSansMemoire(EquipesadminRepository $er): QueryBuilder
    {
        return $er->createQueryBuilder('e')->select('e')
            ->where('e.selectionnee= TRUE')
            ->orderBy('e.lettre', 'ASC');


    }

    public function getEleves(Equipesadmin $equipe): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Elevesinter e
            WHERE e.equipe =:equipe
            ORDER BY e.nom ASC'
        )->setParameter('equipe', $equipe);

        // returns an array of Product objects
        return $query->getResult();


    }

    public function getEquipes_prof_cn(User $prof, Edition $edition): array
    {
        $entityManager = $this->getEntityManager();


        $query = $entityManager->createQuery(
            'SELECT e
            FROM App\Entity\Equipesadmin e 
            WHERE (e.idProf1 =:prof1 OR e.idProf2 =:prof2) AND e.selectionnee = TRUE AND e.edition =:edition
            ORDER BY e.lettre ASC')
            ->setParameter('prof1', $prof)
            ->setParameter('prof2', $prof)
            ->setParameter('edition', $edition);
        return $query->execute();


    }

    public function getNumeros(): string
    {//donne la liste des N° des équipes du professeur de l'édition listée

        $em = $this->getEntityManager();

        $qb = $em->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.idProf1 =: prof1 or e.idprof2 =:prof')
            ->andWhere('e.edition =:edition')
            ->setParameters(['edition' => $this->edition, 'prof1' => $this->idProf1, 'prof2' => $this->idProf2]);
        $listeEquipes = $qb->getQuery()->getResult();
        $numero = $this->getNumero();
        foreach ($listeEquipes as $equipe) {
            if ($equipe != $this)
                $numeros = $numeros . '-' . $equipe->getNumero();

        }
        return $numeros;
    }

}