<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfVideosequipesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass :OdpfVideosequipesRepository::class)]

class OdpfVideosequipes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id= null;

    #[ORM\Column(length:255, nullable :true)]
    private $lien;

    #[ORM\ManyToOne(targetEntity:OdpfEquipesPassees::class)]
    private ?OdpfEquipesPassees $equipe;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(?string $lien): self
    {
        $this->lien = $lien;

        return $this;
    }

    public function getEquipe(): ?OdpfEquipesPassees
    {
        return $this->equipe;
    }

    public function setEquipe(?OdpfEquipesPassees $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }
}
