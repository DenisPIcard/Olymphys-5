<?php

namespace App\Repository;

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

    public function accueil_actus(): array
    {
        $choix='actus';
        $affActus = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.choix =:choix')
            ->setParameter('choix', $choix)
            ->orderBy('e.updatedAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
        for($i=0;$i<count($affActus);$i++ ){
            $texte=$affActus[$i]->getTexte();
            $chaine=strip_tags($texte,'<a>');
            $texte=$this->couper($chaine, 30);
            $affActus[$i]->setTexte($texte);
        }

        $tab['affActus']=$affActus;
        return($tab);
    }
    public function actuspaginees(): array
    {
        $categorie ='Actus';

        $titre='Actus';
        $choix='actus';
        $edition = $this->requestStack->getSession()->get('edition');
        $pageCourante=$this->requestStack->getSession()->get('pageCourante');
        $listActus = $this->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.choix =:choix')
            ->setParameter('choix', $choix)
            ->orderBy('e.updatedAt', 'DESC')
            ->getQuery()
            ->getResult()
           ;
        $limit = 5;
        $totactus=count($listActus);
        $nbpages=intval(ceil($totactus/$limit));
        $affActus=array_chunk($listActus,$limit);

        //dd($affActus);

        return [
            'categorie' =>$categorie,
            'choix' =>$choix,
            'titre'=>$titre,
            'edition' =>$edition,
            'nbpages' =>$nbpages,
            'pageCourante'=>$pageCourante,
            'affActus' => $affActus
            ];
    }

    public  function couper($chaine, $nbmots): string
    {
        $n=0;
        $res="";
        while(1)
        {
            // trouver le début de premier tag
            $debut_tag=strpos($chaine,'<');
            // sinon prendre toute la chaine
            if($debut_tag==false) $debut_tag=strlen($chaine);
            // copier le morceau de texte
            $tmp=substr($chaine,0,$debut_tag);
            // couper par mots
            $mots=explode(' ',$tmp);
            // ajouter les mots au résultat
            for($i=0;$i<count($mots) && $n<$nbmots;$i++,$n++)
                $res.=($n?" ":"" ).$mots[$i];
            // vérifier si on a atteint le nombre max de mots
            if ($n>=$nbmots) return $res;
            // couper la chaine
            $chaine=substr($chaine,$debut_tag);
            // vérifier si on a atteint la fin de la chaine
            if (strlen($chaine)==0) return $res;
            // trouver la fin du tag
            $fin_tag=strpos($chaine,'>');
            // sinon erreur de fin de tag, retourner chaîne vide
            if($fin_tag==false) return "";
            // copier tag
            $res.=substr($chaine, 0, $fin_tag+1);
            // ajouter un mot
            $n++;
            // vérifier si on a atteint le nombre max de mots
            if ($n>=$nbmots) return $res;
            // couper la chaine
            $chaine=substr($chaine,$fin_tag+1);
        }
    }


}
