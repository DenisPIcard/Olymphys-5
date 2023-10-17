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
            }
        return $statut;
    }

    public function getEquipesSousJury($centrecia, $numSousJury)
    {
        $listJures = $this->findBy(['centrecia' => $centrecia, 'numJury' => $numSousJury]);

        $equipes = [];
        $i = 0;
        foreach ($listJures as $jure) {
            $equipesjure = $jure->getEquipes();

            if ($equipes == []) {
                foreach ($equipesjure as $equipejure) {
                    $equipes[$i] = $equipejure;
                    $i = $i + 1;
                }
            }
            if ($equipes != []) {
                foreach ($equipesjure as $equipejure) {
                    $test = false;
                    foreach ($equipes as $equipe) {
                        if ($equipe == $equipejure) {
                            $test = true;
                        }
                    }
                    if ($test == false) {
                        $equipes[$i] = $equipejure;
                        $i = $i + 1;
                    }
                }
            }
        }
        return $equipes;

    }
}
