<?php

namespace App\Service;

use App\Entity\Edition;
use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfEditionsPassees;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;


class OdpfCreateArray
{
    private EntityManagerInterface $em;
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;
    
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->doctrine = $doctrine;
    }
    
    public function getArray($choix): array
    {
        
        try {
            $edition = $this->requestStack->getSession()->get('edition');
            $edition->getDateclotureinscription() >= new \DateTime('now') ? $ouvertes = 'ouvertes' : $ouvertes = 'closes';
        } catch (Exception $e) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
        }
        //dd($edition);
        $repo = $this->em->getRepository(OdpfArticle::class);
        
        
        $article = $repo->findOneBy(['choix' => $choix]);
        $categorie = $article->getCategorie();
        $texte = $article->getTexte();
        if ($choix == 'inscriptions') {
            //Afin que le lien fonctionne en local pour les essais et sur le serveur
            explode(':', $_SERVER['HTTP_HOST'])[0] == 'localhost' ? $path = '/Utilisateur/inscrire_equipe,x' : $path = '/public/index.php/Utilisateur/inscrire_equipe,x';
            //La première ligne de la page les inscriptions est remplie automatiquement selon la date
            if ($ouvertes == 'ouvertes') {
                $texte = '<p><span style="color:#ff0000; font-size:12pt"><a href="' . $path . '" target="_blank" title="Inscrire une équipe">Les inscriptions sont ' . $ouvertes . '</a></span><span style="font-size:12pt">.</span></p>'
                    . $texte;
            }// Le texte de la page inscriptions se rajoute à cette phrase.
            else {
                $texte = 'Les inscriptions sont ' . $ouvertes . '</a></span><span style="font-size:12pt">.</span></p>'
                    . $texte;
            }
        }
        $titre = $article->getTitre();
        $titre_objectifs = $article->getTitreObjectifs();
        $texte_objectifs = $article->getTexteObjectifs();
        $image = $article->getImage();
        $alt_image = $article->getAltImage();
        $descr_image = $article->getDescrImage();
        //l'édition en cours est considérée comme édition passée
        $editionpassee = $this->em->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $edition->getEd()]);
        $photoparrain = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/parrain/' . $editionpassee->getPhotoParrain();
        $parrain = $editionpassee->getNomParrain();
        $lienparrain = $editionpassee->getLienparrain();
        $titreparrain = $editionpassee->getTitreParrain();
        $affiche = '';
        $afficheHr = '';
        if ($editionpassee->getAffiche() !== null) {
            if (file_exists('odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $editionpassee->getAffiche())) {
                $affiche = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $editionpassee->getAffiche();
                $nomaffiche = explode('.', $editionpassee->getAffiche());
                $nomAfficheHr = $nomaffiche[0] . '-HR.' . $nomaffiche[1];
                $afficheHr = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $nomAfficheHr;
            }
        }
        $tab = ['choix' => $choix,
            'article' => $article,
            'categorie' => $categorie,
            'titre' => $titre,
            'texte' => $texte,
            'titre_objectifs' => $titre_objectifs,
            'texte_objectifs' => $texte_objectifs,
            'image' => $image,
            'alt_image' => $alt_image,
            'descr_image' => $descr_image,
            'edition' => $edition,
            'parrain' => $parrain,
            'photoparrain' => $photoparrain,
            'titreparrain' => $titreparrain,
            'lienparrain' => $lienparrain,
            'affiche' => $affiche,
            'afficheHR' => $afficheHr
        ];
        
        return ($tab);
    }
}
