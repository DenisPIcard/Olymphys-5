<?php

namespace App\Entity;

use App\Entity\Cia\JuresCia;
use App\Entity\Cia\NotesCia;
use App\Repository\EquipesadminRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use phpDocumentor\Reflection\Types\Nullable;


#[ORM\Entity(repositoryClass: EquipesadminRepository::class)]
class Equipesadmin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $lettre = null;


    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $numero;


    #[ORM\Column(nullable: true)]
    private ?bool $selectionnee;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titreProjet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomLycee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $denominationLycee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lyceeLocalite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lyceeAcademie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomProf1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomProf1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomProf2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomProf2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uai = null;

    #[ORM\ManyToOne]
    private ?Uai $uaiId = null;

    #[ORM\ManyToOne]
    private ?Centrescia $centre;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contribfinance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $origineprojet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recompense = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partenaire = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $createdAt;


    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $idProf1;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $idProf2;

    #[ORM\Column(nullable: true)]
    protected ?bool $inscrite = true;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $nbeleves = null;

    #[ORM\ManyToMany(targetEntity: Professeurs::class, mappedBy: 'equipes')]
    private ?Collection $equipesstring;

    #[ORM\ManyToOne]
    private ?Edition $edition;

    #[ORM\Column(nullable: true)]
    private ?bool $retiree = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->equipesstring = new ArrayCollection();
        $this->idProf2 = null;
        $this->selectionnee = false;
        $this->juresCia = new ArrayCollection();
        $this->notess = new ArrayCollection();
    }

    public function __toString(): string
    {
        $ed = $this->getEdition()->getEd();

        if ($this->getLettre() != null) {
            return $ed . '-' . $this->numero . '-' . $this->lettre . '-' . $this->titreProjet;
        } else {
            return $ed . '-' . $this->numero . '-' . $this->titreProjet;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setTitreProjet(?string $titreProjet): Equipesadmin
    {

        $this->createdAt = new DateTime('now');
        $this->titreProjet = $titreProjet;

        return $this;
    }

    public function getTitreProjet(): ?string
    {
        return $this->titreProjet;
    }

    public function setNumero(?int $numero): Equipesadmin
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setLettre(?string $lettre): Equipesadmin
    {
        $this->lettre = $lettre;

        return $this;
    }

    public function getLettre(): ?string
    {
        return $this->lettre;
    }


    public function getInfoequipe(): ?string
    {
        $nomcentre = '';

        $this->getLettre() === null ? $Numero = $this->getNumero() : $Numero = $this->numero . '-' . $this->getLettre();
        $edition = $this->getEdition();
        if ($centre = $this->getCentre()) {
            $nomcentre = $this->getCentre()->getCentre() . '-';
        }


        $nom_equipe = $this->getTitreProjet();
        $ville = $this->getLyceeLocalite();

        $infoequipe = $edition->getEd() . '-' . 'Eq ' . $Numero . ' - ' . $nom_equipe . '-' . $ville;
        return $infoequipe;
    }

    public function getInfoequipenat(): ?string
    {
        $edition = $this->getEdition();
        if ($this->getSelectionnee() == '1') {

            $lettre = $this->getLettre();
            if ($lettre === null) {
                $lettre = $this->numero;
            }

            $nom_equipe = $this->getTitreProjet();
            $infoequipe = $edition->getEd() . '-' . $lettre . ' - ' . $nom_equipe;
            if ($this->getUaiId()) {
                $infoequipe = $infoequipe . '-' . $this->getUaiId()->getCommune();
            }


            return $infoequipe;

        }
        return $this->$edition->getEd() . '-' . $this->titreProjet;
    }


    public function getSelectionnee(): ?bool
    {
        return $this->selectionnee;
    }

    public function setSelectionnee(?bool $selectionnee): Equipesadmin
    {
        $this->selectionnee = $selectionnee;

        return $this;
    }

    public function setNomLycee(?string $nomLycee): Equipesadmin
    {
        $this->nomLycee = $nomLycee;

        return $this;
    }

    public function getNomLycee(): ?string
    {
        return $this->nomLycee;
    }


    public function setDenominationLycee(?string $denominationLycee): Equipesadmin
    {
        $this->denominationLycee = $denominationLycee;

        return $this;
    }


    public function getDenominationLycee(): ?string
    {
        return $this->denominationLycee;
    }


    public function setLyceeLocalite(?string $lyceeLocalite): Equipesadmin
    {
        $this->lyceeLocalite = $lyceeLocalite;

        return $this;
    }


    public function getLyceeLocalite(): ?string
    {
        return $this->lyceeLocalite;
    }


    public function setLyceeAcademie(?string $lyceeAcademie): Equipesadmin
    {
        $this->lyceeAcademie = $lyceeAcademie;

        return $this;
    }


    public function getLyceeAcademie(): ?string
    {
        return $this->lyceeAcademie;
    }


    public function setPrenomProf1(?string $prenomProf1): Equipesadmin
    {
        $this->prenomProf1 = $prenomProf1;

        return $this;
    }


    public function getPrenomProf1(): ?string
    {
        return $this->prenomProf1;
    }

    public function setNomProf1(?string $nomProf1): Equipesadmin
    {
        $this->nomProf1 = $nomProf1;

        return $this;
    }

    public function getNomProf1(): ?string
    {
        return $this->nomProf1;
    }

    public function setPrenomProf2(?string $prenomProf2): self
    {
        $this->prenomProf2 = $prenomProf2;

        return $this;
    }

    public function getPrenomProf2(): ?string
    {
        return $this->prenomProf2;
    }

    public function setNomProf2(?string $nomProf2): Equipesadmin
    {
        $this->nomProf2 = $nomProf2;

        return $this;
    }

    public function getNomProf2(): ?string
    {
        return $this->nomProf2;
    }

    public function getUai(): ?string
    {
        return $this->uai;
    }

    public function setUai(?string $uai): Equipesadmin
    {
        $this->uai = $uai;
        return $this;
    }

    public function getUaiId(): ?Uai
    {
        return $this->uaiId;
    }

    public function setUaiId(?Uai $uai_id): Equipesadmin
    {
        $this->uaiId = $uai_id;
        return $this;
    }

    public function getLycee(): ?string
    {
        return $this->getNomLycee() . ' de  ' . $this->getLyceeLocalite();
    }

    public function getProf1(): ?string
    {

        return $this->getPrenomProf1() . ' ' . $this->getNomProf1();
    }

    public function getProf2(): ?string
    {

        return $this->getPrenomProf2() . ' ' . $this->getNomProf2();
    }

    public function getCentre(): ?Centrescia
    {
        return $this->centre;
    }

    public function setCentre(?Centrescia $centre): self
    {
        $this->centre = $centre;

        return $this;
    }

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getContribfinance(): ?string
    {
        return $this->contribfinance;
    }

    public function setContribfinance(?string $contribfinance): self
    {
        $this->contribfinance = $contribfinance;

        return $this;
    }

    public function getOrigineprojet(): ?string
    {
        return $this->origineprojet;
    }

    public function setOrigineprojet(?string $origineprojet): self
    {
        $this->origineprojet = $origineprojet;

        return $this;
    }

    public function getRecompense(): ?string
    {
        return $this->recompense;
    }

    public function setRecompense(?string $recompense): self
    {
        $this->recompense = $recompense;

        return $this;
    }

    public function getPartenaire(): ?string
    {
        return $this->partenaire;
    }

    public function setPartenaire(?string $partenaire): self
    {
        $this->partenaire = $partenaire;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }


    public function getIdProf1(): ?user
    {
        return $this->idProf1;
    }

    public function setIdProf1(?user $idProf1): self
    {
        $this->idProf1 = $idProf1;

        return $this;
    }

    public function getIdProf2(): ?user
    {
        return $this->idProf2;
    }

    public function setIdProf2(?user $idProf2): self
    {
        $this->idProf2 = $idProf2;

        return $this;
    }


    public function getInscrite(): ?bool
    {
        return $this->inscrite;
    }

    public function setInscrite(bool $inscrite): self
    {
        $this->inscrite = $inscrite;


        return $this;
    }

    public function getRetiree(): ?bool
    {
        return $this->retiree;
    }

    public function setRetiree(?bool $retiree): self
    {
        $this->retiree = $retiree;

        return $this;
    }

    public function getNbeleves(): ?int
    {
        return $this->nbeleves;
    }

    public function setNbeleves(?int $nbeleves): self
    {
        $this->nbeleves = $nbeleves;

        return $this;
    }

    public function getEquipesstring(): ?Collection
    {
        return $this->equipesstring;
    }

    public function addEquipesstring(?Professeurs $equipesstring): self
    {
        if (!$this->equipesstring->contains($equipesstring)) {
            $this->equipesstring[] = $equipesstring;
            $equipesstring->addEquipe($this);
        }

        return $this;
    }

    public function removeEquipesstring(Professeurs $equipesstring): self
    {
        if ($this->equipesstring->removeElement($equipesstring)) {
            $equipesstring->removeEquipe($this);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

}