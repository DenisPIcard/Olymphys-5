<?php

namespace App\Service;

use App\Entity\Elevesinter;
use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Odpf\OdpfFichierspasses;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;


class OdpfRempliEquipesPassees
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;


    }

    public function OdpfRempliEquipePassee($equipe)
    {

        $edition = $equipe->getEdition();
        $repositoryEquipesPassees = $this->doctrine->getRepository(OdpfEquipesPassees::class);
        $repositoryEditionsPassees = $this->doctrine->getRepository(OdpfEditionsPassees::class);
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $editionPassee = $repositoryEditionsPassees->findOneBy(['edition' => $edition->getEd()]);
        $em = $this->doctrine->getManager();
        $OdpfEquipepassee = $repositoryEquipesPassees->createQueryBuilder('e')
            ->where('e.numero =:numero')
            ->andWhere('e.editionspassees= :edition')
            ->setParameters(['numero' => $equipe->getNumero(), 'edition' => $editionPassee])
            ->getQuery()->getOneOrNullResult();

        if ($OdpfEquipepassee === null) {
            $OdpfEquipepassee = new OdpfEquipesPassees();
        }
        $OdpfEquipepassee->setEditionspassees($editionPassee);
        $OdpfEquipepassee->setNumero($equipe->getNumero());
        if ($equipe->getRneId() != null) {
            $equipe->getLettre() !== null ? $OdpfEquipepassee->setLettre($equipe->getLettre()) : $OdpfEquipepassee->setLettre(null);

            $OdpfEquipepassee->setLycee($equipe->getRneId()->getNom());
            $OdpfEquipepassee->setVille($equipe->getRneId()->getCommune());
            $OdpfEquipepassee->setAcademie($equipe->getLyceeAcademie());
            $nomsProfs1 = ucfirst($equipe->getPrenomProf1()) . ' ' . strtoupper($equipe->getNomProf1());
            $equipe->getIdProf2() != null ? $nomsProfs = $nomsProfs1 . ', ' . $equipe->getPrenomProf2() . ' ' . $equipe->getNomProf2() : $nomsProfs = $nomsProfs1;
            $OdpfEquipepassee->setProfs($nomsProfs);
            $listeEleves = $repositoryEleves->findBy(['equipe' => $equipe]);
            $nomsEleves = '';
            foreach ($listeEleves as $eleve) {
                $nomsEleves = $nomsEleves . ucfirst($eleve->getPrenom()) . ' ' . $eleve->getNom() . ', ';
            }
            $OdpfEquipepassee->setEleves($nomsEleves);
        }
        if ($OdpfEquipepassee->getNumero()) {
            $OdpfEquipepassee->setTitreProjet($equipe->getTitreProjet());
            $OdpfEquipepassee->setSelectionnee($equipe->getSelectionnee());
            //$editionPassee->addOdpfEquipesPassee($OdpfEquipepassee);
            $em->persist($OdpfEquipepassee);
            $em->flush();
        }

    }

    public function RempliOdpfFichiersPasses($fichier)
    {

        $em = $this->doctrine->getManager();
        $equipe = $fichier->getEquipe();
        $edition = $fichier->getEdition();
        //dd($equipe,$edition);
        $repositoryOdpfFichierspasses = $this->doctrine->getRepository(OdpfFichierspasses::class);
        $repositoryOdpfEquipesPassees = $this->doctrine->getRepository(OdpfEquipesPassees::class);
        $repositoryOdpfEditionsPassees = $this->doctrine->getRepository(OdpfEditionsPassees::class);
        $editionPassee = $repositoryOdpfEditionsPassees->findOneBy(['edition' => $edition->getEd()]);
        if ($fichier->getTypefichier() != 6) {
            $OdpfEquipepassee = $repositoryOdpfEquipesPassees->createQueryBuilder('e')
                ->where('e.numero =:numero')
                ->andWhere('e.editionspassees= :edition')
                ->setParameters(['numero' => $equipe->getNumero(), 'edition' => $editionPassee])
                ->getQuery()->getOneOrNullResult();

            $odpfFichier = $repositoryOdpfFichierspasses->findOneBy(['equipepassee' => $OdpfEquipepassee, 'typefichier' => $fichier->getTypefichier()]);
            if ($odpfFichier === null) {
                $odpfFichier = new OdpfFichierspasses();
                $odpfFichier->setTypefichier($fichier->getTypefichier());
            }
            $odpfFichier->setEquipePassee($OdpfEquipepassee);
        }
        if ($fichier->getTypefichier() == 6) {
            $odpfFichier = $repositoryOdpfFichierspasses->findOneBy(['Nomautorisation' => $fichier->getNomautorisation(), 'typefichier' => $fichier->getTypefichier()]);
            if ($odpfFichier === null) {
                $odpfFichier = new OdpfFichierspasses();
                $odpfFichier->setTypefichier(6);
            }

            $odpfFichier->setNomautorisation($fichier->getNomautorisation());

        }

        $odpfFichier->setEditionspassees($editionPassee);
        $odpfFichier->setNomFichier($fichier->getFichier());
        $odpfFichier->setFichierFile($fichier->getFichierFile());
        $odpfFichier->setUpdatedAt(new DateTime('now'));
        $em->persist($odpfFichier);
        $em->flush();
    }


}