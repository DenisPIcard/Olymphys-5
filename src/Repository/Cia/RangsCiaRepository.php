<?php

namespace App\Repository\Cia;

use App\Entity\Centrescia;
use App\Entity\Cia\NotesCia;
use App\Entity\Cia\RangsCia;
use App\Entity\Coefficients;
use App\Entity\Equipesadmin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ServiceEntityRepository<RangsCia>
 *
 * @method RangsCia|null find($id, $lockMode = null, $lockVersion = null)
 * @method RangsCia|null findOneBy(array $criteria, array $orderBy = null)
 * @method RangsCia[]    findAll()
 * @method RangsCia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RangsCiaRepository extends ServiceEntityRepository
{
    private ManagerRegistry $doctrine;
    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        parent::__construct($doctrine, RangsCia::class);
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;

    }

    public function save(RangsCia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RangsCia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function classement($centre)
    {
        // affiche les équipes dans l'ordre de la note brute


        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $repositoryNotes = $this->doctrine->getRepository(NotesCia::class);
        $listEquipes = $repositoryEquipes->findBy(['edition' => $this->requestStack->getSession()->get('edition'), 'centre' => $centre]);
        $points = [];

        foreach ($listEquipes as $equipe) {
            $listesNotes = $repositoryNotes->getNotess($equipe);
            $nbre_notes = count($listesNotes);//a la place de $equipe->getNbNotes();
            $points[$equipe->getId()] = 0;
            $nb_notes_ecrit = 0;
            $total_ecrit = 0;
            foreach ($listesNotes as $note) {
                $points[$equipe->getId()] = $points[$equipe->getId()] + $note->getPoints();
                if ($note->getEcrit() != null) {
                    $nb_notes_ecrit = $nb_notes_ecrit + 1;
                    $total_ecrit = $total_ecrit + $note->getEcrit();
                }
            }
            if ($nbre_notes != 0) {
                if ($nb_notes_ecrit != 0) {
                    $points[$equipe->getId()] = intval($points[$equipe->getId()] / $nbre_notes + ($total_ecrit / $nb_notes_ecrit));
                } else {
                    $points[$equipe->getId()] = intval($points[$equipe->getId()] / $nbre_notes);
                }
            } else {
                $points[$equipe->getId()] = 0;
            }

        }
        arsort($points);
        $i = 1;
        foreach ($points as $point) {
            $idEquipe = key($points);
            $equipe = $this->doctrine->getRepository(Equipesadmin::class)->findOneBy(['id' => $idEquipe]);
            $rangEquipe = $this->doctrine->getRepository(RangsCia::class)->findOneBy(['equipe' => $equipe]);
            if ($rangEquipe == null) {
                $rangEquipe = new RangsCia();
                $rangEquipe->setEquipe($equipe);
            }
            $rangEquipe->setRang($i);
            $rangEquipe->setPoints($point);
            $this->doctrine->getManager()->persist($rangEquipe);
            $this->doctrine->getManager()->flush();
            next($points);
            $i = $i + 1;
        }
        return $points;

    }

    public function classementSousJury($equipes)
    {
        $repositoryNotes = $this->doctrine->getRepository(NotesCia::class);
        $points = [];

        foreach ($equipes as $equipe) {
            $listesNotes = $repositoryNotes->getNotess($equipe);
            $nbre_notes = count($listesNotes);//a la place de $equipe->getNbNotes();
            $points[$equipe->getId()] = 0;
            $nb_notes_ecrit = 0;
            $total_ecrit = 0;
            foreach ($listesNotes as $note) {
                $points[$equipe->getId()] = $points[$equipe->getId()] + $note->getPoints();
                if ($note->getEcrit() != null) {
                    $nb_notes_ecrit = $nb_notes_ecrit + 1;
                    $total_ecrit = $total_ecrit + $note->getEcrit();
                }
            }
            if ($nbre_notes != 0) {
                if ($nb_notes_ecrit != 0) {
                    $points[$equipe->getId()] = intval($points[$equipe->getId()] / $nbre_notes + ($total_ecrit / $nb_notes_ecrit));
                } else {
                    $points[$equipe->getId()] = intval($points[$equipe->getId()] / $nbre_notes);
                }
            } else {
                $points[$equipe->getId()] = 0;
            }

        }
        arsort($points);
        return $points;
    }

}
