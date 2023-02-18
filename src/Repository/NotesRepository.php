<?php


namespace App\Repository;


use App\Entity\Notes;
use Doctrine\ORM\EntityRepository;

/**
 * @method Notes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notes[]    findAll()
 * @method Notes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotesRepository extends EntityRepository
{
    public function MonClassement($jure_id)
    {
        $queryBuilder = $this->createQueryBuilder('n');  // e est un alias, un raccourci donné à l'entité du repository. 1ère lettre du nom de l'entité

        // On ajoute des critères de tri, etc.
        $queryBuilder
            ->where('n.jure=:jure_id')
            ->setParameter('jure_id', $jure_id)
            ->orderBy('n.exper*10 + n.demarche*10 + n.oral*12.5 + n.origin*12.5 + n.wgroupe*5', 'DESC');

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // getResult() exécute la requête et retourne un tableau contenant les résultats sous forme d'objets.
        // Utiliser getArrayResult en cas d'affichage simple : le résultat est sous forme de tableau : plus rapide que getResult()
        $results = $query->getResult();

        // on retourne ces résultats
        return $results;
    }

    public function EquipeDejaNotee($jure_id, $equipe_id)
    {
        $queryBuilder = $this->createQueryBuilder('n');  // n est un alias, un raccourci donné à l'entité du repository. 1ère lettre du nom de l'entité

        // On ajoute des critères de tri, etc.
        $queryBuilder
            ->where('n.jure=:jure_id')
            ->setParameter('jure_id', $jure_id)
            ->andwhere('n.equipe=:equipe_id')
            ->setParameter('equipe_id', $equipe_id);

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // getResult() exécute la requête et retourne un tableau contenant les résultats sous forme d'objets.
        // Utiliser getArrayResult en cas d'affichage simple : le résultat est sous forme de tableau : plus rapide que getResult()
        $results = $query->getOneOrNullResult();

        // on retourne ces résultats
        return $results;

    }

}


