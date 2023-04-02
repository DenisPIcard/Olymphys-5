<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Edition;
use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Odpf\OdpfFichierspasses;
use App\Entity\Odpf\OdpfVideosequipes;
use App\Entity\Photos;
use App\Service\OdpfCreateArray;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OdpfEditionspasseesController extends AbstractController
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

   #[Route("/odpf/editionspassees/equipe,{id}", name:"odpf_editionspassees_equipe")]
    public function equipe($id, OdpfCreateArray $createArray): Response
    {
        $edition = $this->requestStack->getSession()->get('edition');
        if ($edition === null) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
            return $this->redirectToRoute('core_home');

        }
        $repo = $this->doctrine->getRepository(OdpfArticle::class);
        $listfaq = $repo->listfaq();

        $equipe = $this->em->getRepository(OdpfEquipesPassees::class)->findOneBy(['id' => $id]);
        $listeFichiers = $this->em->getRepository(OdpfFichierspasses::class)->createQueryBuilder('f')
            ->leftJoin('f.equipepassee', 'eq')
            ->andWhere('eq.selectionnee = 1')
            ->andWhere('f.equipepassee =:equipe')
            ->andWhere('f.national = 1')
            ->setParameter('equipe', $equipe)
            ->getQuery()->getResult();

        $photos = $this->em->getRepository(Photos::class)->findBy(['equipepassee' => $equipe]);
        count($photos) != 0 ? $photostest = true : $photostest = false;
        // dd($photos);
        $choix = 'equipepassee';
        $tab = $createArray->getArray($choix);
        $tab['equipe'] = $equipe;
        $tab['texte'] = $this->createTextEquipe($equipe, $listeFichiers);
        $tab['memoires'] = $listeFichiers;
        $tab['photos'] = $photos;
        $tab['photostest'] = $photostest;
        $tab['listfaq'] = $listfaq;
        // $tab['categorie']='editions';

        // dd($tab);
        return $this->render('core/odpf-editions-passees-equipe.html.twig', $tab);
    }

    #[Route("/odpf/editionspassees/editions", name:"odpf_editionspassees_editions")]
    public function editions(OdpfCreateArray $createArray): Response
    {
        $edition = $this->requestStack->getSession()->get('edition');
        if ($edition === null) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
            return $this->redirectToRoute('core_home');

        }
        $repo = $this->doctrine->getRepository(OdpfArticle::class);
        $listfaq = $repo->listfaq();
        $editions = $this->doctrine->getRepository(OdpfEditionsPassees::class)->createQueryBuilder('e')
            ->where('e.edition !=:lim')
            ->addOrderBy('e.edition', 'DESC')
            ->setParameter('lim', $this->requestStack->getSession()->get('edition')->getEd())
            ->getQuery()->getResult();;

        if (isset($_REQUEST['sel'])) {
            $idEdition = $_REQUEST['sel'];

        } else {
            $idEdition = explode('-', $_REQUEST['infos'])[1];

        }
        $editionAffichee = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['id' => $idEdition]);
        $photosed = $this->doctrine->getRepository(photos::class)->findBy(['editionspassees' => $editionAffichee]);

        count($photosed) != 0 ? $photostest = true : $photostest = false;
        $choix = 'edition' . $editionAffichee->getEdition();
        $tab = $createArray->getArray($choix);
        $tab['edition_affichee'] = $editionAffichee;
        $tab['editions'] = $editions;
        $tab['choice'] = 'editions';
        $tab['photoParrain'] = 'odpf-archives/' . $editionAffichee->getEdition() . '/parrain/' . $editionAffichee->getPhotoParrain();
        $tab['parrain'] = $editionAffichee->getNomParrain();
        $tab['lien'] = $editionAffichee->getLienparrain();
        $tab['affiche'] = 'odpf-archives/' . $editionAffichee->getEdition() . '/affiche/' . $editionAffichee->getAffiche();
        $tab['listfaq'] = $listfaq;
        $tab['photostest'] = $photostest;
        //dd($tab);
        return $this->render('core/odpf-pages-editions.html.twig', $tab);
    }

    public function createTextEquipe($equipe, $listeFichiers): string
    {
        if ($equipe->getAcademie() === null) {
            $academie = '.';
        } else {
            $academie = ', académie de ' . $equipe->getAcademie() . '.';
        }
        //test du répertoire de travail

        if(explode(':',$_SERVER['SERVER_NAME'])[0]=='localhost'){
            $texte= '<a href="/odpf/editionspassees/editions?sel=' . $equipe->getEditionspassees()->getId() . '">Retour</a>';
        }
        else{
            $texte='<a href="/../public/index.php/odpf/editionspassees/editions?sel='.$equipe->getEditionspassees()->getId().'">Retour</a>';
        }
        //sur le site : <a href="/../public/index.php/odpf/editionspassees/editions?sel='.$equipe->getEditionspassees()->getId().'">Retour</a>

        $texte = $texte.'
                         
                <table>
                <thead>
                <tr>
                    <th colspan="2"><h3>' . $equipe->getTitreProjet() . '</h3></th>
               </tr>
               </thead>
                <tr>
                    <td colspan="2">Lycée ' . $equipe->getLycee() . ' de ' . $equipe->getVille() . $academie . '</td>
                </tr>
                
               <tr>
                    <td colspan="2"><b> Professeur(s) :  </b></td>
                    </tr>
                    <tr>
                    <td>' . $equipe->getProfs() . '</td>
               </tr>
               <tr>
                    <td> <b>Elèves : </b></td>
               </tr>
               <tr>     
                    <td>' . $equipe->getEleves() . '</td>
               </tr>
    
               </table>';
        if ($equipe->getSelectionnee() == true) {
            $texte = $texte . '<b>Sélectionnée pour le concours national</b><br>';
        }
        //$memoires=$this->em->getRepository(OdpfFichierspasses::class)->findBy(['equipepassee'=>$equipe]);
        $i = 0;
        foreach ($listeFichiers as $fichier) {

            if (in_array($fichier->getTypefichier(), [0, 1, 2, 3])) {

                $fichier->getTypefichier() == 1 ? $typefichier = 0 : $typefichier = $fichier->getTypefichier();

                array_key_last($listeFichiers) == $i ? $virgule = '' : $virgule = ', ';
                if ($fichier->getNomfichier() != null) {
                    if(explode(':',$_SERVER['SERVER_NAME'])[0]=='localhost'){
                        $texte = $texte . '<a href="/../odpf/odpf-archives/' . $equipe->getEditionspassees()->getEdition() . '/fichiers/' . $this->getParameter('type_fichier')[$typefichier] . '/' . $fichier->getNomfichier() . '" target="_blank">' . $this->getParameter('type_fichier_lit')[$fichier->getTypefichier()] . '</a>' . $virgule;
                    }
                    else{
                        $texte = $texte . '<a href="/../public/odpf/odpf-archives/' . $equipe->getEditionspassees()->getEdition() . '/fichiers/' . $this->getParameter('type_fichier')[$typefichier] . '/' . $fichier->getNomfichier() . '" target="_blank">' . $this->getParameter('type_fichier_lit')[$fichier->getTypefichier()] . '</a>' . $virgule;
                    }
                    //$texte = $texte . '<a href="/../odpf/odpf-archives/' . $equipe->getEditionspassees()->getEdition() . '/fichiers/' . $this->getParameter('type_fichier')[$typefichier] . '/' . $fichier->getNomfichier() . '">' . $this->getParameter('type_fichier_lit')[$fichier->getTypefichier()] . '</a>' . $virgule;
                }
            }
            $i += 1;
        }

        $videos = $this->em->getRepository(OdpfVideosequipes::class)->findBy(['equipe' => $equipe]);

        if ($videos != null) {
            $textevideo = '<div class="table">';
            $i=1;
            foreach ($videos as $video) {
               /*$lien = preg_replace(
                    "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                    "<iframe  width=\"560\" height=\"315\" src=\"//www.youtube.com/embed/$2\" allowfullscreen; frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\"></iframe>",
                    $video->getLien()
                );*/
                $lien = '<a href="'.$video->getLien().'" target="_blank"> Vidéo '.$i.'</a><br>';

                $textevideo = $textevideo . '<tr><td>'.$lien.' </td></tr>';
            $i=$i+1;
            }

            $textevideo = $textevideo . '</div>';
            $texte = $texte . $textevideo;


        }

        return $texte;

    }

}
