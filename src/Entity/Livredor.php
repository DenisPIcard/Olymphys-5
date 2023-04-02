<?php

namespace App\Entity;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Repository\LivredorRepository;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass:LivredorRepository::class)]
class Livredor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type : 'text', nullable: true)]
    private ?string $texte = null;

    #[ORM\ManyToOne]
    private ?OdpfEditionsPassees $editionspassees = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categorie = null;

    #[ORM\ManyToOne]
    private User $user;

    #[ORM\OneToOne(targetEntity:Equipesadmin::class, cascade:["remove"])]
       private Equipesadmin $equipe;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(string $texte): self
    {
        $this->texte = $texte;

        return $this;
    }

    public function getEditionspassees(): ?OdpfEditionsPassees
    {
        return $this->editionspassees;
    }

    public function setEdition(?OdpfEditionsPassees $editionpassee): Livredor
    {
        $this->editionspassees = $editionpassee;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser($user): Livredor
    {
        $this->user = $user;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie($categorie): Livredor
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getEquipe(): ?equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe(?equipesadmin $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }
}