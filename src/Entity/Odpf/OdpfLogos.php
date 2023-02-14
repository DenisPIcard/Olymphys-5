<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfLogosRepository;
use App\Service\ImagesCreateThumbs;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use ImagickException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: OdpfLogosRepository::class)]

class OdpfLogos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lien=null;

    #[ORM\Column(nullable:true)]
    private ?DateTime $updatedAt=null;

    #[ORM\Column(nullable:true)]
    private ?DateTime $createdAt=null;

    #[ORM\Column(nullable:true)]
    private ?bool $en_service = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $alt;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping:"odpfLogos", fileNameProperty:"image")]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type= null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $choix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $part = null;


    public function __construct()
    {
        $this->createdAt = new DateTime('now');

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEnService(): ?bool
    {
        return $this->en_service;
    }

    public function setEnService(bool $en_service): self
    {
        $this->en_service = $en_service;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getChoix(): ?string
    {
        return $this->choix;
    }

    public function setChoix(?string $choix): self
    {
        $this->choix = $choix;

        return $this;
    }

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(?string $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage($image): OdpfLogos
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void

    {
        $this->imageFile = $imageFile;

        if ($this->imageFile instanceof UploadedFile) {
            $this->updatedAt = new DateTime('now');
        }

    }

    public function personalNamer(): string
    {

        $ext = $this->getImageFile()->getExtension();
        return 'logo' . uniqid() . $ext;
    }

    /**
     * @throws ImagickException
     */
    public function createThumbs(): OdpfLogos
    {
        $imagesCreateThumbs = new ImagesCreateThumbs();
        $imagesCreateThumbs->createThumbs($this);
        return $this;

    }

    public function setLien(?string $lien): OdpfLogos
    {
        $this->lien = $lien;
        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

}


