<?php

namespace App\Entity;

use App\Repository\JuresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: JuresRepository::class)]
class Jures
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    private ?User $iduser;

    #[ORM\Column(length: 255, nullable: true, name: 'prenomJure')]
    private ?string $prenomJure = null;

    #[ORM\Column(length: 255, nullable: true, name: 'nomJure')]
    private ?string $nomJure = null;

    #[ORM\Column(length: 255, nullable: true, name: 'initialesJure')]
    private ?string $initialesJure = null;

    #[ORM\Column(name: "A", type: Types::SMALLINT, nullable: true)]
    private ?int $a = 0;

    #[ORM\Column(name: "B", type: Types::SMALLINT, nullable: true)]
    private ?int $b = 0;

    #[ORM\Column(name: "C", type: Types::SMALLINT, nullable: true)]
    private ?int $c = 0;

    #[ORM\Column(name: "D", type: Types::SMALLINT, nullable: true)]
    private ?int $d = 0;

    #[ORM\Column(name: "E", type: Types::SMALLINT, nullable: true)]
    private ?int $e = 0;

    #[ORM\Column(name: "F", type: Types::SMALLINT, nullable: true)]
    private ?int $f = 0;

    #[ORM\Column(name: "G", type: Types::SMALLINT, nullable: true)]
    private ?int $g = 0;

    #[ORM\Column(name: "H", type: Types::SMALLINT, nullable: true)]
    private ?int $h = 0;

    #[ORM\Column(name: "I", type: Types::SMALLINT, nullable: true)]
    private ?int $i = 0;

    #[ORM\Column(name: "J", type: Types::SMALLINT, nullable: true)]
    private ?int $j = 0;

    #[ORM\Column(name: "K", type: Types::SMALLINT, nullable: true)]
    private ?int $k = 0;

    #[ORM\Column(name: "L", type: Types::SMALLINT, nullable: true)]
    private ?int $l = 0;

    #[ORM\Column(name: "M", type: Types::SMALLINT, nullable: true)]
    private ?int $m = 0;

    #[ORM\Column(name: "N", type: Types::SMALLINT, nullable: true)]
    private ?int $n = 0;

    #[ORM\Column(name: "O", type: Types::SMALLINT, nullable: true)]
    private ?int $o = 0;

    #[ORM\Column(name: "P", type: Types::SMALLINT, nullable: true)]
    private ?int $p = 0;

    #[ORM\Column(name: "Q", type: Types::SMALLINT, nullable: true)]
    private ?int $q = 0;

    #[ORM\Column(name: "R", type: Types::SMALLINT, nullable: true)]
    private ?int $r = 0;

    #[ORM\Column(name: "S", type: Types::SMALLINT, nullable: true)]
    private ?int $s = 0;

    #[ORM\Column(name: "T", type: Types::SMALLINT, nullable: true)]
    private ?int $t = 0;

    /**
     * @ORM\Column(name="U", type="smallint", nullable=true)
     */
    private ?int $u = 0;

    #[ORM\Column(name: "V", type: Types::SMALLINT, nullable: true)]
    private ?int $v = 0;

    #[ORM\Column(name: "W", type: Types::SMALLINT, nullable: true)]
    private ?int $w = 0;


    #[ORM\Column(name: "X", type: Types::SMALLINT, nullable: true)]
    private ?int $x = 0;

    #[ORM\Column(name: "Y", type: Types::SMALLINT, nullable: true)]
    private ?int $y = 0;

    #[ORM\Column(name: "Z", type: Types::SMALLINT, nullable: true)]
    private ?int $z = 0;

    #[ORM\OneToMany(targetEntity: Notes::class, mappedBy: "jure")]
    private ?Collection $notesj;

    #[ORM\OneToMany(targetEntity: Phrases::class, mappedBy: "jure")]
    private ?Collection $phrases;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notesj = new ArrayCollection();
        $this->phrases = new ArrayCollection();

    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get initialesJure
     *
     * @return string
     */
    public function getInitialesJure(): ?string
    {
        return $this->initialesJure;
    }

    /**
     * Set initialesJure
     *
     * @param string $initialesJure
     *
     * @return Jures
     */
    public function setInitialesJure(string $initialesJure): Jures
    {
        $this->initialesJure = $initialesJure;

        return $this;
    }

    /**
     * Get a
     *
     * @return int|null
     */
    public function getA(): ?int
    {
        return $this->a;
    }

    /**
     * Set a
     *
     * @param int|null $a
     *
     * @return Jures
     */
    public function setA(?int $a): Jures
    {
        $this->a = $a;

        return $this;
    }

    /**
     * Get b
     *
     * @return int|null
     */
    public function getB(): ?int
    {
        return $this->b;
    }

    /**
     * Set b
     *
     * @param int|null $b
     *
     * @return Jures
     */
    public function setB(?int $b): Jures
    {
        $this->b = $b;

        return $this;
    }

    /**
     * Get c
     *
     * @return int|null
     */
    public function getC(): ?int
    {
        return $this->c;
    }

    /**
     * Set c
     *
     * @param int|null $c
     *
     * @return Jures
     */
    public function setC(?int $c): Jures
    {
        $this->c = $c;

        return $this;
    }

    /**
     * Get d
     *
     * @return int|null
     */
    public function getD(): ?int
    {
        return $this->d;
    }

    /**
     * Set d
     *
     * @param int|null $d
     *
     * @return Jures
     */
    public function setD(?int $d): Jures
    {
        $this->d = $d;

        return $this;
    }

    /**
     * Get e
     *
     * @return int|null
     */
    public function getE(): ?int
    {
        return $this->e;
    }

    /**
     * Set e
     *
     * @param int|null $e
     *
     * @return Jures
     */
    public function setE(?int $e): Jures
    {
        $this->e = $e;

        return $this;
    }

    /**
     * Get f
     *
     * @return int|null
     */
    public function getF(): ?int
    {
        return $this->f;
    }

    /**
     * Set f
     *
     * @param int|null $f
     *
     * @return Jures
     */
    public function setF(?int $f): Jures
    {
        $this->f = $f;

        return $this;
    }

    /**
     * Get g
     *
     * @return int
     */
    public function getG(): ?int
    {
        return $this->g;
    }

    /**
     * Set g
     *
     * @param int|null $g
     *
     * @return Jures
     */
    public function setG(?int $g): Jures
    {
        $this->g = $g;

        return $this;
    }

    /**
     * Get h
     *
     * @return int|null
     */
    public function getH(): ?int
    {
        return $this->h;
    }

    /**
     * Set h
     *
     * @param int|null $h
     *
     * @return Jures
     */
    public function setH(?int $h): Jures
    {
        $this->h = $h;

        return $this;
    }

    /**
     * Get i
     *
     * @return int|null
     */
    public function getI(): ?int
    {
        return $this->i;
    }

    /**
     * Set i
     *
     * @param int|null $i
     *
     * @return Jures
     */
    public function setI(?int $i): Jures
    {
        $this->i = $i;

        return $this;
    }

    /**
     * Get j
     *
     * @return int|null
     */
    public function getJ(): ?int
    {
        return $this->j;
    }

    /**
     * Set j
     *
     * @param int|null $j
     *
     * @return Jures
     */
    public function setJ(?int $j): Jures
    {
        $this->j = $j;

        return $this;
    }

    /**
     * Get k
     *
     * @return int|null
     */
    public function getK(): ?int
    {
        return $this->k;
    }

    /**
     * Set k
     *
     * @param int|null $k
     *
     * @return Jures
     */
    public function setK(?int $k): Jures
    {
        $this->k = $k;

        return $this;
    }

    /**
     * Get l
     *
     * @return int|null
     */
    public function getL(): ?int
    {
        return $this->l;
    }

    /**
     * Set l
     *
     * @param int|null $l
     *
     * @return Jures
     */
    public function setL(?int $l): Jures
    {
        $this->l = $l;

        return $this;
    }

    /**
     * Get m
     *
     * @return int|null
     */
    public function getM(): ?int
    {
        return $this->m;
    }

    /**
     * Set m
     *
     * @param int|null $m
     *
     * @return Jures
     */
    public function setM(?int $m): Jures
    {
        $this->m = $m;

        return $this;
    }

    /**
     * Get n
     *
     * @return int|null
     */
    public function getN(): ?int
    {
        return $this->n;
    }

    /**
     * Set n
     *
     * @param int|null $n
     *
     * @return Jures
     */
    public function setN(?int $n): Jures
    {
        $this->n = $n;

        return $this;
    }

    /**
     * Get o
     *
     * @return int|null
     */
    public function getO(): ?int
    {
        return $this->o;
    }

    /**
     * Set o
     *
     * @param int|null $o
     *
     * @return Jures
     */
    public function setO(?int $o): Jures
    {
        $this->o = $o;

        return $this;
    }

    /**
     * Get p
     *
     * @return int|null
     */
    public function getP(): ?int
    {
        return $this->p;
    }

    /**
     * Set p
     *
     * @param int|null $p
     *
     * @return Jures
     */
    public function setP(?int $p): Jures
    {
        $this->p = $p;

        return $this;
    }

    /**
     * Get q
     *
     * @return int|null
     */
    public function getQ(): ?int
    {
        return $this->q;
    }

    /**
     * Set q
     *
     * @param int|null $q
     *
     * @return Jures
     */
    public function setQ(?int $q): Jures
    {
        $this->q = $q;

        return $this;
    }

    /**
     * Get r
     *
     * @return int|null
     */
    public function getR(): ?int
    {
        return $this->r;
    }

    /**
     * Set r
     *
     * @param int|null $r
     *
     * @return Jures
     */
    public function setR(?int $r): Jures
    {
        $this->r = $r;

        return $this;
    }

    /**
     * Get s
     *
     * @return int|null
     */
    public function getS(): ?int
    {
        return $this->s;
    }

    /**
     * Set s
     *
     * @param int|null $s
     *
     * @return Jures
     */
    public function setS(?int $s): Jures
    {
        $this->s = $s;

        return $this;
    }

    /**
     * Get t
     *
     * @return int|null
     */
    public function getT(): ?int
    {
        return $this->t;
    }

    /**
     * Set t
     *
     * @param int|null $t
     *
     * @return Jures
     */
    public function setT(?int $t): Jures
    {
        $this->t = $t;

        return $this;
    }

    /**
     * Get u
     *
     * @return int|null
     */
    public function getU(): ?int
    {
        return $this->u;
    }

    /**
     * Set u
     *
     * @param int|null $u
     *
     * @return Jures
     */
    public function setU(?int $u): Jures
    {
        $this->u = $u;

        return $this;
    }

    /**
     * Get v
     *
     * @return int|null
     */
    public function getV(): ?int
    {
        return $this->v;
    }

    /**
     * Set v
     *
     * @param int|null $v
     *
     * @return Jures
     */
    public function setV(?int $v): Jures
    {
        $this->v = $v;

        return $this;
    }

    /**
     * Get w
     *
     * @return int|null
     */
    public function getW(): ?int
    {
        return $this->w;
    }

    /**
     * Set w
     *
     * @param int|null $w
     *
     * @return Jures
     */
    public function setW(?int $w): Jures
    {
        $this->w = $w;

        return $this;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(?int $x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): ?int
    {
        return $this->y;
    }

    public function setY(?int $y): self
    {
        $this->y = $y;

        return $this;
    }

    public function getZ(): ?int
    {
        return $this->z;
    }

    public function setZ(?int $z): self
    {
        $this->z = $z;

        return $this;
    }

    public function getAttributions(): ?array
    {
        $attribution = array();

        foreach (range('A', 'Z') as $i) {
            // On récupère le nom du getter correspondant à l'attribut.
            $method = 'get' . ucfirst($i);


            // Si le getter correspondant existe.
            if (method_exists($this, $method)) {
                // On appelle le setter.
                $statut = $this->$method();
                if ($statut == 1) {
                    $attribution[$i] = 1;
                } elseif (is_int($statut)) {
                    $attribution[$i] = 0;
                }
            }

        }
        return $attribution;

    }

    /**
     * Add notesj
     *
     * @param Notes $notesj
     *
     * @return Jures
     */
    public function addNotesj(Notes $notesj): ?Jures
    {
        $this->notesj[] = $notesj;

        //On relie l'équipe à "une ligne note"
        $notesj->setJure($this);

        return $this;
    }

    /**
     * Get notesj
     *
     * @return Collection
     */
    public function getNotesj()
    {
        return $this->notesj;
    }

    public function getNom(): string
    {
        return $this->getNomJure() . ' ' . $this->getPrenomJure();
    }

    /**
     * Get nomJure
     *
     * @return string
     */
    public function getNomJure(): ?string
    {
        return $this->nomJure;
    }

    /**
     * Set nomJure
     *
     * @param string $nomJure
     *
     * @return Jures
     */
    public function setNomJure(?string $nomJure): Jures
    {
        $this->nomJure = $nomJure;

        return $this;
    }

    /**
     * Get prenomJure
     *
     * @return string
     */
    public function getPrenomJure(): ?string
    {
        return $this->prenomJure;
    }

    /**
     * Set prenomJure
     *
     * @param string $prenomJure
     *
     * @return Jures
     */
    public function setPrenomJure(?string $prenomJure): Jures
    {
        $this->prenomJure = $prenomJure;

        return $this;
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

    /**
     * @return Collection<int, phrases>
     */
    public function getPhrases(): Collection
    {
        return $this->phrases;
    }

    public function addPhrase(?phrases $phrase): self
    {
        if (!$this->phrases->contains($phrase)) {
            $this->phrases[] = $phrase;
            $phrase->setJure($this);
        }

        return $this;
    }

    public function removePhrase(phrases $phrase): self
    {
        if ($this->phrases->removeElement($phrase)) {
            // set the owning side to null (unless already changed)
            if ($phrase->getJure() === $this) {
                $phrase->setJure(null);
            }
        }

        return $this;
    }


}