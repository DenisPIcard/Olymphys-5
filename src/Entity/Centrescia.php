<?php

namespace App\Entity;

use App\Entity\Cia\JuresCia;
use App\Repository\CentresciaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;


#[ORM\Entity(repositoryClass: CentresciaRepository::class)]
class Centrescia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $centre = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieu = null;
    #[ORM\Column(nullable: true)]
    private ?bool $actif = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?int $edition = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbselectionnees = null;

    #[ORM\Column(nullable: true)]
    private ?bool $verouClassement = null;


    public function __toString()
    {
        return $this->centre;

    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getCentre(): ?string
    {
        return $this->centre;
    }

    public function setCentre($centre)
    {
        $this->centre = $centre;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
    }

    public function getEdition(): ?int
    {
        return $this->edition;
    }

    public function setEdition(?int $edition): Centrescia
    {
        $this->edition = $edition;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getNbselectionnees(): ?int
    {
        return $this->nbselectionnees;
    }

    public function setNbselectionnees(?int $nbselectionnees): self
    {
        $this->nbselectionnees = $nbselectionnees;

        return $this;
    }

    public function getVerouClassement(): ?bool
    {
        return $this->verouClassement;
    }

    public function setVerouClassement(?bool $verouClassement): static
    {

        $this->verouClassement = $verouClassement;

        return $this;
    }


}