<?php

namespace App\Entity\Odpf;

use App\Entity\Photos;
use App\Repository\Odpf\OdpfEditionsPasseesRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass:OdpfEditionsPasseesRepository::class)]
class OdpfEditionsPassees
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?int $edition = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $annee = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $pseudo = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $lieu = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $ville = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $dateCia = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $dateCn = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $dateinscription = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $nomParrain = null;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $titreParrain = null;

    #[ORM\OneToMany(targetEntity:OdpfEquipesPassees::class, mappedBy:"odpfEditionsPassees")]
    private Collection $odpfEquipesPassees;

    #[ORM\Column( length:255, nullable:true)]
    private ?string $photoParrain = null;


    #[ORM\Column( length:255, nullable:true)]
    private $lienparrain; //c'est l'université ou la structure où travaille le parrain

    #[ORM\Column( length:255, nullable:true)]
    private $affiche;


    public function __construct()
    {
        $this->odpfEquipesPassees = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }


    public function __toString(): string
    {
        $numEd = strval($this->edition);
        if (strlen(strval($this->edition)) == 1) {

            $numEd = '0' . strval($this->edition);

        }
        return $numEd;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEdition(): ?int
    {
        return $this->edition;
    }

    public function setEdition(?int $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(?string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getNomParrain(): ?string
    {
        return $this->nomParrain;
    }

    public function setNomParrain(?string $nomParrain): self
    {
        $this->nomParrain = $nomParrain;

        return $this;
    }

    public function getTitreParrain(): ?string
    {
        return $this->titreParrain;
    }

    public function setTitreParrain(?string $titreParrain): self
    {
        $this->titreParrain = $titreParrain;

        return $this;
    }

    public function getOdpfEquipesPassees(): ?Collection
    {
        return $this->odpfEquipesPassees;
    }

    public function addOdpfEquipesPassee(?OdpfEquipesPassees $odpfEquipesPassee): self
    {
        if (!$this->odpfEquipesPassees->contains($odpfEquipesPassee)) {
            $this->odpfEquipesPassees[] = $odpfEquipesPassee;
            $odpfEquipesPassee->setEditionspassees($this);
        }

        return $this;
    }

    public function removeOdpfEquipesPassee(OdpfEquipesPassees $odpfEquipesPassee): self
    {
        if ($this->odpfEquipesPassees->removeElement($odpfEquipesPassee)) {
            // set the owning side to null (unless already changed)
            if ($odpfEquipesPassee->getEditionspassees() === $this) {
                $odpfEquipesPassee->setEditionspassees(null);
            }
        }

        return $this;
    }

    public function setDateinscription(?string $dateinscription): self
    {
        $this->dateinscription = $dateinscription;

        return $this;
    }

    public function setDateCia(?string $datecia): self
    {
        $this->dateCia = $datecia;

        return $this;
    }

    public function getDateinscription(): string
    {
        return $this->dateinscription;
    }

    public function getDateCia(): string
    {
        return $this->dateCia;
    }

    public function getDateCn(): string
    {
        return $this->dateCn;
    }

    public function setDateCn(?string $datecn): self
    {
        $this->dateCn = $datecn;

        return $this;
    }

    public function getPhotoParrain(): ?string
    {
        return $this->photoParrain;
    }

    public function setPhotoParrain(?string $photoParrain): self
    {
        $this->photoParrain = $photoParrain;

        return $this;
    }


    public function getLienparrain(): ?string
    {
        return $this->lienparrain;
    }

    public function setLienparrain(?string $lienparrain): self
    {
        $this->lienparrain = $lienparrain;

        return $this;
    }

    public function getAffiche(): ?string
    {
        return $this->affiche;
    }

    public function setAffiche(?string $affiche): self
    {
        $this->affiche = $affiche;

        return $this;
    }


}
