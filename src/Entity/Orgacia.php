<?php

namespace App\Entity;


use App\Repository\OrgaciaRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: OrgaciaRepository::class)]

class Orgacia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id=null;



    #[ORM\ManyToOne(targetEntity: Centrescia::class)]
    private ?Centrescia $centre=null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;
    public function __toString():string
    {
        return $this->user->getNomPrenom().'('.$this->getCentre()->getCentre().') ';

    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }



    /**
     * Set centre
     * @param Centrescia $centre
     *
     */
    public function setCentre(?Centrescia $centre) :self
    {
        $this->centre = $centre;
        return $this;
    }
    /**
     * Get centre
     *
     * @return string
     */
    public function getCentre() : ?Centrescia
    {
        return $this->centre;
    }
    /**
     * Set user
     * @param User $user
     *
     */
    public function setUser(?User $user) :self
    {
        $this->user = $user;
        return $this;
    }
    /**
     * Get user
     *
     * @return User
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

}