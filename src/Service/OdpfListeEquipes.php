<?php

namespace App\Service;

use App\Entity\Odpf\OdpfArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Equipesadmin;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\User;
use App\Entity\Rne;


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
        $affiche = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $editionpassee->getAffiche();
        $nomaffiche = explode('.', $editionpassee->getAffiche());
        $nomAfficheHr = $nomaffiche[0] . '-HR.' . $nomaffiche[1];
        $afficheHr = 'odpf/odpf-archives/' . $editionpassee->getEdition() . '/affiche/' . $nomAfficheHr;
        $repositoryUser = $this->em->getRepository(User::class);
        $repositoryRne = $this->em->getRepository(Rne::class);
        $listEquipes = $repositoryEquipesadmin->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->orderBy('e.numero', 'ASC')
            ->getQuery()
            ->getResult();
        foreach ($listEquipes as $equipe) {
            $numero = $equipe->getNumero();
            $rne = $equipe->getRne();
            $lycee[$numero] = $repositoryRne->findByRne($rne);
            $idprof1 = $equipe->getIdProf1();
            $prof1[$numero] = $repositoryUser->findById($idprof1);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$numero] = $repositoryUser->findById($idprof2);

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
