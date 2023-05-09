<?php

namespace App\Entity\Cia;

use App\Entity\Coefficients;
use App\Entity\Equipesadmin;
use App\Repository\Cia\NotesCiaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotesCiaRepository::class)]
class NotesCia
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

    #[ORM\Column(name: 'Wgroupe', type: Types::SMALLINT)]
    private ?int $wgroupe = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ecrit = 0;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $repquestions = 0;

    #[ORM\ManyToOne(targetEntity: Equipesadmin::class, inversedBy: "notess")]
    private ?Equipesadmin $equipe;

    #[ORM\ManyToOne(targetEntity: JuresCia::class, inversedBy: "notesj")]
    private ?JuresCia $jure;


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

    public function setExper(int $exper): NotesCia
    {
        $this->exper = $exper;

        return $this;
    }

    public function getExper(): ?int
    {
        return $this->exper;
    }

    public function setDemarche(int $demarche): NotesCia
    {
        $this->demarche = $demarche;

        return $this;
    }

    public function getDemarche(): ?int
    {
        return $this->demarche;
    }

    public function setOral(int $oral): NotesCia
    {
        $this->oral = $oral;

        return $this;
    }

    public function getOral(): ?int
    {
        return $this->oral;
    }

    public function setRepquestions(int $repquestions): NotesCia
    {
        $this->repquestions = $repquestions;

        return $this;
    }

    public function getRepquestions(): ?int
    {
        return $this->repquestions;
    }

    public function setOrigin(int $origin): NotesCia
    {
        $this->origin = $origin;

        return $this;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setWgroupe(int $wgroupe): NotesCia
    {
        $this->wgroupe = $wgroupe;

        return $this;
    }

    public function getWgroupe(): ?int
    {
        return $this->wgroupe;
    }

    public function setEcrit(int $ecrit): NotesCia
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
            + $this->getRepquestions() * $this->coefficients->getRepquestions()//
            + $this->getOrigin() * $this->coefficients->getOrigin()//
            + $this->getWgroupe() * $this->coefficients->getWgroupe();
    }

    public function getTotalPoints(): float|int //Calcul le total pour un juré avec l'écrit
    {
        return $this->getExper() * $this->coefficients->getExper()
            + $this->getDemarche() * $this->coefficients->getDemarche()
            + $this->getOral() * $this->coefficients->getOral()//
            + $this->getRepquestions() * $this->coefficients->getRepquestions()//
            + $this->getOrigin() * $this->coefficients->getOrigin()//
            + $this->getWgroupe() * $this->coefficients->getWgroupe()
            + $this->getEcrit() * $this->coefficients->getEcrit();

    }

    public function setEquipe(Equipesadmin $equipe): NotesCia
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setJure(JuresCia $jure): ?NotesCia
    {
        $this->jure = $jure;

        return $this;
    }

    public function getJure(): ?JuresCia
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

    public function setJureCia(JuresCia $param)
    {
    }


}

