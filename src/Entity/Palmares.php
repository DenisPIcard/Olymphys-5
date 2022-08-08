<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Palmares
 *
 * @ORM\Table(name="palmares")
 * @ORM\Entity(repositoryClass="App\Repository\PalmaresRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Palmares
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
     *
     * @ORM\Column(name="categorie", type="string", length=255)
     */
    private $categorie;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $a;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $b;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $c;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $d;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $e;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $f;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $g;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $h;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $i;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $j;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $k;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $l;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $m;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $n;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $o;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $p;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $q;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $r;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $s;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $t;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $u;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $v;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $w;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $x;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $y;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Prix", cascade={"persist"})
     */
    private $z;

    /**
     * @ORM\PostPersist
     */
    public function attributionsPrix()
    {
        $repositoryEquipes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        foreach (range('A', 'Z') as $i) {
            // On récupère le nom du getter correspondant à l'attribut.
            $method = 'get' . ucfirst($i);

            // Si le getter correspondant existe.
            if (method_exists($this, $method)) {
                // On appelle le setter.
                $prix = $this->$method();
                if ($prix) {
                    $equipe = $repositoryEquipes->findOneByLettre($i);
                    $equipe->setPrix($prix);

                }
            }
        }
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set categorie
     *
     * @param string $categorie
     *
     * @return Palmares
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return string
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set a
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setA($a)
    {
        $this->a = $a;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * Set b
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setB($b)
    {
        $this->b = $b;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getB()
    {
        return $this->b;
    }

    /**
     * Set c
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setC($c)
    {
        $this->c = $c;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getC()
    {
        return $this->c;
    }

    /**
     * Set d
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setD($d)
    {
        $this->d = $d;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getD()
    {
        return $this->d;
    }

    /**
     * Set e
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setE($e)
    {
        $this->e = $e;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getE()
    {
        return $this->e;
    }

    /**
     * Set f
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setF($f)
    {
        $this->f = $f;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getF()
    {
        return $this->f;
    }

    /**
     * Set g
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setG($g)
    {
        $this->g = $g;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getG()
    {
        return $this->g;
    }

    /**
     * Set h
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setH($h)
    {
        $this->h = $h;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getH()
    {
        return $this->h;
    }

    /**
     * Set i
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setI($i)
    {
        $this->i = $i;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getI()
    {
        return $this->i;
    }

    /**
     * Set j
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setJ($j)
    {
        $this->j = $j;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getJ()
    {
        return $this->j;
    }

    /**
     * Set k
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setK($k)
    {
        $this->k = $k;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getK()
    {
        return $this->k;
    }

    /**
     * Set l
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setL($l)
    {
        $this->l = $l;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getL()
    {
        return $this->l;
    }

    /**
     * Set m
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setM($m)
    {
        $this->m = $m;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getM()
    {
        return $this->m;
    }

    /**
     * Set n
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setN($n)
    {
        $this->n = $n;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getN()
    {
        return $this->n;
    }

    /**
     * Set o
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setO($o)
    {
        $this->o = $o;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getO()
    {
        return $this->o;
    }

    /**
     * Set p
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setP($p)
    {
        $this->p = $p;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getP()
    {
        return $this->p;
    }

    /**
     * Set q
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setQ($q)
    {
        $this->q = $q;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getQ()
    {
        return $this->q;
    }

    /**
     * Set r
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setR($r)
    {
        $this->r = $r;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getR()
    {
        return $this->r;
    }

    /**
     * Set s
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setS($s)
    {
        $this->s = $s;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getS()
    {
        return $this->s;
    }

    /**
     * Set t
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setT($t)
    {
        $this->t = $t;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getT()
    {
        return $this->t;
    }

    /**
     * Set u
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setU($u)
    {
        $this->u = $u;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getU()
    {
        return $this->u;
    }

    /**
     * Set v
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setV($v)
    {
        $this->v = $v;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getV()
    {
        return $this->v;
    }

    /**
     * Set w
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setW($w)
    {
        $this->w = $w;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getW()
    {
        return $this->w;
    }

    /**
     * Set x
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set z
     *
     * @param \App\Entity\Prix $prix
     *
     * @return Palmares
     */
    public function setZ($z)
    {
        $this->z = $z;

        return $this;
    }

    /**
     * Get prix
     *
     * @return \App\Entity\Prix
     */
    public function getZ()
    {
        return $this->z;
    }

}
