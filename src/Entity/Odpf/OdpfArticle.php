<?php

namespace App\Entity\Odpf;

use DateTime;
use App\Repository\Odpf\OdpfArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OdpfArticleRepository::class)]
class OdpfArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $soustitre = null;

    #[ORM\ManyToOne]
    private ?OdpfCategorie $categorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt_image;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descr_image;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre_objectifs;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte_objectifs;

    #[ORM\ManyToOne]
    private ?OdpfCarousels $carousel = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt;

    #[ORM\Column(nullable: true)]
    private bool $publie;

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
        $this->publie = false;
    }

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

    public function getSoustitre(): ?string
    {
        return $this->soustitre;
    }

    public function setSoustitre(?string $soustitre): self
    {
        $this->soustitre = $soustitre;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): self
    {
        if ($texte) {
            $this->updatedAt = new DateTime('now');
        }
        $this->texte = $texte;

        return $this;
    }

    public function getTitreObjectifs(): ?string
    {
        return $this->titre_objectifs;
    }

    public function setTitreObjectifs(?string $titre_objectifs): self
    {
        $this->titre_objectifs = $titre_objectifs;

        return $this;
    }

    public function getTexteObjectifs(): ?string
    {
        return $this->texte_objectifs;
    }

    public function setTexteObjectifs(?string $texte_objectifs): self
    {
        $this->texte_objectifs = $texte_objectifs;

        return $this;
    }

    public function getCategorie(): ?OdpfCategorie
    {
        return $this->categorie;
    }

    public function setCategorie(?OdpfCategorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getAltImage(): ?string
    {
        return $this->alt_image;
    }

    public function setAltImage(?string $alt_image): self
    {
        $this->alt_image = $alt_image;

        return $this;
    }

    public function getDescrImage(): ?string
    {
        return $this->descr_image;
    }

    public function setDescrImage(?string $descr_image): self
    {
        $this->descr_image = $descr_image;

        return $this;
    }

    public function getCarousel(): ?odpfCarousels
    {
        return $this->carousel;
    }

    public function setCarousel(?odpfCarousels $carousel): self
    {
        $this->carousel = $carousel;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

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
    public function refreshUpdated()
    {
        $this->setUpdatedAt(new DateTime());
    }

    public function setPublie(bool $publie): self
    {
        $this->publie = $publie;
        return $this;
    }

    public function getPublie(): bool
    {
        return $this->publie;
    }
}
