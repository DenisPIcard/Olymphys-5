<?php

namespace App\Entity;

use datetime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EditionRepository")
 */
class Edition
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $ed = null;

    /**
     * @ORM\Column(type="datetime",  nullable=true)
     */
    private ?DateTime $date = null;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $ville = null;

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     */
    private ?string $lieu = null;

    /**
     * @var DateTime
     * @ORM\Column(name="datelimite_cia", type="datetime", nullable=true)
     */
    protected DateTime $datelimcia;

    /**
     * @var datetime
     * @ORM\Column(name="datelimite_nat", type="datetime",nullable=true)
     */
    protected ?datetime $datelimnat = null;

    /**
     * @var datetime
     * @ORM\Column(name="date_ouverture_site", type="datetime",nullable=true)
     */
    protected ?datetime $dateouverturesite = null;


    /**
     * @var datetime
     * @ORM\Column(name="concours_cia", type="datetime",nullable=true)
     */
    protected ?datetime $concourscia = null;


    /**
     * @var datetime
     * @ORM\Column(name="concours_cn", type="datetime",nullable=true)
     */
    protected ?datetime $concourscn = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?datetime $dateclotureinscription = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $annee = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $nomParrain = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $titreParrain = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $dateinscriptions = null;

    public function __toString() : string
    {
        return strval($this->ed);


    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEd(): ?string
    {
        return $this->ed;
    }

    public function setEd(string $ed): self
    {
        $this->ed = $ed;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }


    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function setDatelimcia($Date)
    {
        $this->datelimcia = $Date;
    }

    public function getDatelimcia()
    {
        return $this->datelimcia;
    }

    public function setDatelimnat($Date)
    {
        $this->datelimnat = $Date;
    }

    public function getDatelimnat()
    {
        return $this->datelimnat;
    }

    public function setDateouverturesite($Date)
    {
        $this->dateouverturesite = $Date;
    }

    public function getDateouverturesite()
    {
        return $this->dateouverturesite;
    }

    public function setConcourscia($Date)
    {
        $this->concourscia = $Date;
    }

    public function getConcourscia()
    {
        return $this->concourscia;
    }

    public function setConcourscn($Date)
    {
        $this->concourscn = $Date;
    }

    public function getConcourscn()
    {
        return $this->concourscn;
    }

    public function getEncours(): ?bool
    {
        return $this->encours;
    }

    public function setEncours(?bool $encours): self
    {
        $this->encours = $encours;

        return $this;
    }

    public function getDateclotureinscription(): ?\DateTimeInterface
    {
        return $this->dateclotureinscription;
    }

    public function setDateclotureinscription(\DateTimeInterface $dateclotureinscription): self
    {
        $this->dateclotureinscription = $dateclotureinscription;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getNomParrain(): ?string
    {
        return $this->nomParrain;
    }

    public function setNomParrain(?string $nomParrain): self
    {
        $this->nomParrain = $nomParrain;

        return $this;
    }

    public function getTitreParrain(): ?string
    {
        return $this->titreParrain;
    }

    public function setTitreParrain(?string $titreParrain): self
    {
        $this->titreParrain = $titreParrain;

        return $this;
    }

    public function getDateinscriptions(): ?string
    {
        return $this->dateinscriptions;
    }

    public function setDateinscriptions(?string $dateinscriptions): self
    {
        $this->dateinscriptions = $dateinscriptions;

        return $this;
    }


}
