<?php


namespace App\Repository\Cia;


use App\Entity\Cia\NotesCia;
use App\Entity\Coefficients;
use App\Entity\Notes;
use App\Repository\CoefficientsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @method Notes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notes[]    findAll()
 * @method Notes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotesCiaRepository extends EntityRepository
{
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        $this->doctrine = $em;
        parent::__construct($em, $class);
    }

    public function get_rangs($jure_id): array
    {


        $queryBuilder = $this->createQueryBuilder('n');  // n est un alias, un raccourci donné à l'entité du repository. 1ère lettre du nom de l'entité
        $repo = $this->doctrine->getRepository(Coefficients::class);
        $coefficients = $repo->findOneBy(['id' => 1]);
        // On ajoute des critères de tri, etc.

        $queryBuilder
            ->where('n.jure=:jure_id')
            ->setParameter('jure_id', $jure_id)
            ->orderBy('n.exper*' . $coefficients->getExper() .
                '+ n.demarche*' . $coefficients->getDemarche() . ' + n.oral*' . $coefficients->getOral() .
                ' + n.origin*' . $coefficients->getOrigin() . ' + n.wgroupe*' . $coefficients->getWgroupe() .
                '+n.repquestions*' . $coefficients->getRepquestions(), 'DESC');

        // on récupère la query
        $query = $queryBuilder->getQuery();

        // getResult() exécute la requête et retourne un tableau contenant les résultats sous forme d'objets.
        // Utiliser getArrayResult en cas d'affichage simple : le résultat est sous forme de tableau : plus rapide que getResult()
        $results = $query->getResult();
        $i = 1;
        $rangs = [];
        foreach ($results as $result) {
            $id = $result->getEquipe()->getId();
            $rangs[$id] = $i;
            $i = $i + 1;
        }
        return $rangs;
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

    public function getNotess($equipe)
    {

        $notes = $this->createQueryBuilder('n')
            ->where('n.equipe =:equipe')
            ->setParameter('equipe', $equipe)
            ->getQuery()->getResult();

        return $notes;


    }

}


