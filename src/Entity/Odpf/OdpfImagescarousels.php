<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfImagescarouselsRepository;
use App\Service\FichierNamer;
use App\Service\ImagesCreateThumbs;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 *OdpfImagescarousels
 * @ORM\Table(name="odpf_imagescarousels")
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass=OdpfImagescarouselsRepository::class)
 *
 */
class OdpfImagescarousels
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTime $createdAt;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $coment;

    /**
     *
     *
     * @Vich\UploadableField(mapping="odpfImagescarousels", fileNameProperty="name")
     * @var File
     */
    private ?File $imageFile = null;

    /**
     * @ORM\ManyToOne(targetEntity=OdpfCarousels::class, inversedBy="images")
     */
    private ?Odpfcarousels $carousel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $numero = null;


    public function __construct()
    {
        $this->createdAt = new DateTime('now');

    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getComent(): ?string
    {
        return $this->coment;
    }

    public function setComent(?string $coment): self
    {
        $this->coment = $coment;

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param File|UploadedFile $imageFile
     */
    public function setImageFile(?File $imageFile): void
    {
        if ($imageFile !== null) {
            $this->imageFile = $imageFile;

            if ($this->imageFile instanceof UploadedFile) {
                $this->updatedAt = new DateTime('now');
            }
            //$this->createThumbs();
        }

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost

    }

    public function personalNamer(): string
    {
        $uploadTime = new datetime('now');
        $time = $uploadTime->format('y-m-d_H-i-s');

        return 'carousel-' . $this->carousel->getName() . '-' . 'diapo' . $this->numero . '_' . $time;
    }

    public function createThumbs(): OdpfImagescarousels
    {

        $imagesCreateThumbs = new ImagesCreateThumbs();
        $imagesCreateThumbs->createThumbs($this);;
        return $this;

    }

    public function getCarousel(): ?Odpfcarousels
    {
        return $this->carousel;
    }

    public function setCarousel(?Odpfcarousels $carousel): self
    {
        $this->carousel = $carousel;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }
}
