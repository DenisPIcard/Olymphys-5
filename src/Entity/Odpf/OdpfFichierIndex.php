<?php

namespace App\Entity\Odpf;

use App\Repository\Odpf\OdpfFichierIndexRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OdpfFichierIndexRepository::class)]
class OdpfFichierIndex
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motClef = null;

    #[ORM\OneToMany(mappedBy: 'OdpfFichierpasses', targetEntity: OdpfFichierspasses::class)]
    private Collection $fichiers;

    public function __construct()
    {
        $this->fichiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotClef(): ?string
    {
        return $this->motClef;
    }

    public function setMotClef(?string $motClef): static
    {
        $this->motClef = $motClef;

        return $this;
    }

    /**
     * @return Collection<int, OdpfFichierspasses>
     */
    public function getFichiers(): Collection
    {
        return $this->fichiers;
    }

    public function addFichier(OdpfFichierspasses $fichier): static
    {
        if (!$this->fichiers->contains($fichier)) {
            $this->fichiers->add($fichier);
            $fichier->setOdpfFichierIndex($this);
        }

        return $this;
    }

    public function removeFichier(OdpfFichierspasses $fichier): static
    {
        if ($this->fichiers->removeElement($fichier)) {
            // set the owning side to null (unless already changed)
            if ($fichier->getOdpfFichierIndex() === $this) {
                $fichier->setOdpfFichierIndex(null);
            }
        }

        return $this;
    }
}
