<?php

namespace App\Repository\Odpf;

use App\Entity\Odpf\OdpfArticle;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method OdpfArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method OdpfArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method OdpfArticle[]    findAll()
 * @method OdpfArticle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OdpfArticleRepository extends ServiceEntityRepository
{
    private RequestStack $requestStack;

    public function __construct(ManagerRegistry $registry, RequestStack $requestStack)
    {
        parent::__construct($registry, OdpfArticle::class);
        $this->requestStack = $requestStack;
    }

    public function listfaq(): array
    {
        // construit la liste des questions de la Foire Ayx Questions
        $categorie = 'faq';
        $listfaq = $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'c')
            ->addSelect('a')
            ->andWhere('c.categorie =:categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('a.updatedAt', 'ASC')
            ->getQuery()
            ->getResult();
        //renvoie la liste des questions, ordonnée par date ascendante
        return ($listfaq);
    }

    public function faq_paginee(): array
    {
        $categorie = 'faq';
        $choix = 'faq';
        $titre = 'Foire aux questions';
        $pageFCourante = $this->requestStack->getSession()->get('pageFCourante');
        $listfaq = $this->createQueryBuilder('a')
            ->leftJoin('a.categorie', 'c')
            ->addSelect('a')
            ->andWhere('c.categorie =:categorie')
            ->setParameter('categorie', $categorie)
            ->orderBy('a.updatedAt', 'ASC')
            ->getQuery()
            ->getResult();

        $limit = 5;
        $totfaq = count($listfaq);
        $nbpages = intval(ceil($totfaq / $limit));
        $afffaq = array_chunk($listfaq, $limit);
        return ['categorie' => $categorie,
            'choix' => $choix,
            'titre' => $titre,
            'nbpages' => $nbpages,
            'pageFCourante' => $pageFCourante,
            'afffaq' => $afffaq
        ];
    }

    public function accueil_actus(): array
    {
        // construit l'affichage des actus en page d'accueil
        $choix = 'actus';
        $affActus = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.choix =:choix')
            ->andWhere('e.publie !=:valeur')
            ->setParameter('valeur', false)
            ->setParameter('choix', $choix)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
        // liste des 5 dernières actus publiées, classées par date descendante
        for ($i = 0; $i < count($affActus); $i++) {
            $texte = $affActus[$i]->getTexte();
            $chaine = strip_tags($texte, '<a>');
            $texte = $this->couper($chaine, 30);
            // découpe les 30 premiers mots de l'actu
            $affActus[$i]->setTexte($texte);
        }

        $tab['affActus'] = $affActus;
        return ($tab);
    }

    public function actuspaginees(): array
    {
        $titre = 'Actus';
        $choix = 'actus';
        $edition = $this->requestStack->getSession()->get('edition');
        $pageCourante = $this->requestStack->getSession()->get('pageCourante');
        $listActus = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.choix =:choix')
            ->andWhere('e.publie !=:valeur')
            ->setParameter('valeur', false)
            ->setParameter('choix', $choix)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $limit = 5;
        $totactus = count($listActus);
        $nbpages = intval(ceil($totactus / $limit));
        $affActus = array_chunk($listActus, $limit);

        //dd($affActus);

        return [
            'choix' => $choix,
            'titre' => $titre,
            'edition' => $edition,
            'nbpages' => $nbpages,
            'pageCourante' => $pageCourante,
            'affActus' => $affActus
        ];
    }

    public function couper($chaine, $nbmots): string
    {
        $n = 0;
        $res = "";
        while (1) {
            // trouver le début de premier tag
            $debut_tag = strpos($chaine, '<');
            // sinon prendre toute la chaine
            if (!$debut_tag) $debut_tag = strlen($chaine);
            // copier le morceau de texte
            $tmp = substr($chaine, 0, $debut_tag);
            // couper par mots
            $mots = explode(' ', $tmp);
            // ajouter les mots au résultat
            for ($i = 0; $i < count($mots) && $n < $nbmots; $i++, $n++)
                $res .= ($n ? " " : "") . $mots[$i];
            // vérifier si on a atteint le nombre max de mots
            if ($n >= $nbmots) return $res;
            // couper la chaine
            $chaine = substr($chaine, $debut_tag);
            // vérifier si on a atteint la fin de la chaine
            if (strlen($chaine) == 0) return $res;
            // trouver la fin du tag
            $fin_tag = strpos($chaine, '>');
            // sinon erreur de fin de tag, retourner chaîne vide
            if (!$fin_tag) return "";
            // copier tag
            $res .= substr($chaine, 0, $fin_tag + 1);
            // ajouter un mot
            $n++;
            // vérifier si on a atteint le nombre max de mots
            if ($n >= $nbmots) return $res;
            // couper la chaine
            $chaine = substr($chaine, $fin_tag + 1);
        }
    }


}
