<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notes
 *
 * @ORM\Table(name="notes")
 * @ORM\Entity(repositoryClass="App\Repository\NotesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Notes
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="exper", type="smallint")
     */
    private ?int $exper = 0;

    /**
     * @ORM\Column(name="demarche", type="smallint")
     */
    private ?int $demarche = 0;

    /**
     * @ORM\Column(name="oral", type="smallint")
     */
    private ?int $oral = 0;

    /**
     * @ORM\Column(name="origin", type="smallint")
     */
    private ?int $origin = 0;

    /**
     * @ORM\Column(name="Wgroupe", type="smallint")
     */
    private ?int $wgroupe = 0;

    /**
     * @ORM\Column(name="ecrit", type="smallint", nullable=true)
     */
    private ?int $ecrit = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipes", inversedBy="notess")
     * @ORM\JoinColumn(name="equipe_id",nullable=false)
     */
    private Equipes $equipe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Jures", inversedBy="notesj")
     * @ORM\JoinColumn(nullable=false)
     */
    private Jures $jure;


    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $total = 0;

    /**
     * @ORM\ManyToOne(targetEntity=Coefficients::class)
     * @ORM\JoinColumn(name="coefficients_id",  referencedColumnName="id",onDelete="CASCADE" )
     */
    private ?coefficients $coefficients;


    const NE_PAS_NOTER = 0; // pour les écrits....
    const INSUFFISANT = 1;
    const MOYEN = 2;
    const BIEN = 3;
    const EXCELLENT = 4;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set exper
     *
     * @param integer $exper
     *
     * @return Notes
     */
    public function setExper(int $exper): Notes
    {
        $this->exper = $exper;

        return $this;
    }

    /**
     * Get exper
     *
     * @return int
     */
    public function getExper(): ?int
    {
        return $this->exper;
    }

    /**
     * Set demarche
     *
     * @param integer $demarche
     *
     * @return Notes
     */
    public function setDemarche(int $demarche): Notes
    {
        $this->demarche = $demarche;

        return $this;
    }

    /**
     * Get demarche
     *
     * @return int
     */
    public function getDemarche(): ?int
    {
        return $this->demarche;
    }

    /**
     * Set oral
     *
     * @param integer $oral
     *
     * @return Notes
     */
    public function setOral(int $oral): Notes
    {
        $this->oral = $oral;

        return $this;
    }

    /**
     * Get oral
     *
     * @return int
     */
    public function getOral(): ?int
    {
        return $this->oral;
    }

    /**
     * Set origin
     *
     * @param integer $origin
     *
     * @return Notes
     */
    public function setOrigin(int $origin): Notes
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return int
     */
    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    /**
     * Set wgroupe
     *
     * @param integer $wgroupe
     *
     * @return Notes
     */
    public function setWgroupe(int $wgroupe): Notes
    {
        $this->wgroupe = $wgroupe;

        return $this;
    }

    /**
     * Get wgroupe
     *
     * @return int
     */
    public function getWgroupe(): ?int
    {
        return $this->wgroupe;
    }

    /**
     * Set ecrit
     *
     * @param integer $ecrit
     *
     * @return Notes
     */
    public function setEcrit(int $ecrit): Notes
    {
        $this->ecrit = $ecrit;

        return $this;
    }

    /**
     * Get ecrit
     *
     * @return int
     */
    public function getEcrit(): ?int
    {
        return $this->ecrit;
    }

// Les attributs calculés
    public function getPoints(): float|int //Calcul le total pour un juré sans l'écrit
    {

        return $this->getExper() * $this->coefficients->getExper()
            + $this->getDemarche() * $this->coefficients->getDemarche()
            + $this->getOral() * $this->coefficients->getOral()
            + $this->getOrigin() * $this->coefficients->getOrigin()
            + $this->getWgroupe() * $this->coefficients->getWgroupe();
    }

    public function getTotalPoints(): float|int //Calcul le total pour un juré avec l'écrit
    {
        return $this->getExper() * $this->coefficients->getExper()
            + $this->getDemarche() * $this->coefficients->getDemarche()
            + $this->getOral() * $this->coefficients->getOral()//
            + $this->getOrigin() * $this->coefficients->getOrigin()//
            + $this->getWgroupe() * $this->coefficients->getWgroupe()
            + $this->getEcrit() * $this->coefficients->getEcrit();

    }

    /**
     * Set equipe
     *
     * @param Equipes $equipe
     *
     * @return Notes
     */
    public function setEquipe(Equipes $equipe): Notes
    {
        $this->equipe = $equipe;

        return $this;
    }

    /**
     * Get equipe
     *
     * @return Equipes|null
     */
    public function getEquipe(): ?Equipes
    {
        return $this->equipe;
    }

    /**
     * Set jure
     *
     * @param Jures $jure
     *
     * @return Notes
     */
    public function setJure(Jures $jure): Notes
    {
        $this->jure = $jure;

        return $this;
    }

    /**
     * Get jure
     *
     * @return Jures|null
     */
    public function getJure(): ?Jures
    {
        return $this->jure;
    }


    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getCoefficients(): ?coefficients
    {
        return $this->coefficients;
    }

    public function setCoefficients(?coefficients $coefficients): self
    {
        $this->coefficients = $coefficients;

        return $this;
    }


}

