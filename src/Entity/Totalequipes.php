<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Totalequipes
 *
 * @ORM\Table(name="totalequipes")
 * @ORM\Entity(repositoryClass="App\Repository\TotalequipesRepository")
 */
class Totalequipes
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="numero_equipe", type="smallint", nullable=true)
     */
    private $numeroEquipe;

    /**
     * @var string
     *
     * @ORM\Column(name="lettre_equipe", type="string", length=1, nullable=true)
     */
    private $lettreEquipe;
    /**
     * @var string
     *
     * @ORM\Column(name="nom_equipe", type="string", length=255, nullable=true)
     */
    private $nomEquipe;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_lycee", type="string", length=255, nullable=true)
     */
    private $nomLycee;

    /**
     * @var string
     *
     * @ORM\Column(name="denomination_lycee", type="string", length=255, nullable=true)
     */
    private $denominationLycee;

    /**
     * @var string
     *
     * @ORM\Column(name="lycee_localite", type="string", length=255, nullable=true)
     */
    private $lyceeLocalite;

    /**
     * @var string
     *
     * @ORM\Column(name="lycee_academie", type="string", length=255, nullable=true)
     */
    private $lyceeAcademie;

    /**
     * @var int
     *
     * @ORM\Column(name="id_prof1", type="smallint", unique=false, nullable=true)
     */
    private $idprof1;

    /**
     * @var int
     *
     * @ORM\Column(name="id_prof2", type="smallint", unique=false, nullable=true)
     */
    private $idprof2;


    /**
     * @var string
     *
     * @ORM\Column(name="prenom_prof1", type="string", length=255, nullable=true)
     */
    private $prenomProf1;


    /**
     * @var string
     *
     * @ORM\Column(name="nom_prof1", type="string", length=255, nullable=true)
     */
    private $nomProf1;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom_prof2", type="string", length=255, nullable=true)
     */
    private $prenomProf2;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_prof2", type="string", length=255, nullable=true)
     */
    private $nomProf2;


    /**
     * @var string
     *
     * @ORM\Column(name="rne", type="string", unique=false, nullable=true)
     */
    private $rne;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set numeroEquipe
     *
     * @param integer $numeroEquipe
     *
     * @return Totalequipes
     */
    public function setNumeroEquipe($numeroEquipe)
    {
        $this->numeroEquipe = $numeroEquipe;

        return $this;
    }

    /**
     * Get numeroEquipe
     *
     * @return integer
     */
    public function getNumeroEquipe()
    {
        return $this->numeroEquipe;
    }

    /**
     * Set lettreEquipe
     *
     * @param string $lettreEquipe
     *
     * @return Totalequipes
     */
    public function setLettreEquipe($lettreEquipe)
    {
        $this->lettreEquipe = $lettreEquipe;

        return $this;
    }

    /**
     * Get lettreEquipe
     *
     * @return string
     */
    public function getLettreEquipe()
    {
        return $this->lettreEquipe;
    }

    /**
     * Set nomEquipe
     *
     * @param string $nomEquipe
     *
     * @return Totalequipes
     */
    public function setNomEquipe($nomEquipe)
    {
        $this->nomEquipe = $nomEquipe;

        return $this;
    }

    /**
     * Get nomEquipe
     *
     * @return string
     */
    public function getNomEquipe()
    {
        return $this->nomEquipe;
    }

    /**
     * Set nomLycee
     *
     * @param string $nomLycee
     *
     * @return Totalequipes
     */
    public function setNomLycee($nomLycee)
    {
        $this->nomLycee = $nomLycee;

        return $this;
    }

    /**
     * Get nomLycee
     *
     * @return string
     */
    public function getNomLycee()
    {
        return $this->nomLycee;
    }

    /**
     * Set denominationLycee
     *
     * @param string $denominationLycee
     *
     * @return Totalequipes
     */
    public function setDenominationLycee($denominationLycee)
    {
        $this->denominationLycee = $denominationLycee;

        return $this;
    }

    /**
     * Get denominationLycee
     *
     * @return string
     */
    public function getDenominationLycee()
    {
        return $this->denominationLycee;
    }

    /**
     * Set lyceeLocalite
     *
     * @param string $lyceeLocalite
     *
     * @return Totalequipes
     */
    public function setLyceeLocalite($lyceeLocalite)
    {
        $this->lyceeLocalite = $lyceeLocalite;

        return $this;
    }

    /**
     * Get lyceeLocalite
     *
     * @return string
     */
    public function getLyceeLocalite()
    {
        return $this->lyceeLocalite;
    }

    /**
     * Set lyceeAcademie
     *
     * @param string $lyceeAcademie
     *
     * @return Totalequipes
     */
    public function setLyceeAcademie($lyceeAcademie)
    {
        $this->lyceeAcademie = $lyceeAcademie;

        return $this;
    }

    /**
     * Get lyceeAcademie
     *
     * @return string
     */
    public function getLyceeAcademie()
    {
        return $this->lyceeAcademie;
    }

    /**
     * Set idprof1
     *
     * @param integer $idprof1
     *
     * @return Totalequipes
     */
    public function setIdprof1($idprof1)
    {
        $this->idprof1 = $idprof1;

        return $this;
    }

    /**
     * Get idprof1
     *
     * @return integer
     */
    public function getIdprof1()
    {
        return $this->idprof1;
    }


    /**
     * Set idprof2
     *
     * @param integer $idprof2
     *
     * @return Totalequipes
     */
    public function setIdprof2($idprof2)
    {
        $this->idprof1 = $idprof2;

        return $this;
    }

    /**
     * Get idprof2
     *
     * @return integer
     */
    public function getIdprof2()
    {
        return $this->idprof2;
    }


    /**
     * Set prenomProf1
     *
     * @param string $prenomProf1
     *
     * @return Totalequipes
     */
    public function setPrenomProf1($prenomProf1)
    {
        $this->prenomProf1 = $prenomProf1;

        return $this;
    }

    /**
     * Get prenomProf1
     *
     * @return string
     */
    public function getPrenomProf1()
    {
        return $this->prenomProf1;
    }

    /**
     * Set nomProf1
     *
     * @param string $nomProf1
     *
     * @return Totalequipes
     */
    public function setNomProf1($nomProf1)
    {
        $this->nomProf1 = $nomProf1;

        return $this;
    }

    /**
     * Get nomProf1
     *
     * @return string
     */
    public function getNomProf1()
    {
        return $this->nomProf1;
    }

    /**
     * Set prenomProf2
     *
     * @param string $prenomProf2
     *
     * @return Totalequipes
     */
    public function setPrenomProf2($prenomProf2)
    {
        $this->prenomProf2 = $prenomProf2;

        return $this;
    }

    /**
     * Get prenomProf2
     *
     * @return string
     */
    public function getPrenomProf2()
    {
        return $this->prenomProf2;
    }

    /**
     * Set nomProf2
     *
     * @param string $nomProf2
     *
     * @return Totalequipes
     */
    public function setNomProf2($nomProf2)
    {
        $this->nomProf2 = $nomProf2;

        return $this;
    }

    /**
     * Get nomProf2
     *
     * @return string
     */
    public function getNomProf2()
    {
        return $this->nomProf2;
    }

    public function getLycee()
    {
        return $this->getDenominationLycee() . ' ' . $this->getNomLycee() . ' de  ' . $this->getLyceeLocalite();
    }

    public function getProf1()
    {

        return $this->getPrenomProf1() . ' ' . $this->getNomProf1();
    }

    public function getProf2()
    {

        return $this->getPrenomProf2() . ' ' . $this->getNomProf2();
    }

    /**
     * Set rne
     *
     * @param string rne
     *
     * @return Totalequipes
     */
    public function setRne($rne)
    {
        $this->rne = $rne;

        return $this;
    }

    /**
     * Get rne
     *
     * @return string
     */
    public function getRne()
    {
        return $this->rne;
    }


    /**
     * Get infoequipe
     *
     * @return \App\Entity\Equipes
     */
    public function getInfoequipe()
    {
        $Lettre = $this->getLettreEquipe();


        $nom_equipe = $this->getNomEquipe();


        $infoequipe = $Lettre . '-' . $nom_equipe;
        return $infoequipe;
    }


}
