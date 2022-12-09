<?php

namespace App\Entity\Odpf;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Repository\Odpf\OdpfFichierspassesRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 * @ORM\Entity(repositoryClass=OdpfFichierspassesRepository::class)
 */
class OdpfFichierspasses
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=OdpfEditionsPassees::class)
     */
    private ?\App\Entity\Odpf\OdpfEditionsPassees $editionspassees;

    /**
     * @ORM\ManyToOne(targetEntity=OdpfEquipesPassees::class)
     */
    private ?\App\Entity\Odpf\OdpfEquipesPassees $equipepassee;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $typefichier;

    /**
     * @Groups({"elastica"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $nomfichier;

    /**
     *
     * @var File
     * @Vich\UploadableField(mapping="odpfFichierspasses", fileNameProperty="nomfichier")
     *
     *
     */
    private ?File $fichierFile = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {

        $this->updatedAt = new \datetime('now');

    }

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $nomautorisation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $national;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEditionspassees(): ?OdpfEditionsPassees
    {
        return $this->editionspassees;
    }

    public function setEditionspassees(?OdpfEditionsPassees $Editionpassee): self
    {
        $this->editionspassees = $Editionpassee;

        return $this;
    }

    public function getEquipepassee(): ?OdpfEquipesPassees
    {
        return $this->equipepassee;
    }

    public function setEquipepassee(?OdpfEquipesPassees $equipepassee): self
    {
        $this->equipepassee = $equipepassee;

        return $this;
    }

    public function getTypefichier(): ?int
    {
        return $this->typefichier;
    }

    public function setTypefichier(?int $typefichier): self
    {
        $this->typefichier = $typefichier;

        return $this;
    }

    public function getNomfichier(): ?string
    {
        return $this->nomfichier;
    }

    public function setNomfichier(?string $Nomfichier): self
    {
        $this->nomfichier = $Nomfichier;

        return $this;
    }

    public function getFichierFile(): ?File
    {

        return $this->fichierFile;
    }

    public function setFichierFile(?File $fichierFile = null)

    {

        //$nom=$this->getFichier();

        $this->fichierFile = $fichierFile;
        if ($this->fichierFile instanceof UploadedFile) {
            $this->updatedAt = new DateTime('now');
        }
        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        //$this->fichier=$nom;

    }


    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getNomautorisation(): ?string
    {
        return $this->nomautorisation;
    }

    public function setNomautorisation(?string $Nomautorisation): self
    {
        $this->nomautorisation = $Nomautorisation;

        return $this;
    }

    public function getNational(): ?bool
    {
        return $this->national;
    }

    public function setNational(?bool $national): self
    {
        $this->national = $national;

        return $this;
    }

    public function directoryName(): string
    {
        $path = $this->editionspassees->getEdition() . '/fichiers';
        if (($this->getTypefichier() == 0) or ($this->getTypefichier() == 1)) {
            $path = $path . '/memoires/';
        }

        if ($this->getTypefichier() == 2) {
            $path = $path . '/resumes/';
        }

        if ($this->getTypefichier() == 3) {
            $path = $path . '/presentation/';
        }


        if ($this->getTypefichier() == 6) {
            $path = $path . '/autorisations/';
        }

        return $path;

    }

    public function personalNamer(): string    //permet à easyadmin de renommer le fichier, ne peut pas être utilisé directement
    {

        $edition = $this->getEditionspassees()->getEdition();
        $equipe = $this->getEquipepassee();
        if ($this->getTypefichier() != 6) {
            if ($equipe) {

                if ($equipe->getLettre() === null) {

                    $libel_equipe = $equipe->getNumero();
                }
                if ($equipe->getLettre() !== null) {

                    $libel_equipe = $equipe->getLettre();
                }


                $nom_equipe = $equipe->getTitreProjet();
                $slugger = new AsciiSlugger();
                $nom_equipe = $slugger->slug($nom_equipe)->toString();

                //$nom_equipe= str_replace("'","",$nom_equipe);
                //$nom_equipe= str_replace("`","",$nom_equipe);

                //$nom_equipe= str_replace("?","",$nom_equipe);
            }
        }
        if ($this->getTypefichier() == 0) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-memoire-' . $nom_equipe;
        }
        if ($this->getTypefichier() == 1) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Annexe';
        }
        if ($this->getTypefichier() == 2) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Resume-' . $nom_equipe;

        }

        if ($this->getTypefichier() == 3) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Presentation-' . $nom_equipe;
        }


        if ($this->getTypefichier() == 6) {
            $nom = $this->getNomautorisation();
            if ($this->equipepassee === null) {
                $libel_equipe = 'prof';

            }

            $fileName = $edition . '-eq-' . $libel_equipe . '-autorisation photos-' . $nom . '-' . uniqid();

        }

        return $fileName;
    }

}
