<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Liaison
 *
 * @ORM\Table(name="liaison")
 * @ORM\Entity(repositoryClass="App\Repository\LiaisonRepository")
 */
class Liaison
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="liaison", type="string", length=255, nullable=true)
     */
    private ?string $liaison = null;


    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set liaison
     *
     * @param string $liaison
     *
     * @return Liaison
     */
    public function setLiaison(string $liaison): Liaison
    {
        $this->liaison = $liaison;

        return $this;
    }

    /**
     * Get liaison
     *
     * @return string
     */
    public function getLiaison(): ?string
    {
        return $this->liaison;
    }
}