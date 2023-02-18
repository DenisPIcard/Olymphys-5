<?php

namespace App\Entity\Odpf;

use App\Entity\User;
use App\Repository\Odpf\OdpfFraisRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OdpfFraisRepository::class)]

class OdpfFrais
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    private ?User $iduser;

    #[ORM\Column(type : Types::DATETIME_MUTABLE)]
    private DateTime $createdAt;

    #[ORM\Column(type : Types::DATETIME_MUTABLE)]
    private DateTime $dateFrais;



    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDateFrais(): ?DateTime
    {
        return $this->dateFrais;
    }

    public function dateFrais(DateTime $dateFrais): self
    {
        $this->dateFrais = $dateFrais;

        return $this;
    }

}
