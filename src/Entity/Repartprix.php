<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Repartprix
 *
 * @ORM\Table(name="repartprix")
 * @ORM\Entity(repositoryClass="App\Repository\RepartprixRepository")
 */
class Repartprix
{

    /**
     * @var int|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="niveau", type="string", length=255, nullable=true)
     */
    private ?string $niveau = null;

    /**
     * @var int
     *
     * @ORM\Column(name="nbreprix", type="smallint", nullable=false)
     */
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