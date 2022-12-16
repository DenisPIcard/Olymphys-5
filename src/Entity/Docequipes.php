<?php

namespace App\Entity;

use App\Repository\DocequipesRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass=DocequipesRepository::class)
 */
class Docequipes
{
    /**
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $fichier = null;

    /**
     *
     * @var File
     * @Vich\UploadableField(mapping="docequipes", fileNameProperty="fichier")
     */
    private ?File $fichierFile = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private ?string $type = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $titre = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier): self
    {
        $this->fichier = $fichier;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFichierFile(): ?File
    {

        return $this->fichierFile;
    }

    public function setFichierFile(?File $fichierFile)

    {

        if ($this->fichierFile instanceof UploadedFile) {
            $this->updatedAt = new DateTime('now');
        }
        $this->fichierFile = $fichierFile;

    }
}