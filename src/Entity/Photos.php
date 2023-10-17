<?php

namespace App\Entity;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Repository\PhotosRepository;
use App\Service\ImagesCreateThumbs;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass:PhotosRepository::class)]
#[Vich\Uploadable]
class Photos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id=null;

    #[ORM\ManyToOne(targetEntity:Equipesadmin::class)]
    #[ORM\JoinColumn(name: "equipe_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private ?Equipesadmin $equipe = null;

    #[ORM\Column(length:255,  nullable:true)]
    private ?string $photo = null;

    #[Vich\UploadableField(mapping: 'photos', fileNameProperty: 'photo')]
    private ?File $photoFile = null;


    #[ORM\Column(length:255,  nullable:true)]
    private $coment = null;

    #[ORM\Column(nullable:true)]
    private ?bool $national = null ;


   #[ORM\Column(nullable:true)]
   private ?DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity:Edition::class)]
    #[ORM\JoinColumn(name:"edition_id",  referencedColumnName:"id",nullable:true)]
    private ?Edition $edition=null;

    #[ORM\ManyToOne(targetEntity:OdpfEditionsPassees::class)]
    #[ORM\JoinColumn(name:"editionspassees_id",  referencedColumnName:"id",nullable:true)]
    private ?OdpfEditionsPassees $editionspassees;

    #[ORM\ManyToOne(targetEntity:OdpfEquipesPassees::class)]
    #[ORM\JoinColumn(name:"equipepassee_id",  referencedColumnName:"id",nullable:true)]
    private ?OdpfEquipesPassees $equipepassee;

    public function __construct()
    {
        $this->setUpdatedAt(new DateTime('now'));


    }

    public function getPhotoFile():?File
    {
        return $this->photoFile;
    }

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

    public function createThumbs()
    {
        $imagesCreateThumbs = new ImagesCreateThumbs();
        $imagesCreateThumbs->createThumbs($this);
        return $this;

    }

}

