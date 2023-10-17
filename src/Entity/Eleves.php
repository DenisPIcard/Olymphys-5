<?php

namespace App\Entity;

use App\Repository\ElevesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass : ElevesRepository::class)]

class Eleves
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id=null;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     */
    private $nom;
    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="classe", type="string", length=255, nullable=true)
     */
    private $classe;

    /**
     * @var string
     *
     * @ORM\Column(name="lettre_equipe", type="string", length=1, nullable=true)
     */
    private $lettre_equipe;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipesadmin")
     * @ORM\JoinColumn(name="equipe_id",  referencedColumnName="id" )
     *
     */
    private $equipe;

    /**
     * @ORM\OneToMany(targetEntity=Elevesinter::class, mappedBy="equipesel")
     */
    private $eleves;

    public function __construct()
    {
        $this->eleves = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Eleves
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Eleves
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set classe
     *
     * @param string $classe
     *
     * @return Eleves
     */
    public function setClasse($classe)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return string
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set lettreEquipe
     *
     * @param string $lettreEquipe
     *
     * @return Eleves
     */
    public function setLettreEquipe($lettreEquipe)
    {
        $this->lettreEquipe = $lettreEquipe;

        return $this;
    }

    /**
     * Get lettreEquipe
     *
     * @return string
     */
    public function getLettreEquipe()
    {
        return $this->lettreEquipe;
    }

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipesadmin $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }

    /**
     * @return Collection|Elevesinter[]
     */
    public function getEleves(): Collection
    {
        return $this->eleves;
    }

    public function addElefe(Elevesinter $elefe): self
    {
        if (!$this->eleves->contains($elefe)) {
            $this->eleves[] = $elefe;
            $elefe->setEquipesel($this);
        }

        return $this;
    }

    public function removeElefe(Elevesinter $elefe): self
    {
        if ($this->eleves->removeElement($elefe)) {
            // set the owning side to null (unless already changed)
            if ($elefe->getEquipesel() === $this) {
                $elefe->setEquipesel(null);
            }
        }

        return $this;
    }
}
