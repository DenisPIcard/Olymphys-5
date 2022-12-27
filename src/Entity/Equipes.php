<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Equipes
 * @Vich\Uploadable
 * @ORM\Table(name="equipes")
 * @ORM\Entity(repositoryClass="App\Repository\EquipesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Equipes
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;


    /**
     * @var int|null
     *
     * @ORM\Column(name="ordre", type="smallint",nullable=true)
     */
    private ?int $ordre = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="heure", type="string", length=255, nullable=true)
     */
    private ?string $heure = null;

    /**
     * @ORM\Column(name="salle", type="string", length=255, nullable=true)
     */
    private ?string $salle = null;

    /**
     * @ORM\Column(name="total", type="smallint", nullable=true)
     */
    private ?int $total = null;

    /**
     * @ORM\Column(name="classement", type="string", length=255, nullable=true)
     */
    private ?string $classement = null;

    /**
     * @ORM\Column(name="rang", type="smallint", nullable=true)
     */
    private ?int $rang = null;

    /**
     * @ORM\Column(name="couleur", type="smallint", nullable=true)
     */
    private ?int $couleur = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Visites")
     * @ORM\JoinColumn(name="visite_id", nullable=true)
     */
    private ?Visites $visite = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Cadeaux")
     * @ORM\JoinColumn(name="cadeau_id", nullable=true)
     */
    private ?Cadeaux $cadeau = null;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Prix")
     * @ORM\JoinColumn(name="prix_id", nullable=true)
     */
    private ?Prix $prix = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Equipesadmin")
     * @ORM\JoinColumn(name="equipeinter_id", nullable=true)
     */
    private ?Equipesadmin $equipeinter = null;

    /**
     * @ORM\Column(name="nb_notes", type="integer", nullable=true)
     */
    private ?int $nbNotes = null;


    /**
     * @ORM\ManyToOne(targetEntity=user::class)
     */
    private ?user $observateur = null;

    // notez le "s" : une equipe est liée à plusieurs eleves.
    // Notez le "s" : une equipe est liée à plusieurs lignes de "notes".
    /**
     * @ORM\OneToMany(targetEntity=Notes::class, mappedBy="equipe")
     */
    private ?Collection $notess;

    /**
     * @ORM\OneToMany(targetEntity=Phrases::class, mappedBy="equipe")
     */
    private ?Collection $phrases;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $salleZoom = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notess = new ArrayCollection();
        $this->phrases = new ArrayCollection();


    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSalle(): ?string
    {
        return $this->salle;
    }

    public function setSalle($salle): Equipes
    {
        $this->salle = $salle;

        return $this;
    }

    public function getVisite(): ?Visites
    {
        return $this->visite;
    }

    public function setVisite(Visites $visite = null): Equipes
    {
        $visiteini = $this->visite;
        if ($visite != null) {
            $visite->setAttribue(true);

        } else {

            if ($visiteini != null) {
                $visiteini->setAttribue(false);
            }
        }
        $this->visite = $visite;

        return $this;
    }

    public function addNotess(Notes $notess): Equipes
    {
        $this->notess[] = $notess;

        $notess->setEquipe($this);

        return $this;
    }

    public function removeNotess(Notes $notess)
    {
        $this->notess->removeElement($notess);
    }

    public function getNotess(): ?Collection
    {
        return $this->notess;
    }

    public function getNbNotes(): ?int
    {
        return $this->nbNotes;
    }

    public function setNbNotes(int $nbNotes): Equipes
    {
        $this->nbNotes = $nbNotes;

        return $this;
    }

    public function getCadeau(): ?Cadeaux
    {
        return $this->cadeau;
    }

    public function setCadeau(Cadeaux $cadeau = null): Equipes
    {
        $cadeauini = $this->cadeau;
        if ($cadeau != null) {
            $cadeau->setAttribue(true);

        } else {
            if ($cadeauini != null) {
                $cadeauini->setAttribue(false);
            }
        }
        $this->cadeau = $cadeau;

        return $this;
    }

    /**
     * add phrase
     *
     * @param Phrases $phrase
     *
     * @return Equipes
     */
    public function addPhrase(Phrases $phrase): Equipes
    {
        $this->phrases[] = $phrase;

        return $this;
    }

    public function removePhrases(Phrases $phrase)
    {
        $this->phrases->removeElement($phrase);
    }

    /**
     * Get phrases
     *
     * @return Collection
     */
    public function getPhrases(): Collection
    {
        return $this->phrases;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): Equipes
    {
        $this->total = $total;

        return $this;
    }

    public function getClassement(): ?string
    {
        return $this->classement;
    }

    public function setClassement(string $classement): Equipes
    {
        $this->classement = $classement;

        return $this;
    }

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(int $rang): Equipes
    {
        $this->rang = $rang;

        return $this;
    }

    public function getCouleur(): ?int
    {
        return $this->couleur;
    }

    public function setCouleur(int $couleur): Equipes
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getPrix(): ?Prix
    {
        return $this->prix;
    }

    public function setPrix($prix): Equipes
    {
        $this->prix = $prix;

        return $this;
    }

    public function getEquipeinter(): ?Equipesadmin
    {
        return $this->equipeinter;
    }

    public function setEquipeinter($equipeinter): Equipes
    {
        $this->equipeinter = $equipeinter;

        return $this;
    }

    public function getClassementEquipe(): ?string
    {
        return $this->classement . ' prix' . ' : ' . $this->lettre . ' - ' . $this->infoequipe->getTitreProjet() . ' ' . $this->infoequipe->getLyceeLocalite();

    }

    /* public function getSallesecours(): ?string
     {
         return $this->sallesecours;
     }

     public function setSallesecours(?string $sallesecours): self
     {
         $this->sallesecours = $sallesecours;

         return $this;
     }

     public function getHote(): ?user
     {
         return $this->hote;
     }

     public function setHote(?user $hote): self
     {
         $this->hote = $hote;

         return $this;
     }

     public function getInterlocuteur(): ?user
     {
         return $this->interlocuteur;
     }

     public function setInterlocuteur(?user $interlocuteur): self
     {
         $this->interlocuteur = $interlocuteur;

         return $this;
     }
      public function getCode(): ?string
     {
         return $this->code;
     }

     public function setCode(?string $code): self
     {
         $this->code = $code;

         return $this;
     }
 */
    public function getObservateur(): ?user
    {
        return $this->observateur;
    }

    public function setObservateur(?user $observateur): self
    {
        $this->observateur = $observateur;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(?string $heure): self
    {
        $this->heure = $heure;

        return $this;
    }

    public function getSalleZoom(): ?string
    {
        return $this->salleZoom;
    }

    public function setSalleZoom(?string $salleZoom): self
    {
        $this->salleZoom = $salleZoom;

        return $this;
    }

    public function getPhrase(): ?Phrases
    {
        return $this->getPhrases()[0];


    }


}