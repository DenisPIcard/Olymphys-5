<?php

namespace App\Entity;

use App\Repository\VideosequipesRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



 #[ORM\Entity(repositoryClass: VideosequipesRepository::class)]

class Videosequipes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name:"lien", type:"string", nullable: true)]
    private ?string $lien = null;

    #[ORM\ManyToOne(targetEntity :Edition::class)]
    #[ORM\JoinColumn(name: "edition_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private edition $edition;

   #[ORM\ManyToOne(targetEntity: Equipesadmin::class)]
   #[ORM\JoinColumn(name:"equipe_id",  referencedColumnName:"id",onDelete:"CASCADE" )]
    private ?Equipesadmin $equipe =null ;

   #[ORM\Column(length : 255 , nullable:true)]
    private ?string $nom = null;

    #[ORM\Column(nullable:true)]
    private ?DateTime $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEdition(): ?edition
    {
        return $this->edition;
    }

    public function setEdition($edition): Videosequipes
    {
        $this->edition = $edition;
        return $this;
    }

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe($equipe): Videosequipes
    {
        $this->equipe = $equipe;
        return $this;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien($lien): Videosequipes
    {
        $this->lien = $lien;
        $this->updatedAt = new DateTime('now');
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom($nom): Videosequipes
    {
        $this->nom = $nom;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


}