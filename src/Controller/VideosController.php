<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Videosequipes;
use App\Form\ConfirmType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class VideosController extends AbstractController
{
    private \Doctrine\Persistence\ManagerRegistry $doctrine;

    public function __construct(\Doctrine\Persistence\ManagerRegistry $doctrine)
    {

        $this->doctrine = $doctrine;
    }

    #[IsGranted("ROLE_PROF")]
    #[Route("/videos/liens_videos,{infos}", name:"videos_liens_videos")]
    public function liens_videos(Request $request, $infos)
    {
        $repositoryEquipesadmin = $this->doctrine
            ->getRepository(Equipesadmin::class);

        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
        $repositoryVideosequipes = $this->doctrine
            ->getRepository(Videosequipes::class);
        $Infos = explode('-', $infos);

        $id_equipe = $Infos[0];
        $concours = $Infos[1];
        $choix = $Infos[2];
        if ($choix == 'modifier') {
            $id_video = $Infos[3];
            $videoequipe = $repositoryVideosequipes->find(['id' => $id_video]);
        }
        if ($choix == 'supprimer') {
            $id_video = $request->get('myModalID');
            $video = $repositoryVideosequipes->find(['id' => $id_video]);
            $em = $this->doctrine->getManager();

            $em->remove($video);
            $em->flush();


            return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', ['infos' => $infos]);
        }


        if ($choix == 'nouvelle') {
            $videoequipe = new Videosequipes();
        };
        if (count($Infos) == 5) {
            $request->getSession()
                ->getFlashBag()
                ->add('alert', $Infos[4]);
            $infos = $Infos[0] . '-' . $Infos[1] . '-' . $Infos[2] . '-' . $Infos[3];
        }
        $equipe = $repositoryEquipesadmin->find(['id' => $id_equipe]);

        $edition = $repositoryEdition->findOneBy([], ['id' => 'desc']);
        //$edition= $session->get('edition');
        $nom_equipe = $equipe->getTitreProjet();
        $lettre_equipe = $equipe->getLettre();
        $donnees_equipe = $lettre_equipe . ' - ' . $nom_equipe;
        if (!$lettre_equipe) {
            $numero_equipe = $equipe->getNumero();
            $nom_equipe = $equipe->getTitreProjet();
            $donnees_equipe = $numero_equipe . ' - ' . $nom_equipe;
        }

        $form = $this->createFormBuilder($videoequipe)->add('lien')
            ->add('nom', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Valider'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();
            $url = $form->get('lien')->getData();
            $parsed = parse_url($url);
            // $file_headers = @get_headers($url);https://youtu.be/4i0nff-3xWg

            //dd($parsed['host']);
            if (($parsed['host'] != 'www.youtube.com') and ($parsed['host'] != 'youtube.com') and ($parsed['host'] != 'www.youtu.be') and ($parsed['host'] != 'youtu.be')) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('info', 'Le lien saisi n\'est pas valide');
                $infos = $infos . '-' . 'Le lien saisi n\'est pas valide';

                return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', ['infos' => $infos]);
            }
            /* if(($file_headers==false ) || ($file_headers[9] != 'Server: YouTube Frontend Proxy')|| (count($file_headers) >17)){          //  'HTTP/1.1 404 Not Found'){
                dd($url);
                 $infos=$infos.'-'.'Le lien saisi n\'est pas valide';
               return $this->redirectToRoute('videos_liens_videos',['infos'=>$infos]);
             }*/
            $nom = $form->get('nom')->getData();
            $videoequipe->setLien($url);
            $videoequipe->setNom($nom);
            $videoequipe->setEquipe($equipe);
            $videoequipe->setEdition($edition);
            $em->persist($videoequipe);
            $em->flush();

            return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', ['infos' => $infos]);
        }

        $qb = $repositoryVideosequipes->createQueryBuilder('v')
            ->where('v.equipe =:equipe')
            ->setParameter('equipe', $equipe)
            ->orderBy('v.nom', 'ASC');
        $liste_videos = $qb->getQuery()->getResult();

        if ($liste_videos === null) {
            $liste_videos = [];

        }
        return $this->render('adminfichiers/liens_videos.html.twig', [
            'form' => $form->createView(), 'donnees_equipe' => $donnees_equipe, 'choix' => $choix, 'liste_videos' => $liste_videos, 'infos' => $infos
        ]);
    }

}