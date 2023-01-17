<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * Orgacia
 *
 * @ORM\Table(name="orgacia")
 * @ORM\Entity(repositoryClass="App\Repository\OrgaciaRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Orgacia
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Centrescia")
     * @ORM\JoinColumn(name ="centre_id", referencedColumnName = "id", nullable=true)
     */
    private ?Centrescia $centre;
    /**
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name ="user_id", referencedColumnName = "id", nullable=true)
     */
    private ?User $user;


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
     * Set centre
     * @param User $user
     *
     */
    public function setUser(?User $user) :self
    {
        $this->user = $user;
        return $this;
    }
    /**
     * Get centre
     *
     * @return User
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

}