<?php

namespace App\Entity;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Service\ImagesCreateThumbs;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Photos
 * @Vich\Uploadable
 * @ORM\Table(name="photos")
 * @ORM\Entity(repositoryClass="App\Repository\PhotosRepository")
 *
 */
class Photos
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipesadmin")
     * @ORM\JoinColumn(name="equipe_id",  referencedColumnName="id",onDelete="CASCADE", nullable=true)
     */
    private ?Equipesadmin $equipe = null;

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     *
     * @var string
     */
    private ?string $photo;

    /**
     *
     * @var File
     * @Vich\UploadableField(mapping="photos", fileNameProperty="photo")
     *
     */
    private $photoFile;


    /**
     * @ORM\Column(type="string", length=125,  nullable=true)
     *
     * @var string
     */
    private $coment;

    /**
     * @ORM\Column(type="boolean",  nullable=true)
     *
     * @var boolean
     */
    private ?bool $national;


    /**
     *
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Edition::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Edition $edition;

    /**
     * @ORM\ManyToOne(targetEntity=OdpfEditionsPassees::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?OdpfEditionsPassees $editionspassees;

    /**
     * @ORM\ManyToOne(targetEntity=OdpfEquipesPassees::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?OdpfEquipesPassees $equipepassee;

    public function __construct()
    {
        $this->setUpdatedAt(new DateTime('now'));


    }

    public function getPhotoFile():?File
    {
        return $this->photoFile;
    }

    /**
     * @param File|UploadedFile $photoFile
     */
    public function setPhotoFile(?File $photoFile = null): void

    {
        $this->photoFile = $photoFile;
        if ($this->photoFile instanceof UploadedFile) {
            $this->updatedAt = new DateTime('now');
        }
        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost

    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function personalNamer()    //permet à vichuploeder et à easyadmin de renommer le fichier, ne peut pas être utilisé directement
    {
        $slugger = new AsciiSlugger();
        $ed = $this->getEditionspassees()->getEdition();
        $equipepassee = $this->getEquipepassee();
        $equipe = $this->getEquipe();
        $centre = ' ';
        $lettre_equipe = '';
        if ($equipe) {
            if ($equipe->getCentre()) {
                $centre = $equipe->getCentre()->getCentre() . '-eq';
            } else(
            $centre = 'CIA-eq'
            );

        }
        $numero_equipe = $equipepassee->getNumero();

        $nom_equipe = $equipepassee->getTitreProjet();

        $nom_equipe = $slugger->slug($nom_equipe)->toString();

        if ($this->getNational() == FALSE) {
            $fileName = $slugger->slug($ed . '-' . $centre . $numero_equipe . '-' . $nom_equipe . '.' . uniqid())->toString();
        }
        if ($this->getNational() == TRUE) {
            $equipepassee->getLettre() === null ? $idEquipe = $equipepassee->getNumero() : $idEquipe = $equipepassee->getLettre();

            $fileName = $ed . '-CN-eq-' . $idEquipe . '-' . $nom_equipe . '.' . uniqid();
        }


        return $fileName;
    }

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }

    public function setEdition(?Edition $edition)
    {
        $this->edition = $edition;
        return $this;
    }

    public function getEditionspassees(): ?OdpfEditionsPassees
    {
        return $this->editionspassees;
    }

    public function setEditionspassees(?OdpfEditionsPassees $editionspassees): self
    {
        $this->editionspassees = $editionspassees;

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

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipesadmin $equipe)
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getNational(): ?bool
    {
        return $this->national;
    }

    public function setNational(?bool $national)
    {
        $this->national = $national;
        return $this;
    }

    public function directoryName(): string
    {


        $path = $this->equipepassee->getEditionspassees()->getEdition() . '/photoseq/';


        return $path;
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire.
     */
    public function refreshUpdated()
    {
        $this->setUpdatedAt(new DateTime());
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($date)
    {
        $this->updatedAt = $date;

        return $this;
    }

    public function getComent()
    {
        return $this->coment;
    }

    public function setComent($coment)
    {
        $this->coment = $coment;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function createThumbs()
    {

        $imagesCreateThumbs = new ImagesCreateThumbs();
        $imagesCreateThumbs->createThumbs($this);
        return $this;

    }

}

