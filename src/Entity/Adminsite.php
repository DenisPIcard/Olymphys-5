<?php
// src/Entity/Author.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Adminsite
 *
 * @ORM\Table(name="adminsite")
 * @ORM\Entity(repositoryClass="App\Repository\AdminsiteRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Adminsite
{
    /**
     * @var \datetime
     * @ORM\Column(name="datelimite_cia", type="datetime", nullable=true)
     */
    protected $datelimcia;
    /**
     * @var \datetime
     * @ORM\Column(name="datelimite_nat", type="datetime",nullable=true)
     */
    protected $datelimnat;
    /**
     * @var \datetime
     * @ORM\Column(name="concours_cia", type="datetime",nullable=true)
     */
    protected $concourscia;
    /**
     * @var \datetime
     * @ORM\Column(name="concours_cn", type="datetime",nullable=true)
     */
    protected $concourscn;
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
     * @ORM\Column(name="session", type="string", nullable=true)
     */
    private $session;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSession(): string
    {
        return $this->session;
    }

    public function setSession($session)
    {
        $this->requestStack = $session;
    }

    public function getDatelimcia()
    {
        return $this->datelimcia;
    }

    public function setDatelimcia($Date)
    {
        $this->datelimcia = $Date;
    }

    public function getDatelimnat()
    {
        return $this->datelimnat;
    }

    public function setDatelimnat($Date)
    {
        $this->datelimnat = $Date;
    }

    public function getConcourscia()
    {
        return $this->concourscia;
    }

    public function setConcourscia($Date)
    {
        $this->concourscia = $Date;
    }

    public function getConcourscn()
    {
        return $this->concourscn;
    }

    public function setConcourscn($Date)
    {
        $this->concourscn = $Date;
    }


}

