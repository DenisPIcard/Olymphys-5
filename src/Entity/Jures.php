<?php

namespace App\Entity;

use App\Repository\JuresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: JuresRepository::class)]
class Jures
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    private ?User $iduser;

    #[ORM\Column(length: 255, nullable: true, name: 'prenomJure')]
    private ?string $prenomJure = null;

    #[ORM\Column(length: 255, nullable: true, name: 'nomJure')]
    private ?string $nomJure = null;

    #[ORM\Column(length: 255, nullable: true, name: 'initialesJure')]
    private ?string $initialesJure = null;


    #[ORM\OneToMany(targetEntity: Notes::class, mappedBy: "jure")]
    private ?Collection $notesj;

    #[ORM\OneToMany(targetEntity: Phrases::class, mappedBy: "jure")]
    private ?Collection $phrases;

    #[ORM\ManyToMany(targetEntity: Attributions::class, inversedBy: 'jure', cascade: ['persist', 'remove'])]
    private ?Collection $attributions;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notesj = new ArrayCollection();
        $this->phrases = new ArrayCollection();
        $this->attributions = new ArrayCollection();
        $this->Equipesnat = new ArrayCollection();


    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get initialesJure
     *
     * @return string
     */
    public function getInitialesJure(): ?string
    {
        return $this->initialesJure;
    }

    /**
     * Set initialesJure
     *
     * @param string $initialesJure
     *
     * @return Jures
     */
    public function setInitialesJure(string $initialesJure): Jures
    {
        $this->initialesJure = $initialesJure;

        return $this;
    }


    /**
     * Add notesj
     *
     * @param Notes $notesj
     *
     * @return Jures
     */
    public function addNotesj(Notes $notesj): ?Jures
    {
        $this->notesj[] = $notesj;

        //On relie l'équipe à "une ligne note"
        $notesj->setJure($this);

        return $this;
    }

    /**
     * Get notesj
     *
     * @return Collection
     */
    public function getNotesj()
    {
        return $this->notesj;
    }

    public function getNom(): string
    {
        return $this->getNomJure() . ' ' . $this->getPrenomJure();
    }

    /**
     * Get nomJure
     *
     * @return string
     */
    public function getNomJure(): ?string
    {
        return $this->nomJure;
    }

    /**
     * Set nomJure
     *
     * @param string $nomJure
     *
     * @return Jures
     */
    public function setNomJure(?string $nomJure): Jures
    {
        $this->nomJure = $nomJure;

        return $this;
    }

    /**
     * Get prenomJure
     *
     * @return string
     */
    public function getPrenomJure(): ?string
    {
        return $this->prenomJure;
    }

    /**
     * Set prenomJure
     *
     * @param string $prenomJure
     *
     * @return Jures
     */
    public function setPrenomJure(?string $prenomJure): Jures
    {
        $this->prenomJure = $prenomJure;

        return $this;
    }

    public function getIduser(): ?user
    {
        return $this->iduser;
    }

    public function setIduser(?user $iduser): self
    {
        $this->iduser = $iduser;

        return $this;
    }

    /**
     * @return Collection<int, phrases>
     */
    public function getPhrases(): Collection
    {
        return $this->phrases;
    }

    public function addPhrase(?phrases $phrase): self
    {
        if (!$this->phrases->contains($phrase)) {
            $this->phrases[] = $phrase;
            $phrase->setJure($this);
        }

        return $this;
    }

    public function removePhrase(phrases $phrase): self
    {
        if ($this->phrases->removeElement($phrase)) {
            // set the owning side to null (unless already changed)
            if ($phrase->getJure() === $this) {
                $phrase->setJure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Attributions>
     */
    public function getAttributions(): ?Collection
    {
        return $this->attributions;
    }

    public function addAttribution(Attributions $attribution): self
    {
        if (!$this->attributions->contains($attribution)) {
            $this->attributions->add($attribution);
        }

        return $this;
    }

    public function removeAttribution(Attributions $attribution): self
    {
        $this->attributions->removeElement($attribution);

        return $this;
    }


}