<?php

namespace App\Entity\Cia;

use App\Entity\Centrescia;
use App\Entity\Equipesadmin;
use App\Entity\User;
use App\Repository\JuresCiaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: JuresCiaRepository::class)]
class JuresCia
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

    #[ORM\Column(length: 3, nullable: true, name: 'initialesJure')]
    private ?string $initialesJure = null;

    #[ORM\OneToMany(targetEntity: NotesCia::class, mappedBy: "jure")]
    private ?Collection $notesj;

    #[ORM\ManyToMany(targetEntity: equipesadmin::class, inversedBy: 'juresCia')]
    private Collection $equipes;

    #[ORM\ManyToOne]
    private ?Centrescia $centrecia = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notesj = new ArrayCollection();
        $this->equipes = new ArrayCollection();

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
     * @return JuresCia
     */
    public function setInitialesJure(string $initialesJure): JuresCia
    {
        $this->initialesJure = $initialesJure;

        return $this;
    }


    /**
     * Add notesj
     *
     * @param NotesCia $notesj
     *
     * @return JuresCia
     */
    public function addNotesj(NotesCia $notesj): ?JuresCia
    {
        $this->notesj[] = $notesj;

        //On relie l'équipe à "une ligne note"
        $notesj->setJureCia($this);

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
     * @return JuresCia
     */
    public function setNomJure(?string $nomJure): JuresCia
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
     * @return JuresCia
     */
    public function setPrenomJure(?string $prenomJure): JuresCia
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
     * @return Collection<int, equipesadmin>
     */
    public function getEquipes(): Collection
    {
        return $this->equipes;
    }

    public function addEquipe(equipesadmin $equipe): self
    {
        if (!$this->equipes->contains($equipe)) {
            $this->equipes->add($equipe);
        }

        return $this;
    }

    public function removeEquipe(equipesadmin $equipe): self
    {
        $this->equipes->removeElement($equipe);

        return $this;
    }

    public function getCentrecia(): ?Centrescia
    {
        return $this->centrecia;
    }

    public function setCentrecia(?Centrescia $centrecia): self
    {
        $this->centrecia = $centrecia;

        return $this;
    }


}