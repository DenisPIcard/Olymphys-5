<?php

namespace App\Controller;

use App\Entity\Centrescia;
use App\Entity\Docequipes;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Jures;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Odpf\OdpfFichierspasses;
use App\Entity\Orgacia;
use App\Entity\Rne;
use App\Entity\User;
use App\Entity\Videosequipes;
use App\Form\ToutfichiersType;
use App\Service\valid_fichiers;
use datetime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ZipArchive;

class FichiersController extends AbstractController
{
    private RequestStack $requestStack;
    private ValidatorInterface $validator;
    private ParameterBagInterface $parameterBag;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ValidatorInterface $validator, ParameterBagInterface $parameterBag, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->validator = $validator;
        $this->parameterBag = $parameterBag;
        $this->doctrine = $doctrine;
    }


    /**
     * @Security("is_granted('ROLE_ORGACIA')")
     *
     * @Route("/fichiers/choix_centre", name="fichiers_choix_centre")
     *
     */
    public function choix_centre(Request $request)
    {
        $session = $this->requestStack->getSession();
        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
        $repositoryCentres = $this->doctrine
            ->getRepository(Centrescia::class);
        $repositoryEquipesAdmin = $this->doctrine
            ->getRepository(Equipesadmin::class);
        $edition = $session->get('edition');
        $centres = $repositoryCentres->createQueryBuilder('c')->addOrderBy('c.centre', 'ASC')->getQuery()->getResult();
        $equipes = $repositoryEquipesAdmin->findBy(['edition' => $edition]);
        if ($equipes != null) {
            foreach ($centres as $centre) {
                foreach ($equipes as $equipe) {
                    if ($centre == $equipe->getCentre()) {
                        $liste_centres[$centre->getCentre()] = $centre;

                    }

                }
            }
        }

        if (isset($liste_centres)) {
            $content = $this
                ->renderView('adminfichiers\choix_centre.html.twig', array(
                        'liste_centres' => $liste_centres
                    )
                );
            return new Response($content);
        } else {
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'Pas encore de centre attribué pour le  concours interacadémique de l\'édition ' . $edition->getEd());
            return $this->redirectToRoute('core_home');


        }
    }

    /**
     * @Security("is_granted('ROLE_PROF')")
     *
     * @Route("/fichiers/choix_equipe, {choix}", name="fichiers_choix_equipe")
     *
     */
    public function choix_equipe(Request $request, $choix)
    {

        $session = $this->requestStack->getSession();
        $repositoryEquipesadmin = $this->doctrine
            ->getRepository(Equipesadmin::class);
        $repositoryCentres = $this->doctrine
            ->getRepository(Centrescia::class);
        $repositoryDocequipes = $this->doctrine
            ->getRepository(Docequipes::class);
        $editionN = $session->get('edition');
        $editionN1=$this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$editionN->getEd()-1]);
        $docequipes = $repositoryDocequipes->findAll();

        $dateconnect = new datetime('now');
        $dateciaN1 = $editionN1->getConcourscia();
        $dateOuvertureSite=$editionN->getDateouverturesite();
        $dateconnect > $dateciaN1 and $dateconnect<$dateOuvertureSite? $phase = 'national' : $phase = 'interacadémique';
        $user = $this->getUser();
        $roles = $user->getRoles();
        $jure = null;
        $rne_objet = null;
        $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $choix]);
        $centre === null ?: $phase = 'interacadémique';
        if (in_array('ROLE_ORGACIA', $user->getRoles())) {
            $centre = $this->doctrine->getRepository(Orgacia::class)->findOneBy(['user' => $user])->getCentre();
            $phase = 'interacadémique';
        }
        if (in_array('ROLE_PROF', $user->getRoles())) {
            if ( $user->getRneId()) {
                $rne_objet = $this->doctrine->getRepository(Rne::class)->find(['id' => $user->getRneId()]);
            }
        }
        if (in_array('ROLE_JURY', $roles)) {
            $repositoryJures = $this->doctrine->getRepository(Jures::class);
            $jure = $repositoryJures->findOneBy(['iduser' => $this->getUser()->getId()]);

        }

        $liste_equipes = $repositoryEquipesadmin->getListeEquipe($user, $phase, $choix, $centre);
        if ($liste_equipes != null) {
            $content = $this->renderView('adminfichiers\choix_equipe.html.twig', array(
                'liste_equipes' => $liste_equipes,
                'user' => $user,
                'phase' => $phase,
                'choix' => $choix,
                'jure' => $jure,
                'doc_equipes' => $docequipes,
                'rneObj' => $rne_objet,
                'centre' => $centre));
            return new Response($content);
        } else {
            $phase == 'interacadémique' ? $message = 'inscrite' : $message = 'selectionnée';
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'Pas encore d\'équipe ' . $message . ' pour la ' . $edition->getEd() . 'e edition');
            return $this->redirectToRoute('core_home');
        }
    }

    /**
     * @Security("is_granted('ROLE_PROF')")
     * @param Request $request
     * @param $infos
     * @param MailerInterface $mailer
     * @param ValidatorInterface $validator
     * @return RedirectResponse|Response
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     * @Route("/fichiers/charge_fichiers, {infos}", name="fichiers_charge_fichiers")
     */
    public function charge_fichiers(Request $request, $infos, MailerInterface $mailer, ValidatorInterface $validator)
    {
        $session = $this->requestStack->getSession();
        $repositoryFichiersequipes = $this->doctrine
            ->getRepository(Fichiersequipes::class);
        $repositoryOdpfFichiersequipes = $this->doctrine
            ->getRepository(OdpfFichierspasses::class);
        $repositoryEquipesadmin = $this->doctrine
            ->getRepository(Equipesadmin::class);
        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
        $repositoryEditionpassee = $this->doctrine
            ->getRepository(Edition::class);
        $repositoryUser = $this->doctrine
            ->getRepository(User::class);
        $repositoryEleve = $this->doctrine
            ->getRepository(Elevesinter::class);
        $validFichier = new valid_fichiers($this->validator, $this->parameterBag, $this->requestStack);

        //dd($_SERVER);
        $info = explode('-', $infos);

        $id_equipe = $info[0];
        $phase = $info[1];
        $choix = $info[2];
        $roles = $this->getUser()->getRoles();
        if (in_array('ROLE_PROF', $roles)) {
            if ($choix == 0 or $choix == 1 or $choix == 2) {

                if (($session->get('edition')->getDatelimcia() < new DateTime('now')) and ($session->get('concours') == 'interacadémique')) {
                    $this->addFlash('alert', 'La date limite de dépôt des fichiers est dépassée, veuillez contacter le comité!');
                    return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', [
                        'infos' => $infos,
                    ]);


                }
                if (($session->get('edition')->getDatelimnat() < new DateTime('now')) and ($session->get('concours') == 'national')) {
                    $this->addFlash('alert', 'La date limite de dépôt des fichiers est dépassée, veuillez contacter le comité!');
                    return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', [
                        'infos' => $infos,
                    ]);


                }


            }
        }
        if (count($info) >= 5) {//pour les autorisations photos
            $id_citoyen = $info[3];


            if ($id_equipe != 'prof') {//autorisations photos des élèves
                $attrib = $info[4];    //$attrib=0 si l'autorisation n'est pas encore déposée et  $attrib= 1 si l'autorisation a déjà été déposée et qu'on l'écrase
                $citoyen = $repositoryEleve->find(['id' => $id_citoyen]);
                $equipe = $repositoryEquipesadmin->find(['id' => $id_equipe]);
                $prof = false;

            } else {//autorisation photo des profs
                $citoyen = $repositoryUser->find(['id' => $id_citoyen]);
                $id_equipe = $info[4];
                $attrib = $info[5];
                $prof = true;
                $equipe = $repositoryEquipesadmin->findOneBy(['id' => $id_equipe]);
            }


        } else {
            $equipe = $repositoryEquipesadmin->find(['id' => $id_equipe]);
            $attrib = $info[3];
            if ($attrib == '1') {//upload d'un fichier FichierID est fourni par la fenêtre modale
                $idfichier = $request->query->get('FichierID');

                $fichier = $repositoryFichiersequipes->findOneBy(['id' => $idfichier]);
                if ($fichier != null) {
                    $choix = $repositoryFichiersequipes->findOneBy(['id' => $idfichier])->getTypefichier();

                } else {//Cela indique que le fichier n'est pas valide car valid_fichier fait disparaître les paramètres de $request->query
                    $idfichier = $session->get('idFichier');

                    $fichier = $repositoryFichiersequipes->findOneBy(['id' => $idfichier]);
                    $choix = $fichier->getTypefichier();// nécessaire dans le cas d'un upload de fichier non valide, valid_fichier fait disparaître les paramètres de $request->query

                }
                if ($choix == 6) {//nécessaire lors l'appel du dépôt d'une nouvelle autorisation

                    if ($request->query->get('FichierID') != null) {
                        $citoyen = $repositoryFichiersequipes->findOneBy(['id' => $idfichier])->getEleve();//pour les élèves
                        if ($citoyen === null) {
                            $citoyen = $repositoryFichiersequipes->findOneBy(['id' => $idfichier])->getProf();//pour les profs

                        }

                    } else {  //Cela indique que le fichier n'est pas valide car valid_fichier fait disparaître les paramètres de $request->query
                        $citoyen = $repositoryFichiersequipes->findOneBy(['id' => $idfichier])->getEleve();//pour les élèves
                        if ($citoyen === null) {
                            $citoyen = $repositoryFichiersequipes->findOneBy(['id' => $idfichier])->getProf();//pour les profs
                        }

                    }

                }
            }
        }


        $edition = $this->requestStack->getSession()->get('edition');

        $datelimnat = $edition->getDatelimnat();

        $dateconnect = new datetime('now');

        $form1 = $this->createForm(ToutfichiersType::class, ['choix' => $choix]);
        if (isset($equipe)) {
            $nom_equipe = $equipe->getTitreProjet();
            $lettre_equipe = $equipe->getLettre();

            $donnees_equipe = $lettre_equipe . ' - ' . $nom_equipe;

            if (!$lettre_equipe) {
                $numero_equipe = $equipe->getNumero();
                $nom_equipe = $equipe->getTitreProjet();
                $donnees_equipe = $numero_equipe . ' - ' . $nom_equipe;
            }
        } else {
            $donnees_equipe = $citoyen->getPrenom() . ' ' . $citoyen->getNom();


        }
        $form1->handleRequest($request);

        if ($form1->isSubmitted() && $form1->isValid()) {

            /** @var UploadedFile $file */
            $file = $form1->get('fichier')->getData();

            $num_type_fichier = $form1->get('choice')->getData();

            if (!isset($num_type_fichier)) {//sert pour les mémoires et annexes

                $this->addFlash('alert', 'Sélectionner le type de fichier !');
                return $this->redirectToRoute('fichiers_charge_fichiers', [
                    'infos' => $infos,
                ]);
            }

            $idFichier = null;
            if (isset($fichier)) {
                $idFichier = $fichier->getId();
                $fichier->getProf() == null ? $prof = false : $prof = true;
            }
            $violations = $validFichier->validation_fichiers($file, $num_type_fichier, $idFichier)['text'];
            if ($violations != '') {
                $request->getSession()
                    ->getFlashBag()
                    ->add('alert', $violations);
                return $this->redirectToRoute('fichiers_charge_fichiers', array('infos' => $infos));

            }
            $em = $this->doctrine->getManager();
            $edition = $this->requestStack->getSession()->get('edition');
            $edition = $em->merge($edition);
            if ($num_type_fichier == 6) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $slugger = new AsciiSlugger();
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move('temp/', $newFilename);

                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $fichier = $this->deposeAutorisations($newFilename, $citoyen, $attrib, $prof, $equipe);
                if ($fichier === null) {
                    $message = 'Une erreur est survenue, le fichier n\'a pas été déposé, veuillez prévenir l\'administrateur du site';
                    $this->requestStack->getCurrentRequest()->getSession()
                        ->getFlashBag()
                        ->add('alert', $message);
                    return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-' . $this->requestStack->getSession()->get('concours') . '-liste_prof'));

                }
                $message = '';
                $nom_fichier = $fichier->getFichier();
            } else {
                if ($attrib == 0) {

                    $fichier = new Fichiersequipes();

                }
                if ($attrib > 0) {
                    $fichier = $repositoryFichiersequipes->findOneBy(['id' => $idfichier]);
                    $message = '';
                    if ($session->get('concours') == 'national') {
                        $fichier->setNational(true);
                    }
                }
                $fichier->setFichierFile($file);

                if ($attrib == 0) {

                    if ($session->get('concours') == 'national') { //on vérifie que le fichier cia existe et on écrase sans demande de confirmation ce fichier  par le fichier national  sauf les autorisations photos et fiche sécurité
                        if ($num_type_fichier < 6) {
                            try {
                                $fichier = $repositoryFichiersequipes->createQueryBuilder('f')
                                    ->where('f.equipe=:equipe')
                                    ->setParameter('equipe', $equipe)
                                    ->andWhere('f.typefichier =:type')
                                    ->setParameter('type', $num_type_fichier)
                                    ->andWhere('f.national =:valeur')
                                    ->setParameter('valeur', '0')
                                    ->getQuery()->getSingleResult();

                            } catch (Exception $e) {// précaution pour éviter une erreur dans le cas du manque du fichier cia, ce qui arrive souvent pour les résumés, annexes, fiche sécurité,
                                $message = '';
                                $fichier = new Fichiersequipes();
                                $nouveau = true;
                            }
                            if (!isset($nouveau)) {
                                $message = 'Pour éviter les confusions, le fichier interacadémique n\'est plus accessible. ';
                            }
                        }
                        if ($num_type_fichier > 6) {
                            $message = '';
                        }

                    }

                    if ($session->get('concours') == 'interacadémique') {
                        $fichier = new Fichiersequipes();
                        $message = '';
                    }
                    $fichier->setTypefichier($num_type_fichier);
                    $fichier->setEdition($edition);
                    if (isset($equipe)) {
                        $fichier->setEquipe($equipe);
                    }
                    $fichier->setNational(0);


                    if ($phase == 'national') {
                        $fichier->setNational(1);
                    }


                    $fichier->setFichierFile($file);
                }
                try {
                    $em->persist($fichier);
                    $em->flush();
                    $nom_fichier = $fichier->getFichier();
                } catch (FileException $e) {
                    $message = 'Une erreur est survenue, le fichier n\'a pas été déposé, veuillez prévenir l\'administrateur du site';
                    $request->getSession()
                        ->getFlashBag()
                        ->add('alert', $message);
                    return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-' . $session->get('concours') . '-liste_prof'));


                }
            }


            $request->getSession()
                ->getFlashBag()
                ->add('info', $message . 'Votre fichier renommé selon : ' . $nom_fichier . ' a bien été déposé. Merci !');

            //$user = $this->getUser();//Afin de rappeler le nom du professeur qui a envoyé le fichier dans le mail
            //$type_fichier = $this->getParameter('type_fichier')[$num_type_fichier];

            $type_fichier = $this->getParameter('type_fichier_lit')[$num_type_fichier];

            if (isset($equipe)) {
                if ($phase != 'national') {
                    $info_equipe = 'L\'equipe ' . $equipe->getInfoequipe();
                }

                if ($phase == 'national') {
                    $info_equipe = 'L\'equipe ' . $equipe->getInfoequipenat();
                }
            } else {

                $info_equipe = 'prof ' . $citoyen->getNomPrenom();
            }
            if (($num_type_fichier != 7) or ($num_type_fichier != 4) or ($num_type_fichier != 8)) {//on enregistre pas dans les éditions passées les questionnaires et fiches sécurités
                $this->RempliOdpfFichiersPasses($fichier);
            }
            try {
                if ($equipe->getRetiree() != true) {

                    $this->MailConfirmation($mailer, $type_fichier, $info_equipe);
                } else {
                    if ($type_fichier == 'mémoire') {

                        $this->MailAvertissement($mailer, $type_fichier, $equipe);
                    } else {
                        $this->MailConfirmation($mailer, $type_fichier, $info_equipe);

                    }

                }
            } catch (Exception $e) {

            }


            return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-' . $session->get('concours') . '-liste_prof'));
        }


        if ($choix == '6') {
            $content = $this
                ->renderView('adminfichiers\charge_fichier_fichier.html.twig', array('form' => $form1->createView(), 'donnees_equipe' => $donnees_equipe, 'citoyen' => $citoyen, 'choix' => $choix, 'infos' => $infos));
        } else {
            $content = $this
                ->renderView('adminfichiers\charge_fichier_fichier.html.twig', array('form' => $form1->createView(), 'donnees_equipe' => $donnees_equipe, 'choix' => $choix, 'infos' => $infos));
        }
        return new Response($content);
    }

    public function deposeAutorisations($newFilename, $citoyen, $attrib, $prof, $equipe)
    {
        $em = $this->doctrine->getManager();
        $edition = $this->requestStack->getSession()->get('edition');
        $edition = $em->merge($edition);
        $repositoryFichiersequipes = $this->doctrine->getRepository(Fichiersequipes::class);

        $fileFichier = new UploadedFile($this->getParameter('app.path.tempdirectory') . '/' . $newFilename, $newFilename, null, null, true);

        try {
            if ($prof == true) {
                $fichier = $repositoryFichiersequipes->createQueryBuilder('f')
                    ->andWhere('f.prof =:citoyen')
                    ->setParameter('citoyen', $citoyen)
                    ->getQuery()->getOneOrNullResult();
                if ($fichier != null) {
                    $citoyen->setAutorisationphotos(null);
                    $em->persist($citoyen);
                    $em->flush();
                    $em->remove($fichier);
                    $em->flush();
                }

                $fichier = new Fichiersequipes();
                $fichier->setProf($citoyen);
                $fichier->setFichierFile($fileFichier);
                $fichier->setEdition($edition);
                $fichier->setTypefichier(6);
                $fichier->setNomautorisation($citoyen->getNom() . '-' . $citoyen->getPrenom());
                $fichier->setNational(0);
                $em->persist($fichier);
                $em->flush();
                $citoyen->setAutorisationphotos($fichier);
                $em->persist($citoyen);
                $em->flush();

            }
            if ($prof == false) {

                if ($attrib == 0) {
                    $fichier = new Fichiersequipes();

                    $fichier->setEleve($citoyen);
                    $fichier->setEquipe($citoyen->getEquipe());
                    $fichier->setFichierFile($fileFichier);
                    $fichier->setEdition($edition);
                    $fichier->setTypefichier(6);
                    $fichier->setNomautorisation($citoyen->getNom() . '-' . $citoyen->getPrenom());
                    $em->persist($fichier);
                    $em->flush();
                    $citoyen->setAutorisationphotos($fichier);
                    $em->persist($citoyen);
                    $em->flush();
                }
                if ($attrib != 0) {
                    $fichier = $repositoryFichiersequipes->createQueryBuilder('f')
                        ->where('f.eleve =:eleve')
                        ->andWhere('f.edition =:edition')
                        ->setParameters(['eleve' => $citoyen, 'edition' => $edition])
                        ->getQuery()->getOneOrNullResult();
                    $fichier->setEquipe($citoyen->getEquipe());
                    $fichier->setFichierFile($fileFichier);
                    $fichier->setNomautorisation($citoyen->getNom() . '-' . $citoyen->getPrenom());
                    $em->persist($fichier);
                    $em->flush();
                    $citoyen->setAutorisationphotos($fichier);
                    $em->persist($citoyen);
                    $em->flush();
                }
            }

        } catch (Exception $e) {
            $fichier = null;
        }

        return $fichier;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function RempliOdpfFichiersPasses($fichier)
    {
        $em = $this->doctrine->getManager();
        $edition = $fichier->getEdition();
        $repositoryOdpfFichierspasses = $this->doctrine->getRepository(OdpfFichierspasses::class);
        $repositoryOdpfEquipesPassees = $this->doctrine->getRepository(OdpfEquipesPassees::class);
        $repositoryOdpfEditionsPassees = $this->doctrine->getRepository(OdpfEditionsPassees::class);
        $editionPassee = $repositoryOdpfEditionsPassees->findOneBy(['edition' => $edition->getEd()]);

        if ($fichier->getTypefichier() != 6) {
            $equipe = $fichier->getEquipe();
            $OdpfEquipepassee = $repositoryOdpfEquipesPassees->createQueryBuilder('e')
                ->where('e.numero =:numero')
                ->andWhere('e.editionspassees= :edition')
                ->setParameters(['numero' => $equipe->getNumero(), 'edition' => $editionPassee])
                ->getQuery()->getOneOrNullResult();
            $odpfFichier = $repositoryOdpfFichierspasses->findOneBy(['equipepassee' => $OdpfEquipepassee, 'typefichier' => $fichier->getTypefichier()]);
            if ($odpfFichier === null) {
                $odpfFichier = new OdpfFichierspasses();
                $odpfFichier->setTypefichier($fichier->getTypefichier());
            }
            $odpfFichier->setEquipePassee($OdpfEquipepassee);
        }

        if ($fichier->getTypefichier() == 6) {
            $odpfFichier = $repositoryOdpfFichierspasses->findOneBy(['nomautorisation' => $fichier->getNomautorisation(), 'typefichier' => $fichier->getTypefichier()]);
            if ($odpfFichier === null) {
                $odpfFichier = new OdpfFichierspasses();
                $odpfFichier->setTypefichier(6);
            }
            if (!str_contains($fichier->getFichier(), 'prof')) {

                $odpfEquipepassee = $repositoryOdpfEquipesPassees->findOneBy(['numero' => $fichier->getEquipe()->getNumero(), 'editionspassees' => $editionPassee]);
                $odpfFichier->setEquipepassee($odpfEquipepassee);
            }

            $odpfFichier->setNational(false);
            $odpfFichier->setNomautorisation($fichier->getNomautorisation());
        }

        $odpfFichier->setEditionspassees($editionPassee);
        $odpfFichier->setNomFichier($fichier->getFichier());

        $odpfFichier->setUpdatedAt(new DateTime('now'));
        $em->persist($odpfFichier);
        $em->flush();

    }

    /**
     * @throws TransportExceptionInterface
     */
    public function MailConfirmation(MailerInterface $mailer, string $type_fichier, string $info_equipe)
    {

        $email = (new Email())
            ->from('info@olymphys.fr')
            ->to('webmestre2@olymphys.fr')
            ->addCc('webmestre3@olymphys.fr');

        if ($type_fichier == 'autorisation') {
            $email->addCc('gilles.pauliat@institutoptique.fr');
        }
        $email->subject('Depot du ' . $type_fichier . ' de ' . $info_equipe)
            ->text($info_equipe . ' a déposé un fichier : ' . $type_fichier . '.');

        $mailer->send($email);

    }

    /**
     * @throws TransportExceptionInterface
     */
    public function MailAvertissement(MailerInterface $mailer, string $type_fichier, $equipe)
    {
        $texte = 'Bonjour,<br> Vous venez de déposer le mémoire de l\'équipe ' . $equipe->getLettre() . '- ' . $equipe->getTitreProjet() .
            ' alors que cette équipe  s\'est retirée du concours.<BR>
                     le comité national reviendra vers vous au sujet de ce mémoire d\'une équipe dont le retrait avait été annoncé<BR>
                     <BR>Le comité national des Olympiades de Physique France';
        $email = (new Email())
            ->from('info@olymphys.fr')
            ->to($this->getUser()->getEmail())
            ->addCc('webmestre2@olymphys.fr', 'webmestre3@olymphys.fr')
            ->cc('pierre.chavel@institutoptique.fr', 'fperrot2010@hotmail.fr');

        $email->subject('Depot du mémoire de l\'équipe' . $equipe->getLettre())
            ->html($texte);

        $mailer->send($email);

    }


    /**
     * @Security("is_granted('ROLE_PROF')")
     *
     * @Route("/fichiers/mon_espace", name="mon_espace")
     *
     */
    public function mon_espace(Request $request)
    {

        $session = $this->requestStack->getSession();
        $user = $this->getUser();
        $id_user = $user->getId();
        $edition = $session->get('edition');
        $repositoryFichiersequipes = $this->doctrine
            ->getRepository(Fichiersequipes::class);
        $repositoryEquipesadmin = $this->doctrine
            ->getRepository(Equipesadmin::class);
        $qb3 = $repositoryEquipesadmin->createQueryBuilder('t')
            ->where('t.idProf1=:professeur')
            ->orwhere('t.idProf2=:professeur')
            ->andWhere('t.edition =:edition')
            ->setParameter('edition', $edition)
            ->setParameter('professeur', $id_user);
        if ($this->requestStack->getSession()->get('concours') == 'interacadémique') {
            $qb3->orderBy('t.numero', 'ASC');
        }
        if ($this->requestStack->getSession()->get('concours') == 'national') {
            $qb3->orderBy('t.lettre', 'ASC');
        }

        $liste_equipes = $qb3->getQuery()->getResult();
        // dd($liste_equipes);
        foreach ($liste_equipes as $equipe) {

            $id_equipe = $equipe->getId();
            $qb1 = $repositoryFichiersequipes->createQueryBuilder('t')
                ->LeftJoin('t.equipe', 'e')
                ->Where('e.id=:id_equipe')
                ->andWhere('e.edition =:edition')
                ->setParameter('edition', $edition)
                ->setParameter('id_equipe', $id_equipe)
                ->addOrderBy('t.typefichier', 'ASC');
            $liste_fichiers[$id_equipe] = $qb1->getQuery()->getResult();
        }
        return $this->render('/adminfichiers/espace_prof.html.twig', array('liste_equipes' => $liste_equipes, 'liste_fichiers' => $liste_fichiers));


    }


    /**
     * @Security("is_granted('ROLE_PROF')")
     *
     * @Route("/fichiers/afficher_liste_fichiers_prof/,{infos}", name="fichiers_afficher_liste_fichiers_prof")
     *
     */
    public function afficher_liste_fichiers_prof(Request $request, $infos): Response
    {
        $session = $this->requestStack->getSession();
        $session->set('oldlisteEleves', null);
        $session->set('supr_eleve', null);

        $repositoryFichiersequipes = $this->doctrine
            ->getRepository(Fichiersequipes::class);
        $repositoryVideosequipes = $this->doctrine
            ->getRepository(Videosequipes::class);
        $repositoryEquipesadmin = $this->doctrine
            ->getRepository(Equipesadmin::class);
        $repositoryUser = $this->doctrine
            ->getRepository(User::class);
        $repositoryElevesinter = $this->doctrine
            ->getRepository(Elevesinter::class);

        $Infos = explode('-', $infos);

        $id_equipe = $Infos[0];
        if ($id_equipe == 'prof') {
            $id_equipe = $Infos[4];
        }
        $concours = $Infos[1];
        $choix = $Infos[2];

        $editionId = $this->requestStack->getSession()->get('edition')->getId();
        $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['id' => $editionId]);
        if(date('now')<$this->requestStack->getSession()->get('dateouverturesite')){
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$edition->getEd()-1]);
        }
        $equipe_choisie = $repositoryEquipesadmin->find(['id' => $id_equipe]);
        $centre = $equipe_choisie->getCentre();


        $qbInit = $repositoryFichiersequipes->createQueryBuilder('t')//Les fichiers sans les autorisations photos
        ->LeftJoin('t.equipe', 'e')
            ->Where('e.id=:id_equipe')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->setParameter('id_equipe', $id_equipe);

        if ($concours == 'interacadémique') {
            $qbComit = $qbInit
                ->andWhere('t.national =:national')
                ->andWhere('t.typefichier in (0,1,2,4,5,7)')
                ->setParameter('national', FALSE);


        }
        if ($concours == 'national') {
            $qbComit = $qbInit
                ->andWhere('t.national =:national')
                ->andWhere('t.typefichier in (0,1,2,3,7,8)')
                ->setParameter('national', TRUE)
                ->orWhere('t.typefichier = 4 and  e.id=:id_equipe');

        }


        $qbJuryNat = $repositoryFichiersequipes->createQueryBuilder('t')  // /pour le jury cn resumé mémoire annexes diaporama fiche sécurité
        ->Where('t.equipe =:equipe')
            ->setParameter('equipe', $equipe_choisie)
            ->andWhere('t.typefichier in (0,1,2,3)')
            ->andWhere('t.national =:national')
            ->setParameter('national', TRUE);

        $listeEleves = $repositoryElevesinter->findByEquipe(['equipe' => $equipe_choisie]);
        $liste_prof[1] = $repositoryUser->find(['id' => $equipe_choisie->getIdProf1()]);
        if (null != $equipe_choisie->getIdProf2()) {
            $liste_prof[2] = $repositoryUser->find(['id' => $equipe_choisie->getIdProf2()]);
        }


        $roles = $this->getUser()->getRoles();
        //$role = $roles[0];
        if ((in_array('ROLE_COMITE', $roles)) or (in_array('ROLE_PROF', $roles)) or (in_array('ROLE_ORGACIA', $roles)) or (in_array('ROLE_SUPER_ADMIN', $roles)) or (in_array('ROLE_JURY', $roles))) {

            $liste_fichiers = $qbComit->getQuery()->getResult();


            $autorisations = $repositoryFichiersequipes->createQueryBuilder('t')//Les fichiers sans les autorisations photos
            ->andWhere('t.typefichier =:type')
                ->andWhere('t.edition =:edition')
                ->setParameters(['edition' => $edition, 'type' => 6])
                ->getQuery()->getResult();

        }

        if (in_array('ROLE_JURYCIA', $roles)) {
            $qbInit->andWhere('t.typefichier in (0,1,2,4,5)');

            $liste_fichiers = $qbInit->getQuery()->getResult();


            $autorisations = [];
        }
        if (in_array('ROLE_JURY', $roles)) {
            $liste_fichiers = $qbJuryNat->getQuery()->getResult();

        }
        $qb = $repositoryVideosequipes->createQueryBuilder('v')
            ->LeftJoin('v.equipe', 'e')
            ->Where('e.id=:id_equipe')
            ->setParameter('id_equipe', $id_equipe)
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition);
        $listevideos = $qb->getQuery()->getResult();

        if (!isset($listevideos)) {
            $listevideos = [];
        }
        if (!isset($autorisations)) {
            $autorisations = [];
        }
        if (!isset($liste_fichiers)) {
            $liste_fichiers = [];
        }


        return  $this->render('adminfichiers\espace_prof.html.twig', array('listevideos' => $listevideos, 'liste_autorisations' => $autorisations,
                    'equipe' => $equipe_choisie, 'centre' => $equipe_choisie->getCentre(), 'concours' => $concours, 'edition' => $edition, 'choix' => $choix,
                    'liste_prof' => $liste_prof, 'listeEleves' => $listeEleves, 'liste_fichiers' => $liste_fichiers)
            );



    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @Route("/fichiers/choixedition,{num_type_fichier}", name="fichiers_choixedition")
     *
     */
    public function choixedition(Request $request, $num_type_fichier): Response
    {
        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
        $qb = $repositoryEdition->createQueryBuilder('e')
            ->orderBy('e.ed', 'DESC');


        $Editions = $qb->getQuery()->getResult();
        return $this->render('adminfichiers/choix_edition.html.twig', [
            'editions' => $Editions, 'num_type_fichier' => $num_type_fichier]);
    }



    /**
     * @IsGranted("ROLE_JURY")
     *
     * @Route("/fichiers/voir_fichier_inter,{typefichier}, {idequipe}", name="voir_fichier_inter")
     *
     */
    public function voir_fichier_interacademique(Request $request, $typefichier, $idequipe)
    {//pour le jurynational avant que les équipes n'aient déposé leur fichiers cn

        switch ($typefichier) {
            case 'memoires' :
                $numTypefichier = 0;
                break;
            case 'annexes'  :
                $numTypefichier = 1;
                break;
            case 'resumes'  :
                $numTypefichier = 2;
                break;
        }
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->findOneBy(['id' => $idequipe]);
        $fichier = $this->doctrine->getRepository(Fichiersequipes::class)->createQueryBuilder('f')
            ->andWhere('f.typefichier =:type')
            ->andWhere('f.equipe = :equipe')
            ->andWhere('f.national = 0')
            ->setParameters(['type' => $numTypefichier, 'equipe' => $equipe])
            ->getQuery()->getOneOrNullResult();
        if ($fichier !== null) {
            return $this->redirectToRoute('telecharger_un_fichier_prof', array('idFichier' => $fichier->getId()));
        } else {
            $request->getSession()
                ->getFlashBag()
                ->add('info', 'L\'équipe n\'a pas déposé ce fichier aux interacadémiques');

            return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-national-liste_cn_comite'));

        }
    }


    /**
     * @IsGranted("ROLE_COMITE")
     *
     * @Route("/fichiers/charge_autorisations", name="fichiers_charge_autorisations")
     *
     */
    public function charge_autorisation(Request $request)
    {
        $repositoryFichiersequipes = $this->doctrine
            ->getRepository(Fichiersequipes::class);
        $query = $request->query;

        for ($i = 0; $i < 8; $i++) {

            try {
                if ($query->get('check-eleve-' . $i) == "on") {
                    $autorisationelevesid[$i] = explode('-', $query->get('eleve-' . $i))[1];
                }
                if ($query->get('check-prof-' . $i) == "on") {
                    $autorisationprofsid[$i] = explode('-', $query->get('prof-' . $i))[1];
                }
            } catch (Exception $e) {

            }

        }

        $zipFile = new ZipArchive();
        $FileName = 'Autorisations' . date('now');
        if ($zipFile->open($FileName, ZipArchive::CREATE) === TRUE) {

            if (isset($autorisationprofsid)) {
                foreach ($autorisationprofsid as $id) {
                    $fichierprof = $repositoryFichiersequipes->findOneById(['id' => $id]);
                    $fichierName = $this->getParameter('app.path.fichiers') . '/autorisations/' . $fichierprof->getFichier();
                    $zipFile->addFromString(basename($fichierName), file_get_contents($fichierName));
                }
            }
            if (isset($autorisationelevesid)) {
                foreach ($autorisationelevesid as $id) {
                    $fichiereleve = $repositoryFichiersequipes->findOneById(['id' => $id]);
                    $fichierName = $this->getParameter('app.path.fichiers') . '/autorisations/' . $fichiereleve->getFichier();
                    $zipFile->addFromString(basename($fichierName), file_get_contents($fichierName));
                }
            }
            $zipFile->close();
            $response = new Response(file_get_contents($FileName));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $FileName
            );
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', $disposition);
            @unlink($FileName);
            return $response;
        }
    }

    /**
     * @IsGranted("ROLE_PROF")
     *
     * @Route("/fichiers/telechargerUnFichierProf,{idFichier}", name="telecharger_un_fichier_prof")
     *
     */
    public function telechargerUnFichierProf($idFichier)
    {

        $fichier = $this->doctrine->getRepository(Fichiersequipes::class)->findOneBy(['id' => $idFichier]);
        $edition = $fichier->getEdition();
        $typefichier = $fichier->getTypefichier();

        $typefichier == 1 ? $path = $this->getParameter('type_fichier')[0] : $path = $this->getParameter('type_fichier')[$typefichier];
        $file = 'odpf/odpf-archives/' . $edition->getEd() . '/fichiers/' . $path . '/' . $fichier->getFichier();

        $mimeTypeGuesser = new FileinfoMimeTypeGuesser();

        //$response = new BinaryFileResponse($file);
        $response = new Response(file_get_contents($file));
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fichier->getFichier()
        );

        if (str_contains($_SERVER['HTTP_USER_AGENT'], 'iPad') or str_contains($_SERVER['HTTP_USER_AGENT'], 'Mac OS X')) {
            $response = new BinaryFileResponse($file);
            $response->headers->set('Content-Type', $mimeTypeGuesser->guessMimeType($file));
        }
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Length', filesize($file));

        return $response;


    }

    /**
     * @IsGranted("ROLE_PROF")
     *
     * @Route("/fichiers/telechargerZip,{equipeId},{concours}", name="telecharger_un_fichier_zip")
     *
     */
    public function telechargerZip($equipeId, $concours)
    {
        $equipe_choisie = $this->doctrine->getRepository(Equipesadmin::class)->findOneBy(['id' => $equipeId]);
        $repositoryFichiersequipes = $this->doctrine->getRepository(Fichiersequipes::class);
        $edition = $this->requestStack->getSession()->get('edition');
        $zipFile = new ZipArchive();
        $fileName = $edition->getEd() . '-Fichiers-eq-' . $equipe_choisie->getNumero() . '-' . date('now');
        if ($zipFile->open($fileName, ZipArchive::CREATE) === TRUE) {
            if ($concours == 'interacadémique') {
                $liste_fichiers = $repositoryFichiersequipes->createQueryBuilder('f')
                    ->where('f.equipe =:equipe')
                    ->andWhere('f.typefichier !=:value')
                    ->setParameters(['equipe' => $equipe_choisie, 'value' => 6])
                    ->getQuery()->getResult();
            }
            if ($concours == 'national') {

                $liste_fichiers = $repositoryFichiersequipes->createQueryBuilder('f')
                    ->where('f.equipe =:equipe')
                    ->andWhere('f.typefichier !=:value1 and f.typefichier !=:value2')
                    ->setParameters(['equipe' => $equipe_choisie, 'value1' => 6, 'value2' => 7])
                    ->getQuery()->getResult();


            }

            foreach ($liste_fichiers as $fichier) {
                if ($fichier) {
                    if ($fichier->getTypefichier() == 1) {

                        $fichierName = $this->getParameter('app.path.odpf_archives') . '/' . $equipe_choisie->getEdition()->getEd() . '/fichiers/' . $this->getParameter('type_fichier')[0] . '/' . $fichier->getFichier();
                    } else {
                        $fichierName = $this->getParameter('app.path.odpf_archives') . '/' . $equipe_choisie->getEdition()->getEd() . '/fichiers/' . $this->getParameter('type_fichier')[$fichier->getTypefichier()] . '/' . $fichier->getFichier();
                    }

                    $zipFile->addFromString(basename($fichierName), file_get_contents($fichierName));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
                }
            }
            $zipFile->close();
            $response = new Response(file_get_contents($fileName));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $fileName
            );
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', $disposition);
            @unlink($fileName);
            return $response;

        }

    }
}