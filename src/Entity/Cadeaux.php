<?php

namespace App\Entity;

use App\Repository\CadeauxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CadeauxRepository::class)]
class Cadeaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fournisseur = null;
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raccourci = null;

    #[ORM\OneToOne(mappedBy: 'cadeau', cascade: ['persist', 'remove'])]
    private ?Equipes $equipe = null;

    #[ORM\Column(nullable: true)]
    private ?bool $attribue = null;


    public function __toString()
    {

        return $this->contenu . '-' . $this->fournisseur . '-' . $this->getMontant() . ' €';

    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRaccourci(): ?string
    {
        return $this->raccourci;
    }

    public function setRaccourci($raccourci): Cadeaux
    {
        $this->raccourci = $raccourci;

        return $this;
    }

    public function displayCadeau(): ?string
    {
        $var1 = $this->getContenu();
        $var2 = $this->getFournisseur();
        return $var1 . " offert par " . strtoupper($var2);
    }


    public function getContenu(): ?string
    {
        return $this->contenu;
    }


    public function setContenu(string $contenu): Cadeaux
    {
        $this->contenu = $contenu;

        return $this;
    }


    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    public function setFournisseur(string $fournisseur): Cadeaux
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }


    public function setMontant(float $montant): Cadeaux
    {
        $this->montant = $montant;

        return $this;
    }

    public function getEquipe(): ?Equipes
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipes $equipe): self
    {
        if ($equipe === null && $this->equipe !== null) {
            $this->equipe->setCadeau(null);
            $this->attribue = false;
        }

        // set the owning side of the relation if necessary
        if ($equipe !== null && $equipe->getcadeau() !== $this) {
            $equipe->setCadeau($this);

        }
        if ($equipe !== null) {
            $this->attribue = true;
        }
        $this->equipe = $equipe;

        return $this;
    }

    public function isAttribue(): ?bool
    {
        return $this->attribue;
    }

    public function setAttribue(?bool $attribue): static
    {
        $this->attribue = $attribue;

        return $this;
    }
}