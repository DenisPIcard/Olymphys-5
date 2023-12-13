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
        $coef = $this->doctrine->getRepository(Coefficients::class)->find(1);
        $listEquipes = $repositoryEquipes->findBy(['edition' => $this->requestStack->getSession()->get('edition'), 'centre' => $centre]);
        $points = [];//ce sont les totaux de chaque équipe

        foreach ($listEquipes as $equipe) {
            $listesNotes = $repositoryNotes->getNotess($equipe);//Pour chauque équipe on relève les notes de chaque juré
            $nbre_notes = count($listesNotes);//a la place de $equipe->getNbNotes();
            $points[$equipe->getId()] = 0;//On initialise à 0 le total dans le tableau points[id équipe]
            $nb_notes_ecrit = 0;//On initialise à 0 le nb de notes
            $total_ecrit = 0;//ON initialise à 0 les points de l'écrit
            foreach ($listesNotes as $note) {// Pour chaque note(une note correspond à un juré)
                $points[$equipe->getId()] = $points[$equipe->getId()] + $note->getPoints();//on additionne les totaux sans écrits de chaque juré les uns aux autres
                if ($note->getEcrit() != null) {// si la note traité contient l'écrit
                    $nb_notes_ecrit = $nb_notes_ecrit + 1;//On incrémente de 1 le nombre de note d'écrit
                    $total_ecrit = $total_ecrit + ($note->getEcrit() * $coef->getEcrit());//on ajoute les points de l'écrit les uns aux autres
                }

            }
            if ($nbre_notes != 0) {// Si l'équipe à été notée
                if ($nb_notes_ecrit != 0) {// Si l'écrit à été évalué dans la série de note traitée
                    $points[$equipe->getId()] = intval($points[$equipe->getId()] / $nbre_notes + ($total_ecrit / $nb_notes_ecrit));//total d'une équipe = moyenne des totaux sans écrit+ moyenne des notes d'écrit
                } else {
                    $points[$equipe->getId()] = intval($points[$equipe->getId()] / $nbre_notes);// moyenne des tautaux sans écrit
                }
            } else {
                $points[$equipe->getId()] = 0;// total=0 si pas de note
            }

        }
        arsort($points);// On classe les totaux par ordre décroissant
        $i = 1;//$i est le rang d'une équipe donc 1 pour l'équipe de total le plus grand
        foreach ($points as $point) {//On met à jour la table rangcia
            $idEquipe = key($points);//La clef du tableau points est l'id de l'équipe
            $equipe = $this->doctrine->getRepository(Equipesadmin::class)->findOneBy(['id' => $idEquipe]);//On récupère l'équipe
            $rangEquipe = $this->doctrine->getRepository(RangsCia::class)->findOneBy(['equipe' => $equipe]);//On récupère le rang précédent de l'équipe
            if ($rangEquipe == null) {//Si le rang d'une équipe n'est pas encore créé
                $rangEquipe = new RangsCia();
                $rangEquipe->setEquipe($equipe);
            }
            $rangEquipe->setRang($i);//On enregistre le rang de l'équipe
            $rangEquipe->setPoints($point);//On enregistre le total correspondant qui s'affiche dans le tableau classement des équuipes pour la délibération
            $this->doctrine->getManager()->persist($rangEquipe);
            $this->doctrine->getManager()->flush();
            next($points);
            $i = $i + 1;
        }

        return $points;

    }

    public function classementSousJury($equipes)//Classement partiel des équipes selon les équipes vues par un juré
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
