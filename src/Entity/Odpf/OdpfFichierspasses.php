<?php

namespace App\Entity\Odpf;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Repository\Odpf\OdpfFichierspassesRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: OdpfFichierspassesRepository::class)]
class OdpfFichierspasses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OdpfEditionsPassees::class)]
    private ?OdpfEditionsPassees $editionspassees = null;

    #[ORM\ManyToOne]
    private ?\App\Entity\Odpf\OdpfEquipesPassees $equipepassee = null;

    #[ORM\Column(nullable: true)]
    private ?int $typefichier;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomfichier = null;

    #[Vich\UploadableField(mapping: "odpfFichierspasses", fileNameProperty: "nomfichier")]
    private ?File $fichierFile = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    public function __construct()
    {

        $this->updatedAt = new \datetime('now');

    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomautorisation;

    #[ORM\Column(nullable: true)]
    private ?bool $national;

    #[ORM\Column(nullable: true)]
    private ?bool $publie;

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


    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
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

    public function getPublie(): ?bool
    {
        return $this->publie;
    }

    public function setPublie(?bool $publie): self
    {

        $this->publie = $publie;

        return $this;
    }

    public function directoryName(): string
    {
        $path = $this->editionspassees->getEdition() . '/fichiers';
        if (($this->getTypefichier() == 0) or ($this->getTypefichier() == 1)) {
            $path = $path . '/memoires/';
            $this->publie == true ? $path = $path . 'publie/' : $path = $path . 'prive/';
        }

        if ($this->getTypefichier() == 2) {
            $path = $path . '/resumes/';
            $this->publie == true ? $path = $path . 'publie/' : $path = $path . 'prive/';
        }

        if ($this->getTypefichier() == 3) {
            $path = $path . '/presentation/';
            $this->publie == true ? $path = $path . 'publie/' : $path = $path . 'prive/';
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

    public function moveFile(): self//permet  de transférer les fichiers publiables vers le dossier publie après le concours national
    {
        $filesystem = new Filesystem();
        $pathTypeFichier = ['memoires', 'memoires', 'resumes', 'presentation', 'fichessecur', 'diaporamas', 'autorisations', 'questionnaires', 'fichessecur'];

        if ($this->getPublie() === true) {
            if (!file_exists('/odpf/odpf-archives/' . $this->getEditionspassees()->getEdition() . '/fichiers/' . $pathTypeFichier[$this->getTypefichier() <= 1 ? 0 : $this->getTypefichier()] . '/publie/' . $this->getNomFichier())) {

                $filesystem->rename('/odpf/odpf-archives/' . $this->getEditionspassees()->getEdition() . '/fichiers/' . $pathTypeFichier[$this->getTypefichier() <= 1 ? 0 : $this->getTypefichier()] . '/prive/' . $this->getNomFichier(),
                    '/odpf/odpf-archives/' . $this->getEditionspassees()->getEdition() . '/fichiers/' . $pathTypeFichier[$this->getTypefichier() <= 1 ? 0 : $this->getTypefichier()] . '/publie/' . $this->getNomFichier());

            }
        }
        if ($this->getPublie() === false) {
            if (!file_exists('/odpf/odpf-archives/' . $this->getEditionspassees()->getEdition() . '/fichiers/' . $pathTypeFichier[$this->getTypefichier() <= 1 ? 0 : $this->getTypefichier()] . '/prive/' . $this->getNomFichier())) {

                $filesystem->rename('/odpf/odpf-archives/' . $this->getEditionspassees()->getEdition() . '/fichiers/' . $pathTypeFichier[$this->getTypefichier() <= 1 ? 0 : $this->getTypefichier()] . '/publie/' . $this->getNomFichier(),
                    '/odpf/odpf-archives/' . $this->getEditionspassees()->getEdition() . '/fichiers/' . $pathTypeFichier[$this->getTypefichier() <= 1 ? 0 : $this->getTypefichier()] . '/pprive/' . $this->getNomFichier());

            }
        }
        return $this;
    }

}
