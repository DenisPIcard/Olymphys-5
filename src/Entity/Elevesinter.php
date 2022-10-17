<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

/**
 * Eleves
 *
 * @ORM\Table(name="elevesinter")
 * @ORM\Entity(repositoryClass="App\Repository\ElevesinterRepository")
 */
class Elevesinter
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="numsite", type="integer", nullable=true)
     *
     */
    private ?int $numsite = 0;

    /**
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     */
    private ?string $nom = null;
    /**
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private ?string $prenom = null;
    /**
     * @ORM\Column(name="genre", type="string", length=1, nullable=true)
     */
    private ?string $genre = null;

    /**
     *
     * @ORM\Column(name="classe", type="string", length=255, nullable=true)
     */
    private ?string $classe = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipesadmin")
     * @ORM\JoinColumn(name="equipe_id",  referencedColumnName="id" )
     */
    private ?Equipesadmin $equipe;

    /**
     *
     * @ORM\Column(name="courriel", type="string",length=60, nullable=true)
     */
    private ?string $courriel = null;


    /**
     * @ORM\OneToOne(targetEntity=Fichiersequipes::class, mappedBy="eleve", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="autorisationphotos_id",  referencedColumnName="id" , nullable=true)
     */
    private ?Fichiersequipes $autorisationphotos;


    public function __toString()
    {
        return $this->getNomPrenomlivre();

    }

    public function getNomPrenomlivre(): string
    {
        if ($this->equipe->getSelectionnee() == true) {
            $NomPrenom = $this->equipe->getNumero() . '-' . $this->equipe->getLettre() . '-' . $this->nom . ' ' . $this->prenom;
        }
        if ($this->equipe->getSelectionnee() == false) {
            $NomPrenom = $this->equipe->getNumero() . '-' . $this->nom . ' ' . $this->prenom;
        }
        return $NomPrenom;
    }

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
     * Get numsite
     *
     * @return integer
     */
    public function getNumsite(): ?int
    {
        return $this->numsite;
    }

    /**
     * Set numsite
     *
     * @var integer
     */
    public function setNumsite($numsite)
    {
        $this->numsite = $numsite;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Elevesinter
     */
    public function setNom(string $nom): Elevesinter
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Elevesinter
     */
    public function setPrenom(string $prenom): Elevesinter
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get classe
     *
     * @return string
     */
    public function getClasse(): ?string
    {
        return $this->classe;
    }

    /**
     * Set classe
     *
     * @param string $classe
     *
     * @return Elevesinter
     */
    public function setClasse(string $classe): Elevesinter
    {
        $this->classe = $classe;

        return $this;
    }

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe($Equipe): Elevesinter
    {
        $this->equipe = $Equipe;

        return $this;
    }

    public function getCourriel(): ?string
    {
        return $this->courriel;
    }

    public function setCourriel($courriel): Elevesinter
    {
        $this->courriel = $courriel;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre($genre): Elevesinter
    {
        $this->genre = $genre;

        return $this;
    }

    public function getAutorisationphotos(): ?fichiersequipes
    {
        return $this->autorisationphotos;
    }

    public function setAutorisationphotos($autorisation): Elevesinter
    {
        $this->autorisationphotos = $autorisation;

        return $this;
    }

    public function getNomPrenom(): ?string
    {

        return $this->nom . ' ' . $this->prenom;
    }

    public function getFichiersequipes(): ?Fichiersequipes
    {
        return $this->fichiersequipes;
    }

    public function setFichiersequipes(?Fichiersequipes $fichiersequipes): self
    {
        // unset the owning side of the relation if necessary
        if ($fichiersequipes === null && $this->fichiersequipes !== null) {
            $this->fichiersequipes->setEleve(null);
        }

        // set the owning side of the relation if necessary
        if ($fichiersequipes !== null && $fichiersequipes->getEleve() !== $this) {
            $fichiersequipes->setEleve($this);
        }

        $this->fichiersequipes = $fichiersequipes;

        return $this;
    }


}

