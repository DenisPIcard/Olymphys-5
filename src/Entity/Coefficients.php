<?php

namespace App\Entity;

use App\Repository\CoefficientsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoefficientsRepository::class)]
class Coefficients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $demarche = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $oral = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $origin = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $wgroupe = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $ecrit = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $exper = 0;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $repquestions = 0;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemarche(): ?int
    {
        return $this->demarche;
    }

    public function setDemarche(string $demarche): self
    {
        $this->demarche = $demarche;

        return $this;
    }

    public function getOral(): ?int
    {
        return $this->oral;
    }

    public function setOral(string $oral): self
    {
        $this->oral = $oral;

        return $this;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getWgroupe(): ?int
    {
        return $this->wgroupe;
    }

    public function setWgroupe(string $wgroupe): self
    {
        $this->wgroupe = $wgroupe;

        return $this;
    }

    public function getEcrit(): ?int
    {
        return $this->ecrit;
    }

    public function setEcrit(string $ecrit): self
    {
        $this->ecrit = $ecrit;

        return $this;
    }

    public function getExper(): ?int
    {
        return $this->exper;
    }

    public function setExper(string $exper): self
    {
        $this->exper = $exper;

        return $this;
    }

    public function getRepquestions(): ?int
    {
        return $this->repquestions;
    }

    public function setRepquestions(string $repquestions): self
    {
        $this->repquestions = $repquestions;

        return $this;
    }

}

