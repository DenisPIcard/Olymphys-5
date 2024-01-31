<?php

namespace App\Entity;

use App\Repository\EquipesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DoctrineExtensions\Query\Mysql\Time;
use Symfony\Component\Validator\Constraints\DateTime;

#[ORM\Entity(repositoryClass: EquipesRepository::class)]
class Equipes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Equipesadmin $equipeinter = null;

    #[ORM\Column(nullable: true)]
    private ?int $ordre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $heure = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salle = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $total = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $classement = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $rang = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $couleur = null;

    #[ORM\OneToOne(inversedBy: 'equipe', cascade: ['persist', 'remove'])]
    private ?Visites $visite = null;

    #[ORM\OneToOne(inversedBy: 'equipe', cascade: ['persist', 'remove'])]
    private ?Cadeaux $cadeau = null;

    #[ORM\OneToOne(inversedBy: 'equipe', cascade: ['persist', 'remove'])]
    private ?Prix $prix = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $nbNotes = null;

    #[ORM\ManyToOne]
    private ?User $observateur = null;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: Notes::class)]
    private ?Collection $notess;

    #[ORM\OneToMany(mappedBy: 'equipe', targetEntity: Phrases::class)]
    private ?Collection $phrases;

    #[ORM\ManyToMany(targetEntity: Jures::class, mappedBy: 'equipe')]
    private ?Collection $jures;

    #[ORM\OneToMany(targetEntity: Attributions::class, mappedBy: 'equipe')]
    private ?Collection $attributions = null;

    public function __toString(): string
    {
        return $this->getEquipeinter()->getLettre() . ' - ' . $this->getEquipeinter()->getTitreProjet();

    }

    public function __construct()
    {
        $this->notess = new ArrayCollection();
        $this->phrases = new ArrayCollection();
        $this->jures = new ArrayCollection();
        $this->attributions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipeinter(): ?Equipesadmin
    {
        return $this->equipeinter;
    }

    public function setEquipeinter(?Equipesadmin $equipeinter): self
    {
        $this->equipeinter = $equipeinter;

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

    public function getSalle(): ?string
    {
        return $this->salle;
    }

    public function setSalle(?string $salle): self
    {
        $this->salle = $salle;

        return $this;
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

    public function getClassement(): ?string
    {
        return $this->classement;
    }

    public function setClassement(?string $classement): self
    {
        $this->classement = $classement;

        return $this;
    }

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(?int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }

    public function getCouleur(): ?int
    {
        return $this->couleur;
    }

    public function setCouleur(?int $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getVisite(): ?Visites
    {
        return $this->visite;
    }

    public function setVisite(?Visites $visite): self
    {


        $this->visite = $visite;

        return $this;
    }

    public function getCadeau(): ?Cadeaux
    {
        return $this->cadeau;
    }

    public function setCadeau(?Cadeaux $cadeau): self
    {

        $this->cadeau = $cadeau;

        return $this;
    }

    public function getPrix(): ?Prix
    {
        return $this->prix;
    }

    public function setPrix(?Prix $prix): self
    {

        $this->prix = $prix;

        return $this;
    }

    public function getNbNotes(): ?int
    {
        return $this->nbNotes;
    }

    public function setNbNotes(?int $nbNotes): self
    {
        $this->nbNotes = $nbNotes;

        return $this;
    }

    public function getObservateur(): ?user
    {
        return $this->observateur;
    }

    public function setObservateur(?user $observateur): self
    {
        $this->observateur = $observateur;

        return $this;
    }

    /**
     * @return Collection<int, Notes>
     */
    public function getNotess(): Collection
    {
        return $this->notess;
    }

    public function addNotess(Notes $notess): self
    {
        if (!$this->notess->contains($notess)) {
            $this->notess->add($notess);
            $notess->setEquipenat($this);
        }

        return $this;
    }

    public function removeNotess(Notes $notess): self
    {
        if ($this->notess->removeElement($notess)) {
            // set the owning side to null (unless already changed)
            if ($notess->getEquipenat() === $this) {
                $notess->setEquipenat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Phrases>
     */
    public function getPhrases(): Collection
    {
        return $this->phrases;
    }

    public function addPhrase(Phrases $phrase): self
    {
        if (!$this->phrases->contains($phrase)) {
            $this->phrases->add($phrase);
            $phrase->setEquipe($this);
        }

        return $this;
    }

    public function removePhrase(Phrases $phrase): self
    {
        if ($this->phrases->removeElement($phrase)) {
            // set the owning side to null (unless already changed)
            if ($phrase->getEquipe() === $this) {
                $phrase->setEquipe(null);
            }
        }

        return $this;
    }

    public function getAttrib($jure): ?int
    {

        $jures = $this->getJures();
        $attribs = [];
        $attrib = null;
        if ($jure instanceof $jures) {
            $attribs = $jure->getAttributions();
            $attrib = 0;
            if (in_array($this->id, $attribs)) {

                $attrib = 1;
            }
        }
        return $attrib;


    }

    /**
     * @return Collection<int, Jures>
     */
    public function getJures(): Collection
    {
        return $this->jures;
    }

    public function addJure(Jures $jure): self
    {
        if (!$this->jures->contains($jure)) {
            $this->jures->add($jure);
            $jure->addEquipesnat($this);
        }

        return $this;
    }

    public function removeJure(Jures $jure): self
    {
        if ($this->jures->removeElement($jure)) {
            $jure->removeEquipesnat($this);
        }

        return $this;
    }

    public function getAttributions(): ?Collection
    {
        return $this->attributions;
    }

    public function addAttribution(?Attributions $attribution): self
    {
        // unset the owning side of the relation if necessary
        if (!$this->attributions->contains($attribution)) {
            $this->attributions->add($attribution);
            $attribution->addEquipe($this);
        }

        return $this;
    }

    public function removeAttribution(Attributions $attribution): self
    {
        if ($this->attributions->removeElement($attribution)) {
            $attribution->removeEquipe($this);
        }

        return $this;
    }

    public function getLettre(): string
    {
        return $this->equipeinter->getLettre();
    }

}
