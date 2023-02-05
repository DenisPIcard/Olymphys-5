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

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get niveau
     *
     * @return string|null
     */
    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    /**
     * Set niveau
     *
     * @param string $niveau
     *
     * @return Repartprix
     */
    public function setNiveau(string $niveau): Repartprix
    {
        $this->niveau = $niveau;

        return $this;
    }


    /**
     * Get nbreprix
     *
     * @return integer
     */
    public function getNbreprix(): int
    {
        return $this->nbreprix;
    }

    /**
     * Set nbreprix
     *
     * @param integer $nbreprix
     *
     * @return Repartprix
     */
    public function setNbreprix(int $nbreprix): Repartprix
    {
        $this->nbreprix = $nbreprix;

        return $this;
    }

}