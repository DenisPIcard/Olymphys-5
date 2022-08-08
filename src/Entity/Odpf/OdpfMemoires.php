<?php

namespace App\Entity\Odpf;

use App\Entity\Odpf\OdpfEquipesPassees;
use App\Repository\Odpf\OdpfMemoiresRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * opdfmemoires
 * @Vich\Uploadable
 * @ORM\Table(name="odpf_memoires")
 * @ORM\Entity(repositoryClass=OdpfMemoiresRepository::class)
 */
class OdpfMemoires
{
    /**
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomfichier;

    /**
     *
     * @var File
     * @Vich\UploadableField(mapping="odpfmemoires", fileNameProperty="nomfichier")
     *
     *
     */
    private $fichier;

    /**
     * @ORM\ManyToOne(targetEntity=OdpfEquipesPassees::class)
     */
    private ?OdpfEquipesPassees $equipe;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNomfichier(): ?string
    {
        return $this->nomfichier;
    }

    public function setNomfichier(string $nomfichier): self
    {
        $this->nomfichier = $nomfichier;

        return $this;
    }

    public function getFichier(): ?File
    {
        return $this->fichier;
    }

    public function setFichier(File $fichier): void
    {
        $this->fichier = $fichier;
        if ($this->fichier instanceof UploadedFile) {
            $this->updatedAt = new \DateTime('now');
        }

    }

    public function getEquipe(): ?OdpfEquipesPassees
    {
        return $this->equipe;
    }

    public function setEquipe(?OdpfEquipesPassees $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function personalNamer(): string
    {

        $equipe = $this->getEquipe();
        $edition = $equipe->getEdition()->getEdition();
        if ($equipe) {
            $lettre = $equipe->getLettre();
            $libel_equipe = $lettre;
            if ($equipe->getSelectionnee() == false) {

                $libel_equipe = $equipe->getNumero();
            }
            $nom_equipe = $equipe->getTitreProjet();
        } else {
            $libel_equipe = 'prof';

        }
        if ($this->getType() == 0) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-memoire-' . $nom_equipe;
        }
        if ($this->getType() == 1) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Annexe';
        }
        if ($this->getType() == 2) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Resume-' . $nom_equipe;

        }


        if ($this->getType() == 3) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Presentation-' . $nom_equipe;
        }


        return $fileName;
    }


    public function directoryName(): string
    {
        $path = '/' . $this->equipe->getEdition()->getEdition() . '/memoires/';

        return $path;

    }
}
