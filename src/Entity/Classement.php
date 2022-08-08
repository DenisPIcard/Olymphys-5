<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Classement
 *
 * @ORM\Table(name="classement")
 * @ORM\Entity(repositoryClass="App\Repository\ClassementRepository")
 */
class Classement
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="niveau", type="string", length=255, nullable=true)
     */
    private $niveau;

    /**
     * @var decimal
     *
     * @ORM\Column(name="montant", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $montant;

    /**
     * @var int
     *
     * @ORM\Column(name="nbreprix", type="smallint", nullable=false)
     */
    private $nbreprix;

    // les constantes de classe  
    const PREMIER = 1;
    const DEUXIEME = 2;
    const TROISIEME = 3;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set niveau
     *
     * @param integer $niveau
     *
     * @return Classement
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;

        return $this;
    }

    /**
     * Get niveau
     *
     * @return integer
     */
    public function getNiveau()
    {
        return $this->niveau;
    }

    /**
     * Set montant
     *
     * @param integer $montant
     *
     * @return Classement
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return decimal
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set nbreprix
     *
     * @param integer $nbreprix
     *
     * @return Classement
     */
    public function setNbreprix($nbreprix)
    {
        $this->nbreprix = $nbreprix;

        return $this;
    }

    /**
     * Get nbreprix
     *
     * @return integer
     */
    public function getNbreprix()
    {
        return $this->nbreprix;
    }
}
