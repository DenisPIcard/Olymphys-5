<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Livredor;
use App\Entity\Photos;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArchivesController extends AbstractController
{
    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @Route("/archives/liste_fichiers_photos,{choix}", name="archives_fichiers_photos")
     *
     */
    public function liste_fichiers_photos(Request $request, $choix)
    {
        $idedition = $request->query->get('sel');
        $repositoryEdition = $this->getDoctrine()
            ->getManager()
            ->getRepository(Edition::class);
        $repositoryFichiersequipes = $this->getDoctrine()
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        $repositoryEquipesadmin = $this->getDoctrine()
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryPhotos = $this->getDoctrine()
            ->getManager()
            ->getRepository(Photos::class);
        $repositoryLivresdor = $this->getDoctrine()
            ->getManager()
            ->getRepository(Livredor::class);
        $editions = $repositoryEdition->findAll();
        $ids = [];
        $i = 0;
        if (count(explode('-', $choix)) == 2) {
            $idedition = explode('-', $choix)[1];
            $choix = explode('-', $choix)[0];
        }

        foreach ($editions as $edition_) {
            $ids[$i] = $edition_->getId();
            $i++;
        }
        if ($choix == 1) {//Edition en cours


            $id = max($ids);
            $edition = $repositoryEdition->findOneBy(['id' => $id]);
            $editions = null;
        } elseif ($choix == 0) {//Archives
            ($idedition !== null) ? $edition = $repositoryEdition->findOneBy(['id' => $idedition]) : $edition = $repositoryEdition->findOneBy(['id' => max($ids) - 1]);
            $editions = $repositoryEdition->createQueryBuilder('e')
                ->select('e')
                ->where('e.id < :maxid')
                ->setParameter('maxid', max($ids))
                ->orderBy('e.ed', 'DESC')
                ->getQuery()->getResult();

        }

        $fichiersEquipes = $repositoryFichiersequipes->createQueryBuilder('f')
            ->where('f.edition =:edition')
            ->andWhere('f.typefichier <4')
            ->setParameter('edition', $edition)
            ->getQuery()->getResult();
        $equipes = $repositoryEquipesadmin->createQueryBuilder('f')
            ->where('f.edition =:edition')
            ->andWhere('f.rneId IS NOT NULL')
            ->setParameter('edition', $edition)
            ->addOrderBy('f.lettre', 'ASC')
            ->addOrderBy('f.numero', 'ASC')
            ->getQuery()->getResult();
        $equipessel = $repositoryEquipesadmin->createQueryBuilder('f')
            ->where('f.edition =:edition')
            ->andWhere('f.rneId IS NOT NULL')
            ->andWhere('f.selectionnee = 1')
            ->setParameter('edition', $edition)
            ->addOrderBy('f.lettre', 'ASC')
            ->addOrderBy('f.numero', 'ASC')
            ->getQuery()->getResult();


        $i = 0;
        $photoseqcia = [];
        $photoseqcn = [];
        foreach ($equipes as $equipe) {

            $qb1 = $repositoryPhotos->createQueryBuilder('p')
                ->where('p.edition =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('p.equipe =:equipe')
                ->setParameter('equipe', $equipe);

            if ($qb1->getQuery()->getResult() != null) {
                $photos = $qb1->getQuery()->getResult();
                shuffle($photos);
                $photoseqcia[$i] = $photos[array_rand([0. . (count($photos) - 1)])];
            }

            if ($equipe->getSelectionnee() == true) {

                $qb2 = $repositoryPhotos->createQueryBuilder('p')
                    ->where('p.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->andWhere('p.equipe =:equipe')
                    ->setParameter('equipe', $equipe)
                    ->andWhere('p.national = 1');

                $photos = $qb2->getQuery()->getResult();
                if ($photos != null) {
                    shuffle($photos);
                    $photoseqcn[$i] = $photos[array_rand([0. . (count($photos) - 1)])];
                }
            }
            $i++;
        }

        $livresdor = $repositoryLivresdor->findBy(['edition' => $edition]);
        if ($choix == 0) {
            return $this->render('archives\archives.html.twig',
                array('fichiersequipes' => $fichiersEquipes, 'editions' => $editions, 'photoseqcn' => $photoseqcn, 'photoseqcia' => $photoseqcia, 'equipes' => $equipes, 'equipessel' => $equipessel, 'livresdor' => $livresdor, 'edition_affichee' => $edition));
        }
        if ($choix == 1) {
            return $this->render('archives\equipes.html.twig',
                array('fichiersequipes' => $fichiersEquipes, 'photoseqcn' => $photoseqcn, 'photoseqcia' => $photoseqcia, 'equipes' => $equipes, 'equipessel' => $equipessel, 'livresdor' => $livresdor, 'edition_affichee' => $edition));


        }
    }

}