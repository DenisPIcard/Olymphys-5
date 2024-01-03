<?php

namespace App\Entity;

use App\Repository\AttributionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributionsRepository::class)]
class Attributions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Jures $jure = null;

    #[ORM\ManyToOne(cascade: ['persist'])]
    private ?Equipes $equipe = null;

    #[ORM\Column(nullable: true)]
    private ?int $estLecteur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJure(): ?Jures
    {
        return $this->jure;
    }

    public function setJure(?Jures $jure): self
    {
        $this->jure = $jure;

        return $this;
    }

    public function getEquipe(): ?Equipes
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipes $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getEstLecteur(): ?int
    {
        return $this->estLecteur;
    }

    public function setEstLecteur(?int $estLecteur): self
    {
        $this->estLecteur = $estLecteur;

        return $this;
    }
}
