<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfDocumentsRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Odpf_documents
 * @ORM\Table(name="odpf_documents")
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass=OdpfDocumentsRepository::class)
 */
class OdpfDocuments
{
    /**
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
     * @var File
     * @Vich\UploadableField(mapping="odpfDocuments", fileNameProperty="fichier")
     *
     */
    private ?File $fichierFile = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private DateTime $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $titre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description;

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

    public function getFichierFile(): ?File
    {
        return $this->fichierFile;
    }

    public function setFichierFile(?File $fichierFile): self
    {

        if ($this->fichierFile instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }
        $this->fichierFile = $fichierFile;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($dateTime): OdpfDocuments
    {
        $this->updatedAt = $dateTime;

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

    public function getUpdatedAtString(): string
    {
        return $this->updatedAt->format('d-m-Y H:i:s');

    }
}
