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
     * @var string
     * @ORM\Column(name="name", type = "string")
     */
    private $name;

    /**
     * @var string
     * @ORM\ManyToOne(targetEntity="App\Entity\Centrescia")
     * @ORM\JoinColumn(name ="centre_id", referencedColumnName = "id", nullable=true)
     */
    private $centre;

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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get centre
     *
     * @return string
     */
    public function getCentre()
    {
        return $this->centre;
    }

    /**
     * Get name
     * @param String $name
     *
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set centre
     * @param String $centre
     *
     */
    public function setCentre($centre)
    {
        $this->centre = $centre;
        return $this;
    }


}