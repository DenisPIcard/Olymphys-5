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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="prix", type="string", length=255, nullable=true)
     */
    private ?string $prix = null;

    /**
     * @ORM\Column(name="niveau", type="string", length=255, nullable=true)
     */
    private ?string $niveau = null;


    /**
     * @var boolean
     *
     * @ORM\Column(name="attribue", type="boolean")
     */
    private ?bool $attribue = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $voix = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $intervenant = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $remisPar = null;

    /**
     * Get id
     *
     * @return string
     */
    public function __toString(): string
    {

        return $this->niveau . '-' . $this->prix;

    }

    public function getId(): ?int
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
    public function setPrix(string $prix): Prix
    {
        $this->prix = $prix;

        return $this;
    }


    public function getPrix(): ?string
    {
        return $this->prix;
    }

    /**
     * Set niveau
     *
     * @param string $niveau
     *
     * @return Prix
     */
    public function setNiveau(string $niveau): Prix
    {
        $this->niveau = $niveau;

        return $this;
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


    public function setAttribue(bool $attribue): Prix
    {
        $this->attribue = $attribue;

        return $this;
    }

    /**
     * Get attribue
     *
     * @return bool|null
     */
    public function getAttribue(): ?bool
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
    public function getIntervenant(): ?string
    {
        return $this->intervenant;
    }

    public function setIntervenant($intervenant): Prix
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