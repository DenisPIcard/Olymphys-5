<?php

namespace App\Entity;

use App\Repository\FichiersequipesRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: FichiersequipesRepository::class)]
#[Vich\Uploadable]
class Fichiersequipes //extends BaseMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(cascade: ['remove'])]
    private ?Equipesadmin $equipe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fichier = null;

    #[Vich\UploadableField(mapping: 'fichiersequipes', fileNameProperty: 'fichier')]
    private ?File $fichierFile = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $typefichier = null;


    #[ORM\Column(nullable: true)]
    private ?bool $national = false;
    #[ORM\Column(nullable: true)]//nonpublie : 0, publie = 1; les memoires,annexes, résumés, présentations nationalespubliables seront validés à la fin du concours national
        //et transférés dans le répertoire publie, les autres fichiers :  CIA, autorisation, fiches sécurité sont par défaut non publiables
    private ?bool $publie = false;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'autorisationphotos', cascade: ['persist'])]
    #[ORM\Column(name: 'prof_id', nullable: true)]
    private ?User $prof = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomautorisation = null;

    #[ORM\ManyToOne]
    private ?Edition $edition = null;


    #[ORM\OneToOne(inversedBy: 'autorisationphotos', cascade: ['persist'])]
    private ?Elevesinter $eleve = null;


    public function getFichierFile(): ?File
    {

        return $this->fichierFile;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $fichierFile
     */
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
            $fileName = $edition . '-eq-' . $libel_equipe . '-Fichesecur-oral-' . $nom_equipe;
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
            $slugger = new AsciiSlugger();
            $fileName = $slugger->slug($edition . '-eq-' . $libel_equipe . '-autorisation photos-' . $nom . '-' . uniqid())->toString();
        }
        if ($this->getTypefichier() == 7) {


            $fileName = $edition . '-eq-' . $libel_equipe . '-questionnaire equipe-' . $nom_equipe . '-' . uniqid();
        }
        if ($this->getTypefichier() == 8) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Fichesecur-expo-' . $nom_equipe;
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

    public function getPublie(): ?bool
    {
        return $this->publie;
    }

    public function setPublie(?bool $publie)
    {
        $this->publie = $publie;
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
        $this->publie == 0 ? $acces = 'prive/' : $acces = 'publie/';
        if (($this->getTypefichier() == 0) or ($this->getTypefichier() == 1)) {
            $path = $path . '/memoires/' . $acces;
        }

        if ($this->getTypefichier() == 2) {
            $path = $path . '/resumes/' . $acces;
        }
        if (($this->getTypefichier() == 4) or ($this->getTypefichier() == 8)) {
            $path = $path . '/fichessecur/';
        }
        if ($this->getTypefichier() == 3) {
            $path = $path . '/presentation/' . $acces;
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
                $ville = $this->getEquipe()->getUaiId()->getCommune();

                $infoequipe = 'Eq ' . $Lettre . ' - ' . $nom_equipe . '-' . $ville;
            }
        }
        if (isset($infoequipe)) {
            return $infoequipe;
        }
    }


}