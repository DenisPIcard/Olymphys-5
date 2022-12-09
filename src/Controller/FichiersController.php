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
use App\Entity\Rne;
use App\Entity\User;
use App\Entity\Videosequipes;
use App\Form\ListefichiersType;
use App\Form\ToutfichiersType;
use App\Service\valid_fichiers;
use datetime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Vich\UploaderBundle\Entity\File;
use ZipArchive;
use function Symfony\Component\String\u;

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
        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
        $repositoryCentres = $this->doctrine
            ->getRepository(Centrescia::class);
        $repositoryEleves = $this->doctrine
            ->getRepository(Elevesinter::class);
        $repositoryDocequipes = $this->doctrine
            ->getRepository(Docequipes::class);
        $edition = $session->get('edition');
        $docequipes = $repositoryDocequipes->findAll();
        $centres = $repositoryCentres->findAll();
        $datelimcia = $edition->getDatelimcia();
        $datelimnat = $edition->getDatelimnat();
        $datecia = $edition->getConcourscia();
        $datecn = $edition->getConcourscn();
        $dateouverturesite = $edition->getDateouverturesite();
        $dateconnect = new datetime('now');

        $user = $this->getUser();


        $id_user = $user->getId();
        $roles = $user->getRoles();
        //dd($roles);
        //$role = $roles[0];

        if (in_array('ROLE_JURY', $roles)) {
            $nom = $user->getUsername();

            $repositoryJures = $this->doctrine->getRepository(Jures::class);
            $jure = $repositoryJures->findOneBy(['iduser' => $this->getUser()->getId()]);
            $id_jure = $jure->getId();
        }
        $qb1 = $repositoryEquipesadmin->createQueryBuilder('t')
            ->andWhere('t.selectionnee=:selectionnee')
            ->setParameter('selectionnee', TRUE)
            ->andWhere('t.lettre >:valeur')
            ->andWhere('t.edition =:edition')
            ->setParameter('edition', $edition)
            ->setParameter('valeur', '')
            ->orderBy('t.lettre', 'ASC');


        $qb3 = $repositoryEquipesadmin->createQueryBuilder('t')
            ->where('t.rneId =:rne')
            ->andWhere('t.edition =:edition')
            ->setParameter('edition', $edition)
            ->setParameter('rne', $user->getRneId());

        if ($dateconnect > $datecia) {
            $phase = 'national';
            $qb3->orderBy('t.lettre', 'ASC');
        }
        if (($dateconnect <= $datecia)) {
            $phase = 'interacadémique';
            $qb3->orderBy('t.numero', 'ASC');
        }


        if (($choix == 'liste_cn_comite')) {
            if ((in_array('ROLE_COMITE', $roles)) or (in_array('ROLE_JURY', $roles)) or (in_array('ROLE_SUPER_ADMIN', $roles))) {

                $liste_equipes = $qb1->getQuery()->getResult();
                if ($liste_equipes != null) {
                    if (($this->isGranted('ROLE_COMITE')) or ($this->isGranted('ROLE_SUPER_ADMIN'))) {

                        $content = $this
                            ->renderView('adminfichiers\choix_equipe.html.twig', array(
                                    'liste_equipes' => $liste_equipes, 'user' => $user, 'phase' => 'national', 'choix' => $choix
                                )
                            );
                    }
                    if ($this->isGranted('ROLE_JURY')) {
                        $content = $this
                            ->renderView('adminfichiers\choix_equipe.html.twig', array(
                                'liste_equipes' => $liste_equipes, 'user' => $user, 'phase' => 'national', 'choix' => $choix, 'jure' => $jure)//Jure necessaire pour le titre
                            );
                    }
                    return new Response($content);
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Pas encore d\'équipe sélectionnée pour le concours national de la ' . $edition->getEd() . 'e edition');
                    return $this->redirectToRoute('core_home');
                }
            }
        }

        foreach ($centres as $Centre) {
            if ($Centre->getCentre() == $choix) {
                $centre = $Centre;
            }
        }


        if (isset($centre) or ($choix == 'centre')) { //pour le jurycia, comité, superadmin liste des équipes d'un centre
            if (($this->isGranted('ROLE_COMITE')) or ($this->isGranted('ROLE_JURY')) or ($this->isGranted('ROLE_SUPER_ADMIN')) or ($this->isGranted('ROLE_ORGACIA')) or ($this->isGranted('ROLE_JURYCIA'))) {
                if (!isset($centre)) {
                    $centre = $this->getUser()->getCentrecia();
                }

                $qb2 = $repositoryEquipesadmin->createQueryBuilder('t')
                    ->where('t.centre =:centre')
                    ->setParameter('centre', $centre)
                    ->andWhere('t.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->orderBy('t.numero', 'ASC');
                $liste_equipes = $qb2->getQuery()->getResult();

                if ($liste_equipes != null) {

                    $content = $this
                        ->renderView('adminfichiers\choix_equipe.html.twig', array(
                                'liste_equipes' => $liste_equipes, 'user' => $user, 'phase' => 'interacadémique', 'choix' => 'liste_prof', 'centre' => $centre->getCentre()
                            )
                        );
                    return new Response($content);

                }
                if ($liste_equipes == null) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Pas encore d\'équipe pour le concours interacadémique de la ' . $edition->getEd() . 'e edition');
                    return $this->redirectToRoute('core_home');
                }
            }
        }


        if (($choix == 'liste_prof')) {

            if (($phase == 'interacadémique') or ($this->isGranted('ROLE_ORGACIA'))) {


                if ($this->isGranted('ROLE_ORGACIA')) {
                    $centre = $this->getUser()->getCentrecia();

                    $liste_equipes = $repositoryEquipesadmin->createQueryBuilder('t')
                        ->where('t.centre =:centre')
                        ->setParameter('centre', $centre)
                        ->andWhere('t.edition =:edition')
                        ->setParameter('edition', $edition)
                        ->orderBy('t.numero', 'ASC')->getQuery()->getResult();
                    $rne_objet = null;
                }


                if (($this->isGranted('ROLE_ORGACIA') == false) and ($this->isGranted('ROLE_PROF') == false)) {
                    $liste_equipes = null;
                    if ($dateconnect > $datecia) {
                        /*$qb3->andWhere('t.selectionnee=:selectionnee')
                                ->setParameter('selectionnee', TRUE)
                                ->orderBy('t.lettre', 'ASC');    */
                        $liste_equipes = $qb3->getQuery()->getResult();
                        $rne_objet = null;
                    }
                }
                if (in_array('ROLE_PROF', $user->getRoles()) == true) {
                    $liste_equipes = $qb3->getQuery()->getResult();
                    $rne_objet = $this->doctrine->getRepository(Rne::class)->find(['id' => $user->getRneId()]);
                    $role = 'ROLE_PROF';
                }


            }

            //if($liste_equipes!=null) {
            if ($phase == 'national') {


                if (in_array('ROLE_PROF', $roles)) {
                    $liste_equipes = //$qb3
                        $qb3->andWhere('t.selectionnee = 1')
                            ->getQuery()->getResult();
                    $rne_objet = $this->doctrine->getRepository(Rne::class)->find(['id' => $user->getRneId()]);

                }
            }

            $content = $this
                ->renderView('adminfichiers\choix_equipe.html.twig', array(
                    'liste_equipes' => $liste_equipes, 'phase' => $phase, 'user' => $user, 'choix' => $choix,  'doc_equipes' => $docequipes, 'rneObj' => $rne_objet
                ));
            return new Response($content);

            /*         }
           /*  else{
            $request->getSession()
                 ->getFlashBag()
                    ->add('info', 'Le site n\'est pas encore prêt pour une saisie des mémoires ou vous n\'avez pas d\'équipe inscrite pour le concours '. $phase.' de la '.$edition->getEd().'e edition') ;
            return $this->redirectToRoute('core_home');
                }*/


        }


        if (($choix == 'deposer')) {//pour le dépôt des fichiers autres que les présentations

            if (in_array('ROLE_PROF', $roles)) {

                if ($choix == 'diaporama_jury') {
                    if ($dateconnect <= $datecia) {
                        $phase = 'interacadémique';

                        $liste_equipes = $qb3->getQuery()->getResult();
                    }

                    if (($dateconnect <= $datecn) and ($dateconnect > $datecia)) {
                        $phase = 'national';

                        $qb3->andWhere('t.selectionnee=:selectionnee')
                            ->setParameter('selectionnee', TRUE);
                        $liste_equipes = $qb3->getQuery()->getResult();

                    }
                } else {

                    if (($dateconnect > $datelimcia) and ($dateconnect <= $datelimnat)) {
                        $phase = 'national';
                        $qb3->andWhere('t.selectionnee=:selectionnee')
                            ->setParameter('selectionnee', TRUE);
                        $liste_equipes = $qb3->getQuery()->getResult();
                    }
                    if (($dateconnect > $dateouverturesite) and ($dateconnect <= $datelimcia)) {
                        $phase = 'interacadémique';

                        $liste_equipes = $qb3->getQuery()->getResult();
                    }
                }

                if ($liste_equipes != null) {

                    $content = $this
                        ->renderView('adminfichiers\choix_equipe.html.twig', array(
                                'liste_equipes' => $liste_equipes, 'phase' => $phase, 'user' => $user, 'choix' => $choix, 'role' => $role
                            )
                        );
                    return new Response($content);
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Le site n\'est pas encore prêt pour une saisie des mémoires ou vous n\'avez pas d\'équipe inscrite pour le concours ' . $phase . ' de la ' . $edition->getEd() . 'e edition');
                    return $this->redirectToRoute('core_home');
                }
            }

            if (in_array('ROLE_COMITE', $roles)) {


                if (($dateconnect > $datelimcia)) {
                    $phase = 'national';
                    $qb4 = $repositoryEquipesadmin->createQueryBuilder('t')
                        ->where('t.selectionnee=:selectionnee')
                        ->setParameter('selectionnee', TRUE)
                        ->andWhere('t.edition =:edition')
                        ->setParameter('edition', $edition)
                        ->andWhere('t.lettre>:valeur')
                        ->setParameter('valeur', '')
                        ->orderBy('t.lettre', 'ASC');
                    $liste_equipes = $qb4->getQuery()->getResult();


                }
                if (($dateconnect > $dateouverturesite) and ($dateconnect <= $session->get('concourscn'))) {
                    $phase = 'interacadémique';
                    $qb4 = $repositoryEquipesadmin->createQueryBuilder('t')
                        ->where('t.nomLycee>:vide')
                        ->setParameter('vide', '')
                        ->andWhere('t.edition =:edition')
                        ->setParameter('edition', $edition)
                        ->orderBy('t.numero', 'ASC');
                    $liste_equipes = $qb4->getQuery()->getResult();
                }
                if ($liste_equipes) {

                    $content = $this
                        ->renderView('adminfichiers\choix_equipe.html.twig', array(
                                'liste_equipes' => $liste_equipes, 'phase' => $phase, 'user' => $user, 'choix' => $choix, 'role' => $role
                            )
                        );
                    return new Response($content);
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Le site n\'est pas encore prêt pour une saisie des mémoires ou vous n\'avez pas d\'équipes inscrite pour le concours ' . $phase . ' de la ' . $edition->getEd() . 'e edition');
                    return $this->redirectToRoute('core_home');
                }


            }
            if ((in_array('ROLE_ORGACIA', $roles)) or (in_array('ROLE_JURYCIA', $roles))) {

                $centre = $user->getCentrecia()->getCentre();
                $qb5 = $repositoryEquipesadmin->createQueryBuilder('t')
                    ->where('t.nomLycee>:vide')
                    ->setParameter('vide', '')
                    ->andWhere('t.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->orderBy('t.numero', 'ASC')
                    ->andWhere('t.centre =:centre')
                    ->setParameter('centre', $user->getCentrecia());
                $liste_equipes = $qb5->getQuery()->getResult();
                // if ($dateconnect>$datecia){
                //     return $this->redirectToRoute('core_home');

                // }


                if ($liste_equipes) {

                    $content = $this
                        ->renderView('adminfichiers\choix_equipe.html.twig', array(
                                'liste_equipes' => $liste_equipes, 'phase' => $phase, 'user' => $user, 'choix' => $choix, 'role' => $role, 'centre' => $centre
                            )
                        );
                    return new Response($content);
                } else {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Le site n\'est pas encore prêt pour une saisie des mémoires ou vous n\'avez pas d\'équipes inscrite pour le concours ' . $phase . ' de la ' . $edition->getEd() . 'e edition');
                    return $this->redirectToRoute('core_home');
                }
            }
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
        $roles= $this->getUser()->getRoles();
        if(in_array('ROLE_PROF',$roles)) {
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


        };
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

                $fichier = $this->deposeAutorisations($newFilename, $citoyen, $attrib, $prof,$equipe);
                if ($fichier===null){
                    $message = 'Une erreur est survenue, le fichier n\'a pas été déposé, veuillez prévenir l\'administrateur du site';
                    $this->requestStack->getCurrentRequest()->getSession()
                        ->getFlashBag()
                        ->add('alert', $message);
                    return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-' . $this->requestStack->getSession()->get('concours') . '-liste_prof'));

                }
                $message='';
                $nom_fichier = $fichier->getFichier();
            } else {
                if ($attrib == 0) {

                    $fichier = new Fichiersequipes();

                }
                if ($attrib > 0) {
                    $fichier = $repositoryFichiersequipes->findOneBy(['id' => $idfichier]);
                    $message = '';

                }
                $fichier->setFichierFile($file);

                if ($attrib == 0) {

                    if ($session->get('concours') == 'national') { //on vérifie que le fichier cia existe et on écrase sans demande de confirmation ce fichier  par le fichier national  sauf les autorisations photos
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
            };
            if (($num_type_fichier != 7) and ($num_type_fichier != 4)) {
                $this->RempliOdpfFichiersPasses($fichier);
            }
            try {
                $this->MailConfirmation($mailer, $type_fichier, $info_equipe);
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

    public function deposeAutorisations($newFilename, $citoyen, $attrib, $prof,$equipe)
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
                $fichier->setEdition($edition);;
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
                    $fichier->setEdition($edition);;
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
               $fichier=null;
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
        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
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
            $qbComit= $qbInit
                ->andWhere('t.national =:national')
                ->andWhere('t.typefichier in (0,1,2,3,7)')
                ->setParameter('national', TRUE)
                ->orWhere('t.typefichier = 4 and  e.id=:id_equipe');

        }




        $qb4 = $repositoryFichiersequipes->createQueryBuilder('t')  // /pour le jury cn resumé mémoire annexes diaporama
        ->Where('t.equipe =:equipe')
            ->setParameter('equipe', $equipe_choisie)
            ->andWhere('t.typefichier in (0,1,2,3)');
        //->andWhere('t.national =:national')
        //->setParameter('national', TRUE) ;

        $listeEleves = $repositoryElevesinter->findByEquipe(['equipe' => $equipe_choisie]);
        $liste_prof[1] = $repositoryUser->find(['id' => $equipe_choisie->getIdProf1()]);
        if (null != $equipe_choisie->getIdProf2()) {
            $liste_prof[2] = $repositoryUser->find(['id' => $equipe_choisie->getIdProf2()]);
        }


        $roles = $this->getUser()->getRoles();
        //$role = $roles[0];
        if ((in_array('ROLE_COMITE', $roles)) or (in_array('ROLE_PROF', $roles)) or (in_array('ROLE_ORGACIA', $roles)) or (in_array('ROLE_SUPER_ADMIN', $roles))) {

            $liste_fichiers = $qbComit->getQuery()->getResult();


            $autorisations = $repositoryFichiersequipes->createQueryBuilder('t')//Les fichiers sans les autorisations photos
            ->andWhere('t.typefichier =:type')
                ->andWhere('t.edition =:edition')
                ->setParameters(['edition' => $edition, 'type' => 6])
                ->getQuery()->getResult();

        }

        if (in_array('ROLE_JURYCIA', $roles)) {
            $qbInit->andWhere('t.typefichier in (0,1,2,5)');

            $liste_fichiers = $qbInit->getQuery()->getResult();


            $autorisations = [];
        }
        if (in_array('ROLE_JURY', $roles)) {
            $liste_fichiers = $qb4->getQuery()->getResult();

        }

        $infoequipe = $equipe_choisie->getInfoequipe();
        if ($equipe_choisie->getSelectionnee() == true) {
            $infoequipe = $equipe_choisie->getInfoequipenat();//pour les comités et jury,inutile pour les prof , ;
        }
        if ($centre) {
            $centre = $equipe_choisie->getCentre()->getCentre();
        }


        $qb = $repositoryVideosequipes->createQueryBuilder('v')
            ->LeftJoin('v.equipe', 'e')
            ->Where('e.id=:id_equipe')
            ->setParameter('id_equipe', $id_equipe)
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition);
        $listevideos = $qb->getQuery()->getResult();

        if ($request->isMethod('POST')) {
            if ($request->request->has('listefichiers')) {
                $zipFile = new ZipArchive();
                $FileName = $edition->getEd() . '-Fichiers-eq-' . $equipe_choisie->getNumero() . '-' . date('now');
                if ($zipFile->open($FileName, ZipArchive::CREATE) === TRUE) {
                    $liste_fichiers = $repositoryFichiersequipes->createQueryBuilder('f')
                        ->where('f.equipe =:equipe')
                        ->andWhere('f.typefichier !=:value')
                        ->setParameters(['equipe' => $equipe_choisie, 'value' => 6])
                        ->getQuery()->getResult();

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
        }

        if ($liste_fichiers) {
            $fichier = new Fichiersequipes();

            $form = $this->createForm(ListefichiersType::class, $fichier);
            $form->add('save', SubmitType::class);
            $Form = $form->createView();

        }
        if (!isset($Form)) {

            $Form = $this->createForm(ListefichiersType::class)->createView();

        }
        if (!isset($listevideos)) {
            $listevideos = [];
        }
        if (!isset($autorisations)) {
            $autorisations = [];
        }
        if (!isset($liste_fichiers)) {
            $liste_fichiers = [];
        }


        $content = $this
            ->renderView('adminfichiers\espace_prof.html.twig', array('form' => $Form, 'listevideos' => $listevideos, 'liste_autorisations' => $autorisations,
                    'equipe' => $equipe_choisie, 'centre' => $equipe_choisie->getCentre(), 'concours' => $concours, 'edition' => $edition, 'choix' => $choix,
                    'liste_prof' => $liste_prof, 'listeEleves' => $listeEleves, 'liste_fichiers' => $liste_fichiers)
            );
        return new Response($content);


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
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @Route("/fichiers/voirfichiers,{editionId_concours}", name="fichiers_voirfichiers")
     *
     */
    public function voirfichiers(Request $request, $editionId_concours)
    {
        $session = $this->requestStack->getSession();
        $editionconcours = explode('-', $editionId_concours);

        $IdEdition = $editionconcours[0];
        $concours = $editionconcours[1];
        $num_type_fichier = $editionconcours[2];
        $repositoryEdition = $this->doctrine
            ->getRepository(Edition::class);
        $repositoryFichiersequipes = $this->doctrine
            ->getRepository(Fichiersequipes::class);
        $repositoryEquipesadmin = $this->doctrine
            ->getRepository(Equipesadmin::class);

        $edition = $repositoryEdition->find(['id' => $IdEdition]);
        $edition_en_cours = $session->get('edition');
        $date = new datetime('now');


        if ($concours == 'cia') {
            if ($edition_en_cours == $edition) {

                if ($edition_en_cours->getConcourscia() > $date) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Les fichiers de l\'édition ' . $edition_en_cours->getEd() . ' ne sont pas encore publiés, patience ...');
                    return $this->redirectToRoute('fichiers_choixedition', array('num_type_fichier' => $num_type_fichier));


                }
            }


            $qb1 = $repositoryFichiersequipes->createQueryBuilder('m')
                ->leftJoin('m.equipe', 'e')
                ->where('e.selectionnee=:selectionnee')
                ->orderBy('e.lyceeAcademie', 'ASC')
                ->setParameter('selectionnee', FALSE)
                ->andWhere('m.edition=:edition')
                ->setParameter('edition', $edition)
                ->andWhere('m.typefichier <:type')
                ->setParameter('type', 3);

            $fichierstab = $qb1->getQuery()->getResult();
            $qb2 = $repositoryEquipesadmin->createQueryBuilder('e')
                ->where('e.selectionnee=:selectionnee')
                ->setParameter('selectionnee', FALSE)
                ->orderBy('e.lyceeAcademie', 'ASC');

            $listeequipe = $qb2->getQuery()->getResult();
        }
        if ($concours == 'cn') {
            if ($edition_en_cours == $edition) {
                if ($edition_en_cours->getConcourscn() > $date) {
                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Les fichiers de l\'édition ' . $edition_en_cours->getEd() . ' ne sont pas encore publiés, patience ...');
                    return $this->redirectToRoute('fichiers_choixedition', array('num_type_fichier' => $num_type_fichier));


                }
            }
            $qb1 = $repositoryFichiersequipes->createQueryBuilder('m')
                ->leftJoin('m.equipe', 'e')
                ->orderBy('e.lettre', 'ASC')
                ->andWhere('m.edition=:edition')
                ->setParameter('edition', $edition);
            if ($num_type_fichier == 0) {
                $qb1->andWhere('m.typefichier <:type')
                    ->setParameter('type', 3);
            }

            if ($num_type_fichier == 3) {
                $qb1->andWhere('m.typefichier =:type')
                    ->setParameter('type', $num_type_fichier)->andWhere('m.edition=:edition')
                    ->setParameter('edition', $edition);
            }
            $fichierstab = $qb1->getQuery()->getResult();


            $qb2 = $repositoryEquipesadmin->createQueryBuilder('e')
                ->where('e.selectionnee=:selectionnee')
                ->setParameter('selectionnee', TRUE)
                ->orderBy('e.lettre', 'ASC');

            $listeequipe = $qb2->getQuery()->getResult();
        }

        if ($listeequipe) {

            $i = 0;
            foreach ($listeequipe as $equipe) {
                if ($fichierstab) {
                    $j = 0;
                    foreach ($fichierstab as $fichier) {


                        if ($fichier->getEquipe() == $equipe) {
                            $fichiersEquipe[$i][$j] = $fichier;
                            $j++;
                        }
                    }
                }
                $i++;
            }

            if (isset($fichiersEquipe)) {
                if ($num_type_fichier < 3) {
                    $content = $this
                        ->renderView('adminfichiers\affiche_memoires.html.twig',
                            array('fichiersequipes' => $fichiersEquipe,
                                'edition' => $edition,
                                'concours' => $concours
                            ));
                }
                if ($num_type_fichier == 3) {

                    $content = $this
                        ->renderView('adminfichiers\affiche_presentations.html.twig',
                            array('fichiersequipes' => $fichiersEquipe,
                                'edition' => $edition,
                                'concours' => $concours
                            ));
                }
                return new Response($content);
            } else {
                $request->getSession()
                    ->getFlashBag()
                    ->add('info', 'Pas de fichier déposé à ce jour pour cette édition  ');
                return $this->redirectToRoute('fichiers_choixedition', array('num_type_fichier' => $num_type_fichier));
            }
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

        if (str_contains($_SERVER['HTTP_USER_AGENT'],'iPad') or str_contains($_SERVER['HTTP_USER_AGENT'],'Mac OS X'))
        {   $response = new BinaryFileResponse($file);
            $response->headers->set('Content-Type', $mimeTypeGuesser->guessMimeType($file));
        }
        $response->headers->set('Content-Disposition',$disposition);
        $response->headers->set('Content-Length', filesize($file));

        return $response;


    }
}