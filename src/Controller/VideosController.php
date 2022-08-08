<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Videosequipes;
use App\Form\ConfirmType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class VideosController extends AbstractController
{


    /**
     * @IsGranted("ROLE_PROF")
     *
     * @Route("/videos/liens_videos,{infos}", name="videos_liens_videos")
     *
     */
    public function liens_videos(Request $request, $infos)
    {
        $repositoryEquipesadmin = $this->getDoctrine()
            ->getManager()
            ->getRepository(Equipesadmin::class);

        $repositoryEdition = $this->getDoctrine()
            ->getManager()
            ->getRepository(Edition::class);
        $repositoryVideosequipes = $this->getDoctrine()
            ->getManager()
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
            $em = $this->getDoctrine()->getManager();

            $em->remove($video);
            $em->flush();


            return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', ['infos' => $infos]);
        }


        if ($choix == 'nouvelle') {
            $videoequipe = new Videosequipes();
        }
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


        $form = $this->createFormBuilder($videoequipe)
            ->add('lien', TextType::class, ['empty_data' => $videoequipe->getLien()])
            ->add('nom', TextType::class, ['empty_data' => $videoequipe->getNom()])
            ->add('save', SubmitType::class, ['label' => 'Valider'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
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

    /**
     * @IsGranted("ROLE_PROF")
     *
     * @Route("/videos/liste_videos,{infos}", name="videos_liste_videos")
     *
     */
    public function liste_videos(Request $request, $infos)
    {
        $repositoryEquipesadmin = $this->getDoctrine()
            ->getManager()
            ->getRepository(Equipesadmin::class);

        $repositoryEdition = $this->getDoctrine()
            ->getManager()
            ->getRepository(Edition::class);
        $repositoryVideosequipes = $this->getDoctrine()
            ->getManager()
            ->getRepository(Videosequipes::class);
        $edition = $repositoryEdition->findOneBy([], ['id' => 'desc']);
        $Infos = explode('-', $infos);
        $id_equipe = $Infos[0];
        $concours = $Infos[1];
        $choix = $Infos[2];
        $equipe = $repositoryEquipesadmin->find(['id' => $id_equipe]);
        $qb = $repositoryVideosequipes->createQueryBuilder('v')
            ->where('v.equipe =:equipe')
            ->setParameter('equipe', $equipe)
            ->orderBy('v.nom', 'ASC');
        $liste_videos = $qb->getQuery()->getResult();

        if ($liste_videos == null) {
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'Pas de vidéo déposée pour  cette équipe');
            return $this->redirectToRoute('fichiers_choix_equipe', array('choix' => 'liste_video'));


        }
        $user = $this->getUser();

        $roles = $user->getRoles();
        $role = $roles[0];
        $i = 0;

        if ($liste_videos != null) {
            foreach ($liste_videos as $video) {
                $id = $video->getId();
                $formBuilder[$i] = $this->get('form.factory')->createNamedBuilder('Form' . $i, FormType::class, $video);
                $formBuilder[$i]->add('id', HiddenType::class, ['data' => $video->getId()])
                    ->add('change', SubmitType::class, ['label' => 'Modifier'])
                    ->add('delete', SubmitType::class, ['label' => 'Supprimer'])
                    ->getForm();
                $Form[$i] = $formBuilder[$i]->getForm();
                $formtab[$i] = $Form[$i]->createView();


                if ($request->isMethod('POST')) {
                    if ($request->request->has('Form' . $i)) {

                        if (isset($request->request->get('Form' . $i)['change'])) {

                            $id_video = $Form[$i]->get('id')->getData();
                            $infos = $equipe->getId() . '-' . $concours . '-modifier-' . $id_video;
                            return $this->redirectToRoute('videos_liens_videos', array('infos' => $infos));

                        }

                        if (isset($request->request->get('Form' . $i)['delete'])) {
                            $id_video = $Form[$i]->get('id')->getData();

                            $infos = $equipe->getId() . '-' . $concours . '-supprimer-' . $id_video;


                            return $this->redirectToRoute('videos_confirme_supr_video', ['infos' => $infos]);
                        }
                    }


                }
                $i = $i + 1;
            }
            $content = $this
                ->renderView('adminfichiers\affiche_liste_videos.html.twig',
                    array('liste_videos' => $liste_videos,
                        'edition' => $edition,
                        'concours' => $concours,
                        'equipe' => $equipe,
                        'role' => $role,
                        'formtab' => $formtab
                    ));

            return new Response($content);
        }


    }

    /**
     * @IsGranted("ROLE_PROF")
     *
     * @Route("/videos/confirme_supr_video,{infos}", name="videos_confirme_supr_video")
     *
     */
    public function confirme_supr_video(Request $request, $infos)
    {

        $repositoryVideosequipes = $this->getDoctrine()
            ->getManager()
            ->getRepository(Videosequipes::class);
        $repositoryEquipesadmin = $this->getDoctrine()
            ->getManager()
            ->getRepository(Equipesadmin::class);

        $info = explode("-", $infos);
        $id_equipe = $info[0];
        $concours = $info[1];
        $choix = $info[2];
        $id_video = $info[3];
        $Equipe = $repositoryEquipesadmin->find(['id' => $id_equipe]);
        $video = $repositoryVideosequipes->find(['id' => $id_video]);

        $avertissement = 'Voulez-vous supprimer cette vidéo : ' . $video->getNom() . '?';


        $form3 = $this->createForm(ConfirmType::class);
        $form3->handleRequest($request);
        if ($form3->isSubmitted() && $form3->isValid()) {
            $filesystem = new Filesystem();
            if ($form3->get('OUI')->isClicked()) {
                $em = $this->getDoctrine()->getManager();

                $em->remove($video);
                $em->flush();


                return $this->redirectToRoute('videos_liste_videos', ['infos' => $infos]);
            }
            if ($form3->get('NON')->isClicked()) {

                return $this->redirectToRoute('videos_liste_videos', ['infos' => $infos]);
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('info', $avertissement . ' Cette opération est définitive, sans possibilité de récupération.');
        $content = $this
            ->renderView('adminfichiers\confirm_supr_video.html.twig', array(
                'form' => $form3->createView(),
                'equipe' => $Equipe,

            ));
        return new Response($content);
    }
}