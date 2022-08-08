<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfCarouselsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=OdpfCarouselsRepository::class)
 * @Vich\Uploadable
 */
class OdpfCarousels
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    private ?\DateTime $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $createdAt;

    /**
     * @ORM\Column(type="boolean", length=255)
     */
    private ?bool $blackbgnd = null;

    /**
     * @ORM\OneToMany(targetEntity=OdpfImagescarousels::class, mappedBy="carousel", cascade={"persist"})
     */
    private ?Collection $images;


    public function __toString()
    {

        return $this->name;

    }

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTime('now');
        $this->blackbgnd = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBlackbgnd(): ?bool
    {
        return $this->blackbgnd;
    }

    public function setBlackbgnd(?bool $black): self
    {
        $this->blackbgnd = $black;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getImages(): ?Collection
    {
        return $this->images;
    }

    public function addImage(?Odpfimagescarousels $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setCarousel($this);
        }

        return $this;
    }

    public function removeImage(Odpfimagescarousels $image): self
    {

        // set the owning side to null (unless already changed)
        if ($image->getCarousel() === $this) {
            if (file_exists('odpf/odpf-images/imagescarousels/' . $image->getName())) {
                unlink('odpf/odpf-images/imagescarousels/' . $image->getName());
            }

            $image->setCarousel(null);
        }


        return $this;
    }


}
