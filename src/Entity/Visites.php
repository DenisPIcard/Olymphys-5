<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Visites
 *
 * @ORM\Table(name="visites")
 * @ORM\Entity(repositoryClass="App\Repository\VisitesRepository")
 */
class Visites
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="intitule", type="string", length=255, nullable=true)
     */
    private ?string $intitule = null;


    /**
     * @ORM\Column(name="attribue", type="boolean")
     */
    public bool $attribue = false;

    public function __toString()
    {

        return $this->intitule;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set intitule
     *
     * @param string $intitule
     *
     * @return Visites
     */
    public function setIntitule(string $intitule): Visites
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string
     */
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }


    public function getAttribue(): ?bool
    {
        return $this->attribue;
    }

    public function setAttribue(bool $attribue): self
    {
        $this->attribue = $attribue;

        return $this;
    }


}


