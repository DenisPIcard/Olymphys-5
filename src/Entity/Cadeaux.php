<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cadeaux
 *
 * @ORM\Table(name="cadeaux")
 * @ORM\Entity(repositoryClass="App\Repository\CadeauxRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Cadeaux
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
     *
     * @ORM\Column(name="contenu", type="string", length=255, nullable=true)
     */
    private ?string $contenu = null;

    /**
     * @ORM\Column(name="fournisseur", type="string", length=255, nullable=true)
     */
    private ?string $fournisseur = null;
    /**
     *
     * @ORM\Column(name="montant", type="float", nullable=true)
     */
    private ?float $montant;
    /**
     * @var boolean
     *
     * @ORM\Column(name="attribue", type="boolean")
     */
    private bool $attribue;

    /**
     *
     * @ORM\Column(name="raccourci", type="string", length=255, nullable=true)
     */
    private ?string $raccourci = null;


    public function __toString()
    {

        return $this->contenu . '-' . $this->fournisseur;

    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get raccourci
     *
     * @return string
     */
    public function getRaccourci(): ?string
    {
        return $this->raccourci;
    }

    public function setRaccourci($raccourci): Cadeaux
    {
        $this->raccourci = $raccourci;

        return $this;
    }

    public function displayCadeau(): ?string
    {
        $var1 = $this->getContenu();
        $var2 = $this->getFournisseur();
        return $var1 . " offert par " . strtoupper($var2);
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     *
     * @return Cadeaux
     */
    public function setContenu(string $contenu): Cadeaux
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get fournisseur
     *
     * @return string
     */
    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    /**
     * Set fournisseur
     *
     * @param string $fournisseur
     *
     * @return Cadeaux
     */
    public function setFournisseur(string $fournisseur): Cadeaux
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    /**
     * Get montant
     *
     */
    public function getMontant(): ?float
    {
        return $this->montant;
    }

    /**
     * Set montant
     *
     * @param float $montant
     * @return Cadeaux
     */
    public function setMontant(float $montant): Cadeaux
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get attribue
     *
     * @return boolean
     */
    public function getAttribue(): ?bool
    {
        return $this->attribue;
    }

    /**
     * Set attribue
     *
     * @param boolean $attribue
     *
     * @return Cadeaux
     */
    public function setAttribue(bool $attribue): Cadeaux
    {
        $this->attribue = $attribue;

        return $this;
    }
}