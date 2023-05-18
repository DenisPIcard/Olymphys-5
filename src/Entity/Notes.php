<?php

namespace App\Entity;

use App\Repository\NotesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotesRepository::class)]
class Notes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $exper = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $demarche = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $oral = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $origin = 0;

    #[ORM\Column(type: Types::SMALLINT, name: 'Wgroupe')]
    private ?int $wgroupe = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ecrit = 0;

    #[ORM\ManyToOne(targetEntity: Equipes::class, inversedBy: "notess")]
    private Equipes $equipe;

    #[ORM\ManyToOne(targetEntity: Jures::class, inversedBy: "notesj")]
    private Jures $jure;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $repquestions = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $total = 0;

    #[ORM\ManyToOne(targetEntity: Coefficients::class)]
    #[ORM\JoinColumn(name: "coefficients_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?Coefficients $coefficients;


    const NE_PAS_NOTER = 0; // pour les écrits....
    const INSUFFISANT = 1;
    const MOYEN = 2;
    const BIEN = 3;
    const EXCELLENT = 4;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setExper(int $exper): Notes
    {
        $this->exper = $exper;

        return $this;
    }

    public function getExper(): ?int
    {
        return $this->exper;
    }

    public function setDemarche(int $demarche): Notes
    {
        $this->demarche = $demarche;

        return $this;
    }

    public function getDemarche(): ?int
    {
        return $this->demarche;
    }

    public function setOral(int $oral): Notes
    {
        $this->oral = $oral;

        return $this;
    }

    public function getOral(): ?int
    {
        return $this->oral;
    }

    public function setOrigin(int $origin): Notes
    {
        $this->origin = $origin;

        return $this;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setWgroupe(int $wgroupe): Notes
    {
        $this->wgroupe = $wgroupe;

        return $this;
    }

    public function getWgroupe(): ?int
    {
        return $this->wgroupe;
    }

    public function setEcrit(int $ecrit): Notes
    {
        $this->ecrit = $ecrit;

        return $this;
    }

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
            + $this->getRepquestions() * $this->coefficients->getRepquestions()
            + $this->getWgroupe() * $this->coefficients->getWgroupe();
    }

    public function getTotalPoints(): float|int //Calcul le total pour un juré avec l'écrit
    {
        return $this->getExper() * $this->coefficients->getExper()
            + $this->getDemarche() * $this->coefficients->getDemarche()
            + $this->getOral() * $this->coefficients->getOral()//
            + $this->getOrigin() * $this->coefficients->getOrigin()//
            + $this->getWgroupe() * $this->coefficients->getWgroupe()
            + $this->getRepquestions() * $this->coefficients->getRepquestions()
            + $this->getEcrit() * $this->coefficients->getEcrit();

    }

    public function setEquipe(Equipes $equipe): Notes
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getEquipe(): ?Equipes
    {
        return $this->equipe;
    }

    public function setJure(Jures $jure): Notes
    {
        $this->jure = $jure;

        return $this;
    }

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

    public function getRepquestions(): ?int
    {
        return $this->repquestions;
    }

    public function setRepquestions(?int $repquestions): self
    {
        $this->repquestions = $repquestions;

        return $this;
    }


}

