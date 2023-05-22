<?php

namespace App\Entity\Cia;

use App\Entity\Equipesadmin;
use App\Repository\Cia\ConseilsJuryCiaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ConseilsJuryCiaRepository::class)]
class ConseilsjuryCia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?JuresCia $jure = null;

    #[ORM\ManyToOne]
    private ?Equipesadmin $equipe = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJure(): ?JuresCia
    {
        return $this->jure;
    }

    public function setJure(?JuresCia $jure): self
    {
        $this->jure = $jure;

        return $this;
    }

    public function getEquipe(): ?Equipesadmin
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipesadmin $equipe): self
    {
        $this->equipe = $equipe;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): self
    {
        $this->texte = $texte;

        return $this;
    }
}
