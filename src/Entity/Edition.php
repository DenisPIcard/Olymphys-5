<?php

namespace App\Entity;

use App\Repository\EditionRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use datetime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EditionRepository::class)]
class Edition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ed = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $datelimCia = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?datetime $datelimNat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?Datetime $dateOuvertureSite = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?Datetime $concoursCia = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?datetime $concoursCn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?Datetime $dateclotureinscription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $annee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomParrain = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titreParrain = null;

    #[ORM\Column(length: 255, nullable: true)]
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

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(?DateTime $date): self
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

    public function setDatelimCia($datelimcia)
    {
        $this->datelimCia = $datelimcia;
    }

    public function getDatelimCia()
    {
        return $this->datelimCia;
    }

    public function setDatelimNat($Date)
    {
        $this->datelimNat = $Date;
    }

    public function getDatelimNat()
    {
        return $this->datelimNat;
    }

    public function setDateOuvertureSite($Date)
    {
        $this->dateOuvertureSite = $Date;
    }

    public function getDateOuvertureSite()
    {
        return $this->dateOuvertureSite;
    }

    public function setConcoursCia($Date)
    {
        $this->concoursCia = $Date;
    }

    public function getConcoursCia()
    {
        return $this->concoursCia;
    }

    public function setConcoursCn($Date)
    {
        $this->concoursCn = $Date;
    }

    public function getConcoursCn()
    {
        return $this->concoursCn;
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

    public function getDateclotureinscription(): ?DateTimeInterface
    {
        return $this->dateclotureinscription;
    }

    public function setDateclotureinscription(DateTimeInterface $dateclotureinscription): self
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
