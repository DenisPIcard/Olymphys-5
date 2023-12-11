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
use Imagick;
use ImagickException;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\DateTime;
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

    #[IsGranted("ROLE_PROF")]
    #[Route("/photos/deposephotos,{concours}", name: "photos_deposephotos")]
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
        in_array('ROLE_PROF', $roles) ? $role = 'ROLE_PROF' : $role = 'ROLE_COMITE';
        in_array('ROLE_ORGACIA', $roles) ? $centre = $user->getCentrecia()->getCentre() : $centre = '';
        $photos = new Photos();
        //$photos->setEdition($edition);
//$Photos->setSession($session);
        $form = $this->createForm(PhotosType::class, ['concours' => $concours, 'role' => $role, 'prof' => $user, 'centre' => $centre]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $equipe = $form->get('equipe')->getData();
//$equipe=$repositoryEquipesadmin->findOneBy(['id'=>$id_equipe]);
            $nom_equipe = $equipe->getTitreProjet();
            $edition = $equipe->getEdition();
            $numero_equipe = $equipe->getNumero();
            $files = $form->get('photoFiles')->getData();
            $editionpassee = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $edition->getEd()]);
            $equipepassee = $this->doctrine->getRepository(OdpfEquipesPassees::class)->findOneBy(['editionspassees' => $editionpassee, 'numero' => $equipe->getNumero()]);
            $type = true;
            if ($files) {
                $nombre = count($files);
                $fichiers_erreurs = [];
                $i = 0;
                foreach ($files as $file) {
                    $violations = $validator->validate(
                        $file,
                        [
                            new NotBlank(),
                            new File([
                                'maxSize' => '7500k',
                            ])
                        ]
                    );
                    $typeImage = $file->guessExtension();//Les .HEIC donnent jpg
                    $originalFilename = $file->getClientOriginalName();
                    $parsedName = explode('.', $originalFilename);
                    $ext = end($parsedName);// détecte les .JPG et .HEIC

                    if (($typeImage != 'jpg') or ($ext != 'jpg')) {// on transforme  le fichier en .JPG
                        //dd('OK');
                        $nameExtLess = $parsedName[0];
                        $imax = count($parsedName);
                        for ($i = 1; $i <= $imax - 2; $i++) {// dans le cas où le nom de  fichier comporte plusieurs points
                            $nameExtLess = $nameExtLess . '.' . $parsedName[$i];
                        }
                        try {//on dépose le fichier dans le temp
                            $file->move(
                                'temp/',
                                $originalFilename
                            );
                        } catch (FileException $e) {

                        }
                        $this->setImageType($originalFilename, $nameExtLess, 'temp/');//appelle de la fonction de transformation de la compression

                        if (isset($_REQUEST['erreur'])) {

                            unlink('temp/' . $originalFilename);
                            $type = false;
                        }
                        if (!isset($_REQUEST['erreur'])) {
                            $type = true;
                            if (file_exists('temp/' . $nameExtLess . '.jpg')) {
                                $size = filesize('temp/' . $nameExtLess . '.jpg');
                            } else($size = 10000000);
                            $file = new UploadedFile('temp/' . $nameExtLess . '.jpg', $nameExtLess . '.jpg', $size, null, true);
                            unlink('temp/' . $originalFilename);
                        }
                    }


                    if (($violations->count() > 0) or ($type == false)) {
                        $violation = '';
                        /** @var ConstraintViolation $violation */
                        if (isset($violations[0])) {
                            $violation = 'fichier de taille supérieure à 7 M';
                        }
                        /*if ($ext != 'jpg') {
                            $violation = $violation . ':  fichier non jpeg ';
                        }*/
                        $fichiers_erreurs[$i] = $file->getClientOriginalName() . ' : ' . $violation;
                        $i++;
                    } else {
                        $photo = new Photos();
                        $photo->setEdition($edition);
                        $photo->setEditionspassees($editionpassee);
                        if (($equipe->getLettre() === null) or ($concours == 'inter')) {//Un membre du comité peut vouloir déposer une photo interacadémique lors du concours national
                            $photo->setNational(FALSE);
                        }
                        if (($equipe->getLettre() !== null) or ($concours == 'cn')) {

                            $photo->setNational(TRUE);
                        }
                        if ($equipe->getNumero() >= 100) { //ces "équipes" sont des équipes technique remise des prix, ambiance du concours, etc, ...
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
            'form' => $Form, 'edition' => $edition, 'concours' => $concours,
        ]);
    }

    public function setImageType($image, $nameExtLess, $path)
    {
        try {
            $imageOrig = new Imagick($path . $image);
            $imageOrig->readImage($path . $image);
            $imageOrig->setImageCompression(Imagick::COMPRESSION_JPEG);
            $imageOrig->setType(Imagick::IMGTYPE_TRUECOLOR);

            $imageOrig->writeImage($path . $nameExtLess . '.jpg');
        } catch (\Exception $e) {


            $_REQUEST['erreur'] = 'yes';

        }

    }

    #[Isgranted("ROLE_PROF")]
    #[Route("/photos/gestion_photos, {infos}", name: "photos_gestion_photos")]
    public function gestion_photos(Request $request, $infos)
    {
        $choix = explode('-', $infos)[3];
        $roles = $this->getUser()->getRoles();

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

        $concourseditioncentre = explode('-', $infos);
        $concours = $concourseditioncentre[0];
        $editionN = $repositoryEdition->find(['id' => $concourseditioncentre[1]]);
        $editionN1 = $repositoryEdition->findOneBy(['ed' => $editionN->getEd() - 1]);
        new DateTime('now') >= $this->requestStack->getSession()->get('ouverturesite') ? $edition = $editionN : $edition = $editionN1;
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
            if ((in_array('ROLE_ORGACIA', $roles)) or (in_array('ROLE_SUPER_ADMIN', $roles)) or (in_array('ROLE_COMITE', $roles))) {
                $ville = $centre->getCentre();
                $qb->andWhere('e.centre=:centre')
                    ->setParameter('centre', $centre);
            }
            if (in_array('ROLE_PROF', $user->getRoles())) {
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

        if ($concours == 'cn') {

            $equipe = $repositoryEquipesadmin->findOneBy(['id' => $concourseditioncentre[2]]);

            $equipes = $repositoryEquipesadmin->createQueryBuilder('eq')
                ->andWhere('eq.selectionnee = TRUE')
                ->andWhere('eq.idProf1 =:prof or eq.idProf2 =:prof')
                ->setParameter('prof', $id_user)
                ->andWhere('eq.edition =:edition')
                ->setParameter('edition', $edition)
                ->getQuery()->getResult();

            $qb2 = $repositoryPhotos->createQueryBuilder('p')
                ->andWhere('p.national = 1')
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
            return $this->redirectToRoute('core_home');
        }

        $i = 0;
        foreach ($liste_photos as $photo) {
            $id = $photo->getId();
            $form[$i] = $this->createForm(FormType::class, $photo);
//if($photo->getComent()==null){$data=$photo->getEquipe()->getTitreProjet();}
//else {$data=$photo->getComent();}

            $form[$i]->add('equipe', EntityType::class, [
                'class' => Equipesadmin::class,
                'choices' => $equipes,
            ])
                ->add('id', HiddenType::class, ['disabled' => true, 'data' => $id, 'label' => false])
                ->add('coment', TextType::class, [
                    'required' => false,
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
            $request->getSession()->set('info', 'Vous n\'avez pas déposé de photo pour le concours ' . $concours . ' de l\'édition ' . $edition->getEd() . ' à ce jour');
            return $this->redirectToRoute('core_home');


        }

        if ($concours == 'inter') {
            $content = $this
                ->renderView('photos/gestion_photos_cia.html.twig', array('formtab' => $formtab,
                    'liste_photos' => $liste_photos, 'centre' => $ville, 'choix' => $choix,
                    'edition' => $edition, 'liste_equipes' => $liste_equipes, 'concours' => 'cia'));
            return new Response($content);
        }

        if ($concours == 'cn') {

            $content = $this
                ->renderView('photos/gestion_photos_cn.html.twig', array('formtab' => $formtab, 'liste_photos' => $liste_photos,
                    'edition' => $edition, 'equipe' => $equipe, 'concours' => 'national', 'choix' => $choix));
            return new Response($content);
        }

    }

    #[IsGranted("ROLE_PROF")]
    #[Route("/photos/confirme_efface_photo, {concours_photoid_infos}", name: "photos_confirme_efface_photo")]
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

    #[Route("/photos/voirgalerie {infos}", name: "photos_voir_galerie")]
    public function voirgalerie(Request $request, $infos)
    {
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);
        if (explode('-', $infos)[0] == 'equipe') {
            $idEquipe = explode('-', $infos)[1];

            $equipe = $this->doctrine->getRepository(OdpfEquipesPassees::class)->findOneBy(['id' => $idEquipe]);
            $edition = $equipe->getEditionspassees();
            $photosequipes = $this->getPhotosEquipes($edition);
            $photos = $repositoryPhotos->findBy(['equipepassee' => $equipe]);
            $listeEquipes = [$equipe];
            $edition = $equipe->getEditionspassees();
            return $this->render('photos/affiche_galerie_equipe.html.twig', ['photos' => $photos, 'liste_equipes' => $listeEquipes, 'edition' => $edition, 'photosequipes' => $photosequipes]);

        }
        if (explode('-', $infos)[0] == 'edition' or explode('-', $infos)[0] == 'editionEnCours') {

            $idEdition = explode('-', $infos)[1];

            if (explode('-', $infos)[0] == 'edition') {


                $edition = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['id' => $idEdition]);
            }
            if (explode('-', $infos)[0] == 'editionEnCours') {

                $editionEnCours = $this->doctrine->getRepository(Edition::class)->findOneBy(['id' => $idEdition]);

                $edition = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $editionEnCours->getEd()]);
            }
            $photos = $this->getPhotosEquipes($edition);
            $listeEquipes = $this->doctrine->getRepository(OdpfEquipesPassees::class)
                ->createQueryBuilder('e')
                ->andWhere('e.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->addOrderBy('e.numero', 'ASC')
                ->getQuery()->getResult();
            if (isset($photos)) {
                return $this->render('photos/affiche_galerie_edition.html.twig', ['photos' => $photos, 'liste_equipes' => $listeEquipes, 'edition' => $edition]);
            } else {

                return $this->redirectToRoute('core_home');

            }
        };

    }

    public function getPhotosEquipes($edition)
    {
        $repositoryPhotos = $this->doctrine
            ->getManager()
            ->getRepository(Photos::class);
        $listeEquipes = $this->doctrine->getRepository(OdpfEquipesPassees::class)
            ->createQueryBuilder('e')
            ->andWhere('e.editionspassees =:edition')
            ->setParameter('edition', $edition)
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();
        foreach ($listeEquipes as $equipe) {
            $listPhotos = $repositoryPhotos->createQueryBuilder('p')
                ->andWhere('p.equipepassee =:equipe')
                ->setParameter('equipe', $equipe)
                ->getQuery()->getResult();

            if (null != $listPhotos) {
                $rand_keys = array_rand($listPhotos, 1);
                $equipe->getNumero() !== null ? $photos[$equipe->getNumero()] = $listPhotos[$rand_keys] : $photos[$equipe->getLettre()] = $listPhotos[$rand_keys];
            }

        }
        return $photos;

    }

}

