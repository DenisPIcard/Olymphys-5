<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Prix
 *
 * @ORM\Table(name="prix")
 * @ORM\Entity(repositoryClass="App\Repository\PrixRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Prix
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
     * @ORM\Column(name="prix", type="string", length=255, nullable=true)
     */
    private $prix;

    /**
     * @var string
     *
     * @ORM\Column(name="classement", type="string", length=255, nullable=true)
     */
    private $classement;

    // les constantes de classe 
    const PREMIER = 600; 
    const DEUXIEME = 400; 
    const TROISIEME = 200;

        /**
     * @var boolean
     *
     * @ORM\Column(name="attribue", type="boolean")
     */
    private $attribue;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $voix;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $intervenant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remisPar;
    
    /**
     * Get id
     *
     * @return int
     */
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set prix
     *
     * @param string $prix
     *
     * @return Prix
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return string
     */
    public function getPrix()
    {
        return $this->prix;
    }

    /**
     * Set classement
     *
     * @param string $classement
     *
     * @return Prix
     */
    public function setClassement($classement)
    {
        $this->classement = $classement;

        return $this;
    }

    /**
     * Get classement
     *
     * @return string
     */
    public function getClassement()
    {
        return $this->classement;
    }

        /**
     * Set attribue
     *
     * @param boolean $attribue
     *
     * @return Cadeaux
     */
    public function setAttribue($attribue)
    {
        $this->attribue = $attribue;

        return $this;
    }

    /**
     * Get attribue
     *
     * @return boolean
     */
    public function getAttribue()
    {
        return $this->attribue;
    }

    public function getVoix(): ?string
    {
        return $this->voix;
    }

    public function setVoix(?string $voix): self
    {
        $this->voix = $voix;

        return $this;
    }
    /**
     * Get intervenant
     *
     * 
     */
    public function getIntervenant()
    {
        return $this->intervenant;
    }
    /**
     * 
     */
    public function setIntervenant($intervenant)
    {
        $this->intervenant = $intervenant;

        return $this;
    }

    public function getRemisPar(): ?string
    {
        return $this->remisPar;
    }

    public function setRemisPar(?string $remisPar): self
    {
        $this->remisPar = $remisPar;

        return $this;
    }
}
