<?php


namespace App\Entity;

use App\Repository\VisitesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitesRepository::class)]
class Visites
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[ORM\Column(length: 255, nullable: true)]
    private ?string $intitule = null;


   #[ORM\Column(nullable: true)]
    public ?bool $attribue = false;

    #[ORM\OneToOne( mappedBy: 'visite', cascade: ['persist', 'remove'])]
    private ?Equipes $equipe = null;

    public function __toString() : string
    {
        return $this->intitule;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set intitule
     *
     * @param string $intitule
     *
     * @return Visites
     */
    public function setIntitule(string $intitule): Visites
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string
     */
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }


    public function getAttribue(): ?bool
    {
        return $this->attribue;
    }

    public function setAttribue(?bool $attribue): self
    {
        $this->attribue = $attribue;

        return $this;
    }

    public function getEquipe(): ?Equipes
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipes $equipe): self
    {
        if ($equipe === null && $this->equipe !== null) {
            $this->equipe->setVisite(null);
            $this->attribue=false;
        }

        // set the owning side of the relation if necessary
        if ($equipe !== null && $equipe->getVisite() !== $this) {
            $equipe->setVisite($this);
            $this->attribue=true;
        }
        $this->equipe = $equipe;

        return $this;
    }


}


