<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Memoires
 * @Vich\Uploadable
 * @ORM\Table(name="fichiersequipes")
 * @ORM\Entity(repositoryClass="App\Repository\FichiersequipesRepository")
 *
 */
class Fichiersequipes //extends BaseMedia
{


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipesadmin")
     * @ORM\JoinColumn(name="equipe_id",  referencedColumnName="id",onDelete="CASCADE" )
     */
    private ?Equipesadmin $equipe = null;

    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     */
    private ?string $fichier = null;


    /**
     *
     * @var File
     * @Vich\UploadableField(mapping="fichiersequipes", fileNameProperty="fichier")
     *
     *
     */
    private ?File $fichierFile = null;
    /**
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $typefichier = null;


    /**
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @var boolean
     */
    private ?bool $national = false;


    /**
     *
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt = null;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id",  referencedColumnName="id", nullable=true )
     */
    private ?user $prof = null;

    /**
     *
     *
     * @ORM\Column(type="string", length=255,  nullable=true, )
     */
    private ?string $nomautorisation = null;

    /**
     * @ORM\ManyToOne(targetEntity=Edition::class)
     */
    private ?Edition $edition = null;

    /**
     * @ORM\OneToOne(targetEntity=Elevesinter::class, inversedBy="autorisationphotos", cascade={"persist"})
     * @ORM\JoinColumn(name="eleve_id",  referencedColumnName="id", nullable=true)
     */
    private ?Elevesinter $eleve;


    public function getFichierFile(): ?File
    {

        return $this->fichierFile;
    }

    public function setFichierFile(?File $fichierFile)

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

    public function getFichier(): ?string
    {
        return $this->fichier;
    }

    public function setFichier(?string $fichier)
    {
        $this->fichier = $fichier;
        if ($fichier) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new DateTime('now');
        }
        /*  if ($this->typefichier==6){
              $citoyen=$this->getEleve();
              if (!$citoyen){
                  $citoyen=$this->getProf();
              }
             $citoyen->setAutorisationphotos($this);
          }
          return $this;*/
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function personalNamer(): string    //permet à easyadmin de renommer le fichier, ne peut pas être utilisé directement
    {

        $edition = $this->getEdition()->getEd();
        $equipe = $this->getEquipe();

        if ($equipe) {
            $lettre = $equipe->getLettre();
            $libel_equipe = $lettre;
            if ($this->getNational() == 0) {

                $libel_equipe = $equipe->getNumero();
            }
            $nom_equipe = $equipe->getTitreProjet();
            $slugger = new AsciiSlugger();
            $nom_equipe = $slugger->slug($nom_equipe)->toString();



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
        if ($this->getTypefichier() == 4) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Fichesecur-' . $nom_equipe;
        }

        if ($this->getTypefichier() == 3) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Presentation-' . $nom_equipe;
        }

        if ($this->getTypefichier() == 5) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-diaporama-' . $nom_equipe;
        }

        if ($this->getTypefichier() == 6) {
            $nom = $this->getNomautorisation();

            if ($this->getProf() !== null) {
                $libel_equipe = 'prof';
            }

            $fileName = $edition . '-eq-' . $libel_equipe . '-autorisation photos-' . $nom . '-' . uniqid();
        }
        if ($this->getTypefichier() == 7) {


            $fileName = $edition . '-eq-' . $libel_equipe . '-questionnaire equipe-' . $nom_equipe . '-' . uniqid();
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

    }

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipesadmin $equipe): Fichiersequipes
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getNational(): ?bool
    {
        return $this->national;
    }

    public function setNational(?bool $national): Fichiersequipes
    {
        $this->national = $national;

        return $this;
    }


    public function getTypefichier(): ?int
    {
        return $this->typefichier;
    }

    public function setTypefichier($typefichier)
    {
        $this->typefichier = $typefichier;
    }

    public function getNomautorisation(): ?string
    {
        return $this->nomautorisation;
    }

    public function setNomautorisation(?string $nom): Fichiersequipes
    {

        $slugger = new AsciiSlugger();
        $nom = $slugger->slug($nom)->toString();
        $this->nomautorisation = $nom;
        return $this;
    }

    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire.
     */
    public function refreshUpdated()
    {
        $this->setUpdated(new DateTime());
    }

    public function setUpdated($date): Fichiersequipes
    {
        $this->updated = $date;

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

    public function directoryName(): string
    {
        $path = $this->edition->getEd() . '/fichiers';
        if (($this->getTypefichier() == 0) or ($this->getTypefichier() == 1)) {
            $path = $path . '/memoires/';
        }

        if ($this->getTypefichier() == 2) {
            $path = $path . '/resumes/';
        }
        if ($this->getTypefichier() == 4) {
            $path = $path . '/fichessecur/';
        }
        if ($this->getTypefichier() == 3) {
            $path = $path . '/presentation/';
        }

        if ($this->getTypefichier() == 5) {
            $path = $path . '/diaporamas/';
        }
        if ($this->getTypefichier() == 6) {
            $path = $path . '/autorisations/';
        }
        if ($this->getTypefichier() == 7) {
            $path = $path . '/questionnaires/';
        }
        return $path;

    }

    public function getEleve(): ?Elevesinter
    {
        return $this->eleve;
    }

    public function setEleve($eleve)
    {
        $this->eleve = $eleve;
    }

    public function getProf(): ?user
    {
        return $this->prof;
    }

    public function setProf($prof)
    {
        $this->prof = $prof;
    }


    public function getInfoequipenat()
    {
        if ($this->getEquipe()->getSelectionnee() == TRUE) {

            $lettre = $this->getEquipe()->getLettre();

            if ($lettre) {
                $Lettre = $this->getEquipe()->getLettre();

                $nom_equipe = $this->getEquipe()->getTitreProjet();
                $ville = $this->getEquipe()->getRneId()->getCommune();

                $infoequipe = 'Eq ' . $Lettre . ' - ' . $nom_equipe . '-' . $ville;
            }
        }
        if (isset($infoequipe)) {
            return $infoequipe;
        }
    }


}