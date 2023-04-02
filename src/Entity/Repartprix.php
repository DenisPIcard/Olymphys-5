<?php

namespace App\Entity;

use App\Repository\RepartprixRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepartprixRepository::class)]
class Repartprix
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[ORM\Column(length: 255, nullable: true)]
   private ?string $niveau = null;

    #[ORM\Column(type: Types::SMALLINT, nullable:false)]
    private int $nbreprix = 0;


    public function getId(): int
    {
        return $this->id;
    }


    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): Repartprix
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getNbreprix(): int
    {
        return $this->nbreprix;
    }

    public function setNbreprix(int $nbreprix): Repartprix
    {
        $this->nbreprix = $nbreprix;

        return $this;
    }

}