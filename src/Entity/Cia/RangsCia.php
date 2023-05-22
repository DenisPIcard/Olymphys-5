<?php

namespace App\Entity\Cia;

use App\Entity\Equipesadmin;
use App\Repository\Cia\RangsCiaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RangsCiaRepository::class)]
class RangsCia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?Equipesadmin $equipe = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $rang = null;

    #[ORM\Column(nullable: true)]
    private ?int $points = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(?int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;

        return $this;
    }
}
