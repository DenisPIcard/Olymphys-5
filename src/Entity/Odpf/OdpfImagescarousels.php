<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfImagescarouselsRepository;
use App\Service\ImagesCreateThumbs;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity]
#[Vich\Uploadable]
class OdpfImagescarousels
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(nullable: true)]
    private ?string $coment = null;

    #[Vich\UploadableField(mapping: 'odpfImagescarousels', fileNameProperty: 'name', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\ManyToOne(targetEntity: OdpfCarousels::class, inversedBy: 'images')]
    private ?Odpfcarousels $carousel;

    #[ORM\Column(nullable: true)]
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

    public function setCreatedAt(?DateTime $createdAt): self
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
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile): void
    {
        $this->imageFile = $imageFile;
        if ($imageFile !== null) {


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

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }
}
