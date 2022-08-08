<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Centrescia
 *
 * @ORM\Table(name="videosequipes")
 * @ORM\Entity(repositoryClass="App\Repository\VideosequipesRepository")
 *
 */
class Videosequipes
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @Assert\Url(
     *    message = "L\'url '{{ value }}' n'est pas valide",
     *  )
     * @ORM\Column(name="lien", type="string")
     */
    private ?string $lien;
    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Edition")
     * @ORM\JoinColumn(name="edition_id",  referencedColumnName="id",onDelete="CASCADE" )
     */
    private edition $edition;

    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipesadmin")
     * @ORM\JoinColumn(name="equipe_id",  referencedColumnName="id",onDelete="CASCADE" )
     */
    private ?Equipesadmin $equipe;

    /**
     *
     * @ORM\Column(name="nom", type="string", nullable=true)
     */
    private ?string $nom = null;

    /**
     *
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
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