<?php

namespace App\Service;

use App\Entity\Odpf\OdpfArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Equipesadmin;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\User;
use App\Entity\Uai;


class OdpfListeEquipes
{
    private EntityManagerInterface $em;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function getArray($choix): array
    {
        $edition = $this->requestStack->getSession()->get('edition');
        $repo = $this->em->getRepository(OdpfArticle::class);
        $article = $repo->findOneBy(['choix' => $choix]);
        $categorie = $article->getCategorie();
        $titre = $article->getTitre() . ' ' . $edition->getEd() . 'e édition';
        $repositoryEquipesadmin = $this->em->getRepository(Equipesadmin::class);
        $editionpassee = $this->em->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $edition->getEd()]);
        $photoparrain = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/parrain/' . $editionpassee->getPhotoParrain();
        $parrain = $editionpassee->getNomParrain();
        $lienparrain = $editionpassee->getLienparrain();
        $titreparrain = $editionpassee->getTitreParrain();
        if ($editionpassee->getAffiche() !== null) {
            if (file_exists('odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $editionpassee->getAffiche())) {
                $affiche = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $editionpassee->getAffiche();
                $nomaffiche = explode('.', $editionpassee->getAffiche());
                $nomAfficheHr = $nomaffiche[0] . '-HR.' . $nomaffiche[1];
                $afficheHr = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $nomAfficheHr;
            }
        }
        $affiche = '';
        $afficheHr = '';
        $repositoryUser = $this->em->getRepository(User::class);
        $repositoryUai = $this->em->getRepository(Uai::class);
        $listEquipes = $repositoryEquipesadmin->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->andWhere('e.inscrite !=0')
            ->orderBy('e.numero', 'ASC')
            ->getQuery()
            ->getResult();
        foreach ($listEquipes as $equipe) {
            $numero = $equipe->getNumero();
            $rne = $equipe->getUai();
            $lycee[$numero] = $repositoryUai->findBy(['rne'=>$rne]);
            $idprof1 = $equipe->getIdProf1();
            $prof1[$numero] = $repositoryUser->findBy(['id'=>$idprof1]);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$numero] = $repositoryUser->findBy(['id'=>$idprof2]);

        }
        //dd($listEquipes);
        if ($listEquipes != []) {

            return ['listEquipes' => $listEquipes,
                'prof1' => $prof1,
                'prof2' => $prof2,
                'lycee' => $lycee,
                'choix' => $choix,
                'edition' => $edition,
                'titre' => $titre,
                'categorie' => $categorie,
                'parrain' => $parrain,
                'photoparrain' => $photoparrain,
                'titreparrain' => $titreparrain,
                'lienparrain' => $lienparrain,
                'affiche' => $affiche,
                'afficheHR' => $afficheHr
            ];
        } else {
            $listEquipes = [];
            return ['listEquipes' => $listEquipes,
                'choix' => $choix,
                'edition' => $edition,
                'titre' => $titre,
                'categorie' => $categorie,
                'parrain' => $parrain,
                'photoparrain' => $photoparrain,
                'titreparrain' => $titreparrain,
                'lienparrain' => $lienparrain,
                'affiche' => $affiche,
                'afficheHR' => $afficheHr

            ];
        }
    }



}
