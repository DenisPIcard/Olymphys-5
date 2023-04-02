<?php

namespace App\Entity;

use App\Repository\LiaisonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiaisonRepository::class)]
class Liaison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
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