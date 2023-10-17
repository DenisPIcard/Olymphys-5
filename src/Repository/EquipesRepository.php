<?php


namespace App\Repository;


use App\Entity\Equipes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipes>
 * @method Equipes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Equipes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Equipes[]    findAll()
 * @method Equipes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EquipesRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipes::class);
    }

    public function getEquipe(EquipesRepository $er): QueryBuilder
    {

        return $er->createQueryBuilder('e')->select('e');
        //->where('e.lettre = :lettre')
        //->setParameter('lettre',$lettre);
    }

    public function getEquipes(EquipesRepository $er): QueryBuilder
    {
        return $er->createQueryBuilder('e')
            ->leftJoin('e.equipeinter', 'eq')
            ->where('e.visite IS  NULL')
            ->orderBy('eq.lettre', 'ASC');


    }


    public function getEquipesVisites()
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.visite', 'v')
            ->leftJoin('e.equipeinter', 'eq')
            ->orderBy('eq.lettre')
            ->getQuery();

        return $query->getResult();
    }

    public function getEquipesPrix()
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.prix', 'p')
            ->leftJoin('e.equipeinter', 'eq')
            ->addSelect('p')
            ->orderBy('e.classement')
            ->addSelect('eq')
            ->getQuery();

        return $query->getResult();
    }

    public function getEquipesPhrases()
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.phrases', 'p')
            ->leftJoin('e.equipeinter', 'eq')
            ->addSelect('p')
            ->orderBy('e.classement', 'ASC')
            ->addOrderBy('eq.lettre', 'ASC')
            ->getQuery();

        return $query->getResult();
    }


    public function getEquipesCadeaux()
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.cadeau', 'c')
            ->addSelect('c')
            ->addOrderBy('e.couleur','ASC')
            ->leftJoin('e.equipeinter','eq')
            ->addOrderBy('eq.lettre','ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function getEquipesPalmares()
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.cadeau', 'c')
            ->addSelect('c')
            ->leftJoin('e.phrases', 'f')
            ->addSelect('f')
            ->leftJoin('e.prix', 'p')
            ->addSelect('p')
            ->leftJoin('e.visite', 'v')
            ->addSelect('v')
            ->leftJoin('e.equipeinter', 'i')
            ->addSelect('i')
            ->orderBy('e.classement', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function getEquipesPalmaresJury()
    {
        $query = $this->createQueryBuilder('e')
            ->leftJoin('e.cadeau', 'c')
            ->addSelect('c')
            ->leftJoin('e.phrases', 'f')
            //->leftJoin('e.liaison', 'l')
            ->addSelect('f')
            //->addSelect('l')
            ->leftJoin('e.prix', 'p')
            ->addSelect('p')
            ->leftJoin('e.visite', 'v')
            ->addSelect('v')
            ->leftJoin('e.equipeinter', 'i')
            ->addSelect('i')
            ->orderBy('e.classement', 'DESC', 'e.lettre', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function getEquipesAccueil()
    {
        $query = $this->createQueryBuilder('e')
            ->Join('e.infoequipe', 'i')
            ->addSelect('i')
            ->orderBy('e.lettre')
            ->getQuery();

        return $query->getResult();
    }

    public function miseEnOrdre()
    {
        $query = $this->createQueryBuilder('e')
            ->orderBy('e.ordre', 'ASC')
            ->getQuery();

        return $query->getResult();
    }

    public function classement($niveau, $offset, $nbreprix)
    {

        $queryBuilder = $this->createQueryBuilder('e');

        if ($niveau == 0) {
            $queryBuilder
                ->orderBy('e.total', 'DESC');
        } else {
            $limit = $nbreprix;
            $queryBuilder
                ->select('e')
                ->orderBy('e.total', 'DESC')
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // getResult() exécute la requête et retourne un tableau contenant les résultats sous forme d'objets.
        // Utiliser getArrayResult en cas d'affichage simple : le résultat est sous forme de tableau : plus rapide que getResult()
        $results = $query->getResult();

        // on retourne ces résultats
        return $results;
    }

    public function palmares($niveau, $offset, $nbreprix)
    {

        $queryBuilder = $this->createQueryBuilder('e');  // e est un alias, un raccourci donné à l'entité du repository. 1ère lettre du nom de l'entité

        // On ajoute des critères de tri, etc.

        if ($niveau == 0) {
            $queryBuilder
                ->orderBy('e.rang', 'ASC');
        } else {
            $limit = $nbreprix;
            $queryBuilder
                ->select('e')
                ->orderBy('e.rang', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // getResult() exécute la requête et retourne un tableau contenant les résultats sous forme d'objets.
        // Utiliser getArrayResult en cas d'affichage simple : le résultat est sous forme de tableau : plus rapide que getResult()
        $results = $query->getResult();

        // on retourne ces résultats
        return $results;
    }

    public function MyFindOne($id)
    {
        $queryBuilder = $this->createQueryBuilder('e');  // e est un alias, un raccourci donné à l'entité du repository. 1ère lettre du nom de l'entité

        // On ajoute des critères de tri, etc.
        $queryBuilder
            ->where('e.id=:id')
            ->setParameter('id', $id);

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // on récupère les résultats à partir de la Query
        $results = $query->getResult();

        // on retourne ces résultats
        return $results;
    }

    public function MyFindIdByLettre($lettre)
    {
        $queryBuilder = $this->createQueryBuilder('e');  // e est un alias, un raccourci donné à l'entité du repository. 1ère lettre du nom de l'entité

        // On ajoute des critères de tri, etc.
        $queryBuilder
            ->select('e.id')
            ->where('e.lettre=:lettre')
            ->setParameter('lettre', $lettre);

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // on récupère les résultats à partir de la Query
        $value = $query->getSingleScalarResult();

        // on retourne ces résultats
        return $value;
    }

}