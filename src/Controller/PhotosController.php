<?php

namespace App\Controller;


use App\Entity\Centrescia;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Photos;
use App\Form\ConfirmType;
use App\Form\PhotosType;
use datetime;
use ImagickException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class PhotosController extends AbstractController
{
    private RequestStack $requestStack;
    private \Doctrine\Persistence\ManagerRegistry $doctrine;


    public function __construct(RequestStack $requestStack, \Doctrine\Persistence\ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }


    /**
     * @IsGranted("ROLE_PROF")
     *
     * @Route("/photos/deposephotos,{concours}", name="photos_deposephotos")
     *
     * @throws ImagickException
     */
    public function deposephotos(Request $request, ValidatorInterface $validator, $concours)
    {
        $em = $this->doctrine->getManager();

        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);


        $editionId = $this->requestStack->getSession()->get('edition')->getId();
        $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['id' => $editionId]);


        $user = $this->getUser();
        $id_user = $user->getId();
        $roles = $user->getRoles();
        $role = $roles[0];

        $Photos = new Photos();
//$Photos->setSession($session);
        $form = $this->createForm(PhotosType::class, ['concours' => $concours, 'role' => $role, 'prof' => $user]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $equipe = $form->get('equipe')->getData();
//$equipe=$repositoryEquipesadmin->findOneBy(['id'=>$id_equipe]);
            $nom_equipe = $equipe->getTitreProjet();

            $numero_equipe = $equipe->getNumero();
            $files = $form->get('photoFiles')->getData();
            $editionpassee = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $edition->getEd()]);
            $equipepassee = $this->doctrine->getRepository(OdpfEquipesPassees::class)->findOneBy(['editionspassees' => $editionpassee, 'numero' => $equipe->getNumero()]);

            if ($files) {
                $nombre = count($files);
                $fichiers_erreurs = [];
                $i = 0;
                foreach ($files as $file) {
                    $ext = $file->guessExtension();
                    $violations = $validator->validate(
                        $file,
                        [
                            new NotBlank(),
                            new File([
                                'maxSize' => '7000k',
                            ])
                        ]
                    );
                    if (($violations->count() > 0) or ($ext != 'jpg')) {
                        $violation = '';
                        /** @var ConstraintViolation $violation */
                        if (isset($violations[0])) {
                            $violation = 'fichier de taille supérieure à 7 M';
                        }
                        if ($ext != 'jpg') {
                            $violation = $violation . ':  fichier non jpeg ';
                        }
                        $fichiers_erreurs[$i] = $file->getClientOriginalName() . ' : ' . $violation;
                        $i++;
                    } else {
                        $photo = new Photos();


                        $photo->setEdition($edition);
                        $photo->setEditionspassees($editionpassee);
                        if ($concours == 'inter') {
                            $photo->setNational(FALSE);
                        }
                        if ($concours == 'cn') {

                            $photo->setNational(TRUE);
                        }
                        $photo->setPhotoFile($file);//Vichuploader gère l'enregistrement dans le bon dossier, le renommage du fichier
                        $photo->setEquipe($equipe);
                        $photo->setEquipepassee($equipepassee);
                        $em->persist($photo);
                        $em->flush();

                    }
                }

                if (count($fichiers_erreurs) == 0) {
                    if ($nombre == 1) {
                        $message = 'Votre fichier a bien été déposé. Merci !';
                    } else {
                        $message = 'Vos fichiers ont bien été déposés. Merci !';
                    }
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', $message);
                } else {
                    $message = '';


                    foreach ($fichiers_erreurs as $erreur) {
                        $message = $message . $erreur . ', ';
                    }
                    if (count($fichiers_erreurs) == 1) {
                        $message = $message . ' n\'a pas pu être déposé';
                    }
                    if (count($fichiers_erreurs) > 1) {
                        $message = $message . ' n\'ont pas pu être déposés';
                    }


                    $request->getSession()
                        ->getFlashBag()
                        ->add('alert', 'Des erreurs ont été constatées : ' . $message);

                }
            }
            if (!$files) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('alert', 'Pas fichier sélectionné: aucun dépôt effectué !');
            }
            return $this->redirectToRoute('photos_deposephotos', array('concours' => $concours));
        }
        $Form = $form->createView();

        return $this->render('photos/deposephotos.html.twig', [
            'form' => $Form, 'edition' => $edition, 'concours' => $concours, 'role' => $role
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @Route("/photos/choixedition", name="photos_choixedition")
     *
     */
    public function choixedition(Request $request)
    {
        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);
        $qb = $repositoryEdition->createQueryBuilder('e')
            ->orderBy('e.ed', 'DESC');
        $Editions = $qb->getQuery()->getResult();
        return $this->render('photos/choix_edition.html.twig', [
            'editions' => $Editions]);
    }

    /**
     *
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/photos/voirphotoscia,{editionchoix}", name="photos_voirphotoscia")
     *
     */
    public function voirphotoscia(Request $request, $editionchoix)
    {
        $edition = explode('-', $editionchoix)[0];
        $choix = explode('-', $editionchoix)[1];
        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);
        $repositoryCentrescia = $this->doctrine
            ->getManager()
            ->getRepository(Centrescia::class);
        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);
        $Edition_en_cours = $this->requestStack->getSession()->get('edition');

        $Edition = $repositoryEdition->find(['id' => $edition]);
        $user = $this->getUser();
        if ($user) {
            $id_user = $user->getId();
            $roles = $user->getRoles();
            $role = $roles[0];

        } else {
            $role = 'IS_GRANTED_ANONIMOUSLY';

        }

        $liste_centres = $repositoryCentrescia->findAll();
        $qb = $repositoryPhotos->createQueryBuilder('p')
            ->andWhere('p.edition =:edition')
            ->andWhere('p.national =:national')
            ->setParameter('edition', $Edition)
            ->setParameter('national', 'FALSE');


        $date = new datetime('now');


        $liste_photos = $qb->getQuery()->getResult();
        if ($liste_photos) {

            if (($role != 'ROLE_COMITE') and ($role != 'ROLE_ORGACIA') and ($role != 'ROLE_SUPER_ADMIN')) {

                $publiable = TRUE;
                if ($Edition_en_cours == $Edition) {

                    if (($date < $Edition_en_cours->getConcourscia())) {
                        $publiable = FALSE;
                    }
                }
                if ($publiable == TRUE) {
                    return $this->render('photos/affiche_photos_cia.html.twig', [
                        'liste_photos' => $liste_photos, 'edition' => $Edition, 'liste_centres' => $liste_centres, 'concours' => 'cia', 'choix' => $choix]);

                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Pas de photo des épreuves interacadémiques publiée pour l\'édition ' . $Edition->getEd() . ' à ce jour');
                    return $this->redirectToRoute('photos_choixedition');
                }
            } else {
                return $this->render('photos/affiche_photos_cia.html.twig', [
                    'liste_photos' => $liste_photos, 'edition' => $Edition, 'liste_centres' => $liste_centres, 'concours' => 'cia', 'id_user' => $id_user, 'choix' => $choix]);
            }

        } else {
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'Pas de photo des épreuves interacadémiques publiée pour l\'édition ' . $Edition->getEd() . ' à ce jour');
            return $this->redirectToRoute('photos_choixedition');
        }


    }

    /**
     *
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/photos/voirphotoscn, {editionchoix}", name="photos_voirphotoscn")
     *
     */
    public function voirphotoscn(Request $request, $editionchoix)
    {
        $edition = explode('-', $editionchoix)[0];
        $choix = explode('-', $editionchoix)[1];

        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);

        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);


        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);
        $Edition_en_cours = $this->requestStack->getSession()->get('edition');
        $Edition = $repositoryEdition->find(['id' => $edition]);
        $user = $this->getUser();
        if ($user) {
            $id_user = $user->getId();
            $roles = $user->getRoles();
            $role = $roles[0];

        } else {
            $role = 'IS_GRANTED_ANONIMOUSLY';

        }

        $qb1 = $repositoryEquipesadmin->createQueryBuilder('e')
            ->where('e.selectionnee = TRUE')
            ->orderBy('e.lettre', 'ASC');
        $liste_equipes = $qb1->getQuery()->getResult();


        $qb2 = $repositoryPhotos->createQueryBuilder('p')
            ->leftJoin('p.equipe', 'e')
            ->andWhere('e.selectionnee = TRUE')
            ->orderBy('e.lettre', 'ASC')
            ->andWhere('p.national = TRUE')
            ->andWhere('p.edition =:edition')
            ->setParameter('edition', $Edition);

        $liste_photos = $qb2->getQuery()->getResult();
        $date = new datetime('now');
//dd($liste_photos);
//$liste_photos=$repositoryPhotosinter->findByEdition(['edition'=>$edition]);
        if ($liste_photos)
            if (($role != 'ROLE_COMITE') and ($role != 'ROLE_ORGACIA') and ($role != 'ROLE_SUPER_ADMIN')) {

                $publiable = TRUE;
                if ($Edition_en_cours == $Edition) {
                    if (($date < $Edition_en_cours->getConcourscn())) {
                        $publiable = FALSE;
                    }
                }
                if ($publiable == TRUE) {
                    return $this->render('photos/affiche_photos_cn.html.twig', ['liste_photos' => $liste_photos, 'edition' => $Edition, 'liste_equipes' => $liste_equipes, 'concours' => 'national', 'choix' => $choix]);
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Pas de photo des épreuves inationales publiée pour l\'édition ' . $Edition->getEd() . ' à ce jour');
                    return $this->redirectToRoute('archives_fichiers_photos', ['choix' => $choix]);
                }
            } else {
                return $this->render('photos/affiche_photos_cn.html.twig', ['liste_photos' => $liste_photos, 'edition' => $Edition, 'liste_equipes' => $liste_equipes, 'concours' => 'national', 'choix' => $choix]);
            }

        if (!$liste_photos) {
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'Pas de photo du concours national publiée pour l\'édition ' . $Edition->getEd() . ' à ce jour');
            return $this->redirectToRoute('archives_fichiers_photos', ['choix' => $choix]);
        }
    }

    /**
     *
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/photos/galleryphotos, {infos}", name="photos_galleryphotos")
     *
     */
    public function galleryphotos(Request $request, $infos)
    {
        $choix = explode('-', $infos)[3];
        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);

        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);


        $repositoryCentrescia = $this->doctrine
            ->getManager()
            ->getRepository(Centrescia::class);
        $concourseditioncentre = explode('-', $infos);
        $concours = $concourseditioncentre[0];
        $Edition = $repositoryEdition->find(['id' => $concourseditioncentre[1]]);

        if ($concours == 'inter') {
            $centre = $repositoryCentrescia->find(['id' => $concourseditioncentre[2]]);

            $qb = $repositoryEquipesadmin->createQueryBuilder('e')
                ->where('e.centre=:centre')
                ->setParameter('centre', $centre);
            $liste_equipes = $qb->getQuery()->getResult();

            $qb2 = $repositoryPhotos->createQueryBuilder('p')
                ->join('p.equipe', 'r')
                ->andWhere('p.edition =:edition')
                ->setParameter('edition', $Edition)
                ->andWhere('r.centre =:centre')
                ->setParameter('centre', $centre)
                ->orderBy('r.numero', 'ASC')
                ->andWhere('p.national = FALSE');
            $liste_photos = $qb2->getQuery()->getResult();

        }

        if ($concours == 'national') {

            $equipe = $repositoryEquipesadmin->findOneBy(['id' => $concourseditioncentre[2]]);
            $qb = $repositoryPhotos->createQueryBuilder('p')
                ->andWhere('p.equipe =:equipe')
                ->setParameter('equipe', $equipe)
                ->andWhere('p.edition =:edition')
                ->setParameter('edition', $Edition)
                ->andWhere('p.national = TRUE');

            $liste_photos = $qb->getQuery()->getResult();
        }

        if ($concours == 'cia') {
            $content = $this
                ->renderView('photos/liste_photos_cia_carrousels.html.twig', array('liste_photos' => $liste_photos, 'edition' => $Edition, 'centre' => $centre,
                    'liste_equipes' => $liste_equipes, 'concours' => 'cia', 'choix' => $choix));
            return new Response($content);
        }

        if ($concours == 'national') {
            $content = $this
                ->renderView('photos/liste_photos_cn_carrousels.html.twig', array('liste_photos' => $liste_photos,
                    'edition' => $Edition, 'equipe' => $equipe, 'concours' => 'national', 'choix' => $choix));
            return new Response($content);
        }
    }

    /**
     *
     * @IsGranted("ROLE_PROF")
     * @Route("/photos/gestion_photos, {infos}", name="photos_gestion_photos")
     *
     */
    public function gestion_photos(Request $request, $infos)
    {
        $choix = explode('-', $infos)[3];


        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);

        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);


        $repositoryCentrescia = $this->doctrine
            ->getManager()
            ->getRepository(Centrescia::class);
        $user = $this->getUser();
        $id_user = $user->getId();
        $roles = $user->getRoles();
        $role = $roles[0];
        $concourseditioncentre = explode('-', $infos);
        $concours = $concourseditioncentre[0];
        $idedition = $repositoryEdition->find(['id' => $concourseditioncentre[1]]);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);

        if ($concours == 'inter') {
            $qb = $repositoryEquipesadmin->createQueryBuilder('e')
                ->andWhere('e.edition =:edition')
                ->setParameter('edition', $edition)
                ->addOrderBy('e.numero', 'ASC');

            $centre = $repositoryCentrescia->find(['id' => $concourseditioncentre[2]]);
            if ($centre == null) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('info', 'Les centres interacadémiques ne sont pas encore attribués pour la ' . $edition->getEd() . 'e édition');
                $this->redirectToRoute('core_home');
            }
            if (($role == 'ROLE_ORGACIA') or ($role == 'ROLE_SUPER_ADMIN') or ($role == 'ROLE_COMITE')) {
                $ville = $centre->getCentre();
                $qb->andWhere('e.centre=:centre')
                    ->setParameter('centre', $centre);
            }
            if ($role == 'ROLE_PROF') {
                $ville = 'prof';
                $qb->andWhere('e.idProf1 =:prof or e.idProf2 =:prof')
                    ->setParameter('prof', $id_user);


            }

            $liste_equipes = $qb->getQuery()->getResult();


            $qb2 = $repositoryPhotos->createQueryBuilder('p')
                ->andWhere('p.national =:valeur')
                ->setParameter('valeur', '0')
                ->andWhere('p.edition =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('p.equipe in(:equipes)')
                ->setParameter('equipes', $liste_equipes)
                ->leftJoin('p.equipe', 'e')
                ->addOrderBy('e.numero', 'ASC');


            /* if ($role=='ROLE_PROF'){
            $qb2->leftJoin('p.equipe','e')
            ->andWhere('e.idProf1 =:prof1')
            ->setParameter('prof1',$id_user)
            ->orWhere('e.idProf2 =:prof2')
            ->setParameter('prof2',$id_user);
            }*/
            $liste_photos = $qb2->getQuery()->getResult();


        }

        if ($concours == 'national') {

            $equipe = $repositoryEquipesadmin->findOneBy(['id' => $concourseditioncentre[2]]);

            $qb2 = $repositoryPhotos->createQueryBuilder('p')
                ->where('p.equipe =:equipe')
                ->andWhere('p.edition =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('p.national = 1')
                ->setParameter('equipe', $equipe);
            if ($role == 'ROLE_PROF') {
                $equipes = $repositoryEquipesadmin->createQueryBuilder('eq')
                    ->andWhere('eq.selectionnee = TRUE')
                    ->andWhere('eq.idProf1 =:prof or eq.idProf2 =:prof')
                    ->setParameter('prof', $id_user)
                    ->getQuery()->getResult();


                $qb2 = $repositoryPhotos->createQueryBuilder('p')
                    ->andWhere('p.national =:valeur')
                    ->setParameter('valeur', '1')
                    ->andWhere('p.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->andWhere('p.equipe in(:equipes)')
                    ->setParameter('equipes', $equipes)
                    ->leftJoin('p.equipe', 'e')
                    ->addOrderBy('e.lettre', 'ASC');


            }
            $liste_photos = $qb2->getQuery()->getResult();
            if (!$liste_photos) {
                $request->getSession()
                    ->getFlashBag()
                    ->add('info', 'Pas de photo pour le concours ' . $concours . ' de l\'édition ' . $edition->getEd() . ' à ce jour');
                $this->redirectToRoute('core_home');
            }
        }
        $i = 0;
        foreach ($liste_photos as $photo) {
            $id = $photo->getId();
            $form[$i] = $this->createForm(FormType::class, $photo);
//if($photo->getComent()==null){$data=$photo->getEquipe()->getTitreProjet();}
//else {$data=$photo->getComent();}
            $form[$i]->add('id', HiddenType::class, ['disabled' => true, 'data' => $id, 'label' => false])
                ->add('coment', TextType::class, [

                    'required' => false,
// 'data'=>$data
                ]);
            if ($concours == 'inter') {
                $form[$i]->add('equipe', EntityType::class, [
                    'class' => Equipesadmin::class,
                    'query_builder' => $qb,

                    'choice_label' => 'getInfoequipe',
                    'label' => 'Choisir une équipe',
                    'mapped' => true,

                ]);
            }
            $form[$i]->add('sauver', SubmitType::class)
                ->add('effacer', SubmitType::class);


            $form[$i]->handleRequest($request);
            $formtab[$i] = $form[$i]->createView();

            if ($form[$i]->isSubmitted() && $form[$i]->isValid()) {
                $photo = $repositoryPhotos->find(['id' => $id]);

                if ($form[$i]->get('sauver')->isClicked()) {

                    $em = $this->doctrine->getManager();
                    $photo->setComent($form[$i]->get('coment')->getData());
                    if ($concours == 'cn') {
                        $photo->setEquipe($form[$i]->get('equipe')->getData());
                    }
                    $em->persist($photo);
                    $em->flush();

                    return $this->redirectToRoute('photos_gestion_photos', array('infos' => $infos));


                }
                if ($form[$i]->get('effacer')->isClicked()) {
                    return $this->redirectToRoute('photos_confirme_efface_photo', array('concours_photoid_infos' => $concours . ':' . $photo->getId() . ':' . $infos));

                }

            }


            $i = $i + 1;

        }
        if (!isset($formtab)) {
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'Vous n\'avez pas déposé de photo pour le concours ' . $concours . ' de l\'édition ' . $edition->getEd() . ' à ce jour');
            return $this->redirectToRoute('core_home');


        }

        if ($concours == 'inter') {
            $content = $this
                ->renderView('photos/gestion_photos_cia.html.twig', array('formtab' => $formtab,
                    'liste_photos' => $liste_photos, 'centre' => $ville, 'choix' => $choix,
                    'edition' => $edition, 'liste_equipes' => $liste_equipes, 'concours' => 'cia', 'role' => $role));
            return new Response($content);
        }

        if ($concours == 'national') {
            $content = $this
                ->renderView('photos/gestion_photos_cn.html.twig', array('formtab' => $formtab, 'liste_photos' => $liste_photos,
                    'edition' => $edition, 'equipe' => $equipe, 'concours' => 'national', 'role' => $role, 'choix' => $choix));
            return new Response($content);
        }

    }

    /**
     *
     * @IsGranted("ROLE_PROF")
     * @Route("/photos/confirme_efface_photo, {concours_photoid_infos}", name="photos_confirme_efface_photo")
     *
     */
    public function confirme_efface_photo(Request $request, $concours_photoid_infos)
    {
        $filesystem = new Filesystem();
        $photoid_concours = explode(':', $concours_photoid_infos);
        $photoId = $photoid_concours[1];
        $concours = $photoid_concours[0];
        $infos = $photoid_concours[2];


        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);

        $photo = $repositoryPhotos->find(['id' => $photoId]);


        $Form = $this->createForm(ConfirmType::class);
        $Form->handleRequest($request);
        $form = $Form->createView();
        if ($Form->isSubmitted() && $Form->isValid()) {

            if ($Form->get('OUI')->isClicked()) {

                $em = $this->doctrine->getManager();
                $em->remove($photo);
                $em->flush();
                $filesystem->remove('/upload/photos/thumbs/' . $photo->getPhoto());
                return $this->redirectToRoute('photos_gestion_photos', array('infos' => $infos));
            }
            if ($Form->get('NON')->isClicked()) {
                return $this->redirectToRoute('photos_gestion_photos', array('infos' => $infos));
            }
        }


        $content = $this->renderView('/photos/confirm_supprimer.html.twig', array('form' => $form, 'photo' => $photo, 'concours' => $concours));
        return new Response($content);


    }

    /**
     *
     *
     * @Route("/photos/voirgalerie {infos}", name="photos_voir_galerie")
     *
     */

    public function voirgalerie(Request $request, $infos)
    {
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);
        if (explode('-', $infos)[0] == 'equipe') {
            $idEquipe = explode('-', $infos)[1];
            $equipe = $this->doctrine->getRepository(OdpfEquipesPassees::class)->findOneBy(['id' => $idEquipe]);
            $photos = $repositoryPhotos->findBy(['equipepassee' => $equipe]);
            $listeEquipes = [$equipe];
            $edition = $equipe->getEditionspassees();
            return $this->render('photos/affiche_galerie_equipe.html.twig', ['photos' => $photos, 'liste_equipes' => $listeEquipes, 'edition' => $edition]);

        }
        if (explode('-', $infos)[0] == 'edition') {

            $idEdition = explode('-', $infos)[1];
            $edition = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['id' => $idEdition]);
            $listeEquipes = $this->doctrine->getRepository(OdpfEquipesPassees::class)->findBy(['editionspassees' => $edition]);
            foreach ($listeEquipes as $equipe) {
                $listPhotos = $repositoryPhotos->createQueryBuilder('p')
                    ->andWhere('p.equipepassee =:equipe')
                    ->setParameter('equipe', $equipe)
                    ->getQuery()->getResult();

                if (null != $listPhotos) {
                    $rand_keys = array_rand($listPhotos, 1);
                    $photos[$equipe->getNumero()] = $listPhotos[$rand_keys];
                }

            }
            return $this->render('photos/affiche_galerie_edition.html.twig', ['photos' => $photos, 'liste_equipes' => $listeEquipes, 'edition' => $edition]);

        };

    }

}

