<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Centrescia
 *
 * @ORM\Table(name="centrescia")
 * @ORM\Entity(repositoryClass="App\Repository\CentresciaRepository")
 *
 */
class Centrescia
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private ?string $centre = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $actif = false;
    /**
    * @ORM\Column(type="string", length=255, nullable = true)
    */
    private ?int $edition = null;

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


}