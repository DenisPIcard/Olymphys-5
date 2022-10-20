<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfPartenairesRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OdpfPartenairesRepository::class)
 */
class OdpfPartenaires
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $choix;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $titre;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $mecenes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $donateurs;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $visites;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $cadeaux;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $cia;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private DateTime $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChoix(): ?string
    {
        return $this->choix;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function setChoix(?string $choix): self
    {
        $this->choix = $choix;

        return $this;
    }

    public function getMecenes(): ?string
    {
        return $this->mecenes;
    }

    public function setMecenes(?string $mecenes): self
    {
        if ($mecenes) {
            $this->updatedAt = new DateTime('now');
        }
        $this->mecenes = $mecenes;

        return $this;
    }

    public function getDonateurs(): ?string
    {
        return $this->donateurs;
    }

    public function setDonateurs(?string $donateurs): self
    {
        if ($donateurs) {
            $this->updatedAt = new DateTime('now');
        }
        $this->donateurs = $donateurs;

        return $this;
    }

    public function getVisites(): ?string
    {
        return $this->visites;
    }

    public function setVisites(?string $visites): self
    {
        if ($visites) {
            $this->updatedAt = new DateTime('now');
        }
        $this->visites = $visites;

        return $this;
    }

    public function getCadeaux(): ?string
    {
        return $this->cadeaux;
    }

    public function setCadeaux(?string $cadeaux): self
    {
        if ($cadeaux) {
            $this->updatedAt = new DateTime('now');
        }
        $this->cadeaux = $cadeaux;

        return $this;
    }

    public function getCia(): ?string
    {
        return $this->cia;
    }

    public function setCia(?string $cia): self
    {
        if ($cia) {
            $this->updatedAt = new DateTime('now');
        }
        $this->cia = $cia;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire.
     */
    public function refreshUpdated(): void
    {
        $this->setUpdatedAt(new DateTime());
    }
}
