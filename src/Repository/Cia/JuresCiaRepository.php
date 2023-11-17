<?php

namespace App\Repository\Cia;


use App\Entity\Cia\JuresCia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JuresCia>
 *
 * @method JuresCia|null find($id, $lockMode = null, $lockVersion = null)
 * @method JuresCia|null findOneBy(array $criteria, array $orderBy = null)
 * @method JuresCia[]    findAll()
 * @method JuresCia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuresCiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JuresCia::class);
    }

    public function save(JuresCia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(JuresCia $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAttribution($jure, $equipe)
    {
        $statut = null;
        $equipes = $jure->getEquipes();

        foreach ($equipes as $equipejure)
            if ($equipe == $equipejure) {
                if (in_array($equipe->getNumero(), $jure->getRapporteur())) {

                    $statut = 1;
                } else {
                    $statut = 0;
                }
                if (in_array($equipe->getNumero(), $jure->getLecteur())) {
                    $statut = 1;

                } else {
                    $statut = 0;

                }
            }
        return $statut;
    }

    public function getEquipesSousJury($centrecia, $numSousJury)
    {

        $listJures = $this->findBy(['centrecia' => $centrecia, 'numJury' => $numSousJury]);
        $equipes = [];
        $i = 0;
        foreach ($listJures as $jure) {//Pour compter toutes équipes du sous-jury : un jury ne voit pas forcément toutes les équipes du sous-jury
            $equipesjure = $jure->getEquipes();

            if ($equipes == []) {//Pour le premier juré
                foreach ($equipesjure as $equipejure) {
                    $equipes[$i] = $equipejure;
                    $i = $i + 1; //On ajoute ses équipes dans la liste

                }
            }

            if ($equipes != []) {//Pour les jurés suivants, on ajoute les équipes non encore comptablisées
                foreach ($equipesjure as $equipejure) {//On balaye les équipes du juré
                    $test = false;//Initialisation du test à faux
                    foreach ($equipes as $equipe) {//on balaye les équipes déjà dans la liste
                        if ($equipe->getNumero() == $equipejure->getNumero()) {
                            $test = true;//Cette équipe du juré est déjà dans la liste des équipes du sous-jury

                        }
                    }
                    if ($test == false) {//Le balayage précédent n'a pas trouvé l'équipe donc, elle n'est pas dans la liste, on la rajoute
                        $equipes[$i] = $equipejure;//On ajoute l'équipe du juré dans la liste
                        $i = $i + 1;

                    }
                }
            }
        }
        return $equipes;

    }
}
