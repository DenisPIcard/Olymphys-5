<?php

namespace App\Entity;

use App\Repository\JuryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JuryRepository::class)
 */
class Jury
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=user::class, cascade={"persist", "remove"})
     */
    private $iduser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIduser(): ?user
    {
        return $this->iduser;
    }

    public function setIduser(?user $iduser): self
    {
        $this->iduser = $iduser;

        return $this;
    }
}
