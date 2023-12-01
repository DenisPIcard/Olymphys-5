<?php

namespace App\Controller\Cia;


use App\Entity\Centrescia;
use App\Entity\Cia\ConseilsjuryCia;
use App\Entity\Cia\HorairesSallesCia;
use App\Entity\Cia\RangsCia;
use App\Entity\Coefficients;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Cia\JuresCia;
use App\Entity\Cia\NotesCia;
use App\Entity\Fichiersequipes;
use App\Entity\Uai;
use App\Form\JuresCiaType;
use App\Form\NotesCiaType;
use App\Service\Mailer;
use DateTime;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use DoctrineExtensions\Query\Mysql\Time;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

;

use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\String\u;


class SecretariatjuryCiaController extends AbstractController
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;
    private Mailer $mailer;
    private UserPasswordHasher $userPasswordHasher;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine, Mailer $mailer, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[IsGranted('ROLE_COMITE')]
    #[Route("/Cia/SecretariatjuryCia/accueil_comite", name: "secretariatjuryCia_accueil_comite")]
    public function accueil_comite(): Response
    {
        $listeCentres = $this->doctrine->getRepository(Centrescia::class)->findBy(['actif' => true], ['centre' => 'ASC']);
        $content = $this->renderView('cyberjuryCia/accueil_comite.html.twig',
            array('centres' => $listeCentres));//pour le comité, c'est la liste des centres qui est la page d'accueil du cyberjury

        return new Response($content);


    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/Cia/SecretariatjuryCia/accueil,{centre}", name: "secretariatjuryCia_accueil")]
    public function accueil($centre): Response//Pour les organisateurs, c'est la liste des équipes du centre qui est la page d'accueil du cyberjury
    {
        $em = $this->doctrine->getManager();
        $edition = $this->requestStack->getSession()->get('edition');
        if ($edition == null) {
            $this->requestStack->getSession()->set('info', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        if (new DateTime('now') < $this->requestStack->getSession()->get('edition')->getDateouverturesite()) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => $edition->getEd() - 1]);
        }
        $repositoryEquipesadmin = $this->doctrine->getRepository(Equipesadmin::class);
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryUai = $this->doctrine->getRepository(Uai::class);
        $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);
        $listEquipes = $repositoryEquipesadmin->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.edition =:edition')
            ->andWhere('e.numero <:numero')
            ->andWhere('e.centre =:centre')
            ->setParameters(['edition' => $edition, 'numero' => 100, 'centre' => $centre])//les numéros supérieurs à 100 sont réservés aux "équipes" kurynational, ambiance du concours, remise des prix pour les photos
            ->orderBy('e.numero', 'ASC')
            ->getQuery()
            ->getResult();
        $lesEleves = [];
        $lycee = [];

        foreach ($listEquipes as $equipe) {
            $numero = $equipe->getLettre();
            $lesEleves[$numero] = $repositoryEleves->findBy(['equipe' => $equipe]);
            $uai = $equipe->getUai();
            $lycee[$numero] = $repositoryUai->findBy(['uai' => $uai]);
        }

        $tableau = [$listEquipes, $lesEleves, $lycee];
        $session = $this->requestStack->getSession();//on crèe une variable globale de session qui contient le tableau
        $session->set('tableau', $tableau);
        $content = $this->renderView('cyberjuryCia/accueil.html.twig',
            array('centre' => $centre, 'equipes' => $listEquipes));

        return new Response($content);
    }


    #[IsGranted('ROLE_ORGACIA')] //La vue globale des résultats est visible du comité et des organisateurs
    #[Route("/secretariatjuryCia/vueglobale,{centre}", name: "secretariatjuryCia_vueglobale")]
    public function vueglobale($centre): Response  //Donne le total des points obtenus par chaque équipe pour chacun des jurés
    {

        $em = $this->doctrine->getManager();
        $repositoryNotes = $this->doctrine->getRepository(NotesCia::class);
        $repositoryCentres = $this->doctrine->getRepository(Centrescia::class);
        $centrecia = $repositoryCentres->findOneBy(['centre' => $centre]);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $listJures = $repositoryJures->createQueryBuilder('j')
            ->leftJoin('j.iduser', 'u')
            ->where('u.centrecia =:centre')
            ->setParameter('centre', $centrecia)
            ->getQuery()->getResult();

        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $listEquipes = $repositoryEquipes->findBy(['edition' => $this->requestStack->getSession()->get('edition'), 'centre' => $centrecia, 'inscrite' => true]);

        $nbre_equipes = 0;
        $progression = [];
        $nbre_jures = 0;
        foreach ($listEquipes as $equipe) {
            $nbre_equipes = $nbre_equipes + 1;
            $id_equipe = $equipe->getId();
            $nbre_jures = 0;
            foreach ($listJures as $jure) {
                $id_jure = $jure->getId();
                $nbre_jures += 1;
                $statut = $repositoryJures->getAttribution($jure, $equipe);//vérifie l'attribution du juré !: 0 si assiste, 1 si rapporteur sinon Null
                //récupère l'évaluation de l'équipe par le juré dans $note pour l'afficher(mémoire compris) :
                if (is_null($statut)) { //Si le juré n'a pas à évaluer l'équipe le statut est null
                    $progression[$nbre_equipes][$nbre_jures] = 'ras';//pour éviter une valeur nulle dans le tableau

                } elseif ($statut == 1) {// le juré évalue le mémoire(il est rapporteur)
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);//Vérifie si le juré à déjà noté l'équipe en lisant la note
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getTotalPoints();//Si la note est nulle, l'équipe n'est pas encore notée, on place une * , sinon on place le total avec mémoire
                } else { // Le juré n'évalue pas le mémoire, il est simple examinateur
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);//Vérifie si le juré à déjà noté l'équipe en lisant la note
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getPoints();//Si la note est nulle, l'équipe n'est pas encore notée, on place une * , sinon on place le total sans mémoire
                }
            }
        }

        $content = $this->renderView('cyberjuryCia/vueglobale.html.twig', array(
            'listJures' => $listJures,
            'listEquipes' => $listEquipes,
            'progression' => $progression,
            'nbre_equipes' => $nbre_equipes,
            'nbre_jures' => $nbre_jures,
            'centre' => $centre
        ));

        return new Response($content);
    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/secretariatjuryCia/classement,{centre}", name: "secretariatjuryCia_classement")]
    public function classement($centre): Response
    {

        // affiche les équipes dans l'ordre de la note brute
        $edition = $this->requestStack->getSession()->get('edition');
        if ($edition == null) {
            $this->requestStack->getSession()->set('info', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        $repositoryCentres = $this->doctrine->getRepository(Centrescia::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $repositoryRangs = $this->doctrine->getRepository(RangsCia::class);
        $centrecia = $repositoryCentres->findOneBy(['centre' => $centre]);
        $listEquipes = $repositoryEquipes->findBy(['edition' => $edition, 'centre' => $centre]);

        $rangs = $repositoryRangs->createQueryBuilder('r')
            ->leftJoin('r.equipe', 'eq')
            ->where('eq.edition =:edition')
            ->andWhere('eq.centre =:centre')
            ->setParameters(['edition' => $edition, 'centre' => $centrecia])
            ->addOrderBy('r.rang', 'ASC')
            ->getQuery()->getResult();
        $content = $this->renderView('cyberjuryCia/classement.html.twig',
            array('rangs' => $rangs, 'equipes' => $listEquipes, 'centre' => $centrecia)
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/secretariatjuryCia/classementSousJury,{centre}", name: "secretariatjuryCia_classementSousJury")]
    public function classementSousJury($centre): Response
    {

        // affiche les équipes dans l'ordre de la note brute
        $edition = $this->requestStack->getSession()->get('edition');
        if ($edition == null) {
            $this->requestStack->getSession()->set('info', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        $repositoryCentres = $this->doctrine->getRepository(Centrescia::class);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $repositoryRangs = $this->doctrine->getRepository(RangsCia::class);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $numJury = $repositoryJures->findOneBy(['iduser' => $this->getUser()])->getNumJury();
        $centrecia = $repositoryCentres->findOneBy(['centre' => $centre]);
        $equipesSousJury = $repositoryJures->getEquipesSousJury($centrecia, $numJury);

        $rangs = $repositoryRangs->classementSousJury($equipesSousJury);

        $content = $this->renderView('cyberjuryCia/classement_sous_jury.html.twig',
            array('rangs' => $rangs, 'equipes' => $equipesSousJury, 'centre' => $centre)
        );
        return new Response($content);
    }

    #[Route("/secretariatjuryCia/classementEquipesJure,{idUserJure}", name: "secretariatjuryCia_classementEquipesJure")]
    public function classementEquipesJure($idUserJure): Response
    {

        // affiche les équipes dans l'ordre de la note brute
        $edition = $this->requestStack->getSession()->get('edition');
        if ($edition == null) {
            $this->requestStack->getSession()->set('info', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        $repositoryCentres = $this->doctrine->getRepository(Centrescia::class);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $repositoryRangs = $this->doctrine->getRepository(RangsCia::class);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $jure = $repositoryJures->findOneBy(['iduser' => $idUserJure]);
        $centre = $jure->getCentrecia();
        $equipesjure = $jure->getEquipes();

        $rangs = $repositoryRangs->createQueryBuilder('r')
            ->leftJoin('r.equipe', 'eq')
            ->where('eq.centre =:centre')
            ->andWhere('eq.inscrite =:value')
            ->setParameter('centre', $centre)
            ->setParameter('value', true)
            ->orderBy('r.rang', 'ASC')
            ->getQuery()->getResult();

        $content = $this->renderView('cyberjuryCia/classement_equipes_jure.html.twig',
            array('rangs' => $rangs, 'equipes' => $equipesjure, 'centre' => $centre)
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/creeJure,{centre}", name: "secretariatjuryCia_creeJure")]
    public function creeJure(Request $request, UserPasswordHasherInterface $passwordEncoder, Mailer $mailer, $centre): Response    //Creation du juré par l'organisateur cia
    {
        $centrecia = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);
        $orgacia = $this->getUser();
        $this->requestStack->getSession()->set('info', '');
        $slugger = new AsciiSlugger();
        $repositoryUser = $this->doctrine->getRepository(User::class);
        $jureCia = new JuresCia();
        $form = $this->createFormBuilder($jureCia)
            ->add('email', RepeatedType::class, [
                'first_options' => ['label' => 'Email'],
                'second_options' => ['label' => 'Saisir de nouveau l\'email'],
            ])
            ->add('nomJure', TextType::class)
            ->add('prenomJure', TextType::class)
            ->add('valider', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('valider')->isClicked()) {

                $email = $form->get('email')->getData();
                $nom = $form->get('nomJure')->getData();
                $prenom = $form->get('prenomJure')->getData();
                $user = $repositoryUser->findOneBy(['email' => $email]);

                if ($user === null) {// Le mail ne correspond à aucun compte olymphys
                    $user = new User();//On crée le user
                    try {

                        $user->setNom(strtoupper($slugger->slug($nom)));//Elimine les caractères ésotériques
                        $prenomNorm = ucfirst(strtolower($slugger->slug($prenom)));//prépare la normalisation du prénom

                        if (count(explode('-', $prenom)) > 1) {//Si le prénom est composé

                            $prenomNorm = '';
                            $i = 0;
                            $arrayPrenom = explode('-', $prenom);

                            foreach ($arrayPrenom as $sprenom) {//Pour obtenir un prénom composé de la form Prenom-Prenom
                                if ($i == 0) {
                                    $prenomNorm = ucfirst(strtolower($sprenom)) . '-';
                                    $i++;

                                } elseif ($i < count(explode('-', $prenom)) - 1) {

                                    $prenomNorm = $prenomNorm . ucfirst(strtolower($sprenom)) . '-';

                                } elseif ($i == count(explode('-', $prenom)) - 1) {
                                    $prenomNorm = $prenomNorm . ucfirst(strtolower($sprenom));

                                }
                            }

                        }

                        $user->setPrenom($prenomNorm);
                        $user->setEmail($email);
                        $user->setRoles(['ROLE_JURYCIA']);
                        $user->setCentrecia($centrecia);
                        $username = $prenomNorm[0] . '_' . $slugger->slug($nom);//Création d'un username avec caratères ASCII
                        $pwd = $prenomNorm;
                        $i = 1;
                        while ($repositoryUser->findBy(['username' => $username])) {//pour éviter des logins identiques on ajoute un numéro à la fin
                            $username = $username . $i;
                            $i = +1;
                        }
                        $user->setUsername($username);
                        $user->setPassword($passwordEncoder->hashPassword($user, $pwd));
                        $this->doctrine->getManager()->persist($user);
                        $this->doctrine->getManager()->flush();
                        $mailer->sendInscriptionUserJure($orgacia, $user, $pwd, $centre);//On envoie au nouvel user ses identifiants avec copie au comité
                    } catch (\Exception $e) {
                        $texte = 'Une erreur est survenue lors de l\'inscription de ce jure :' . $e;
                        $this->requestStack->getSession()->set('info', $texte);//Un emodale surgira si une erreur est survenue lors de la création du juré
                    }

                } else {

                    $user->setRoles(['ROLE_JURYCIA']);//Si le compte Olymphys existe déjà, on s'assure que son rôle sera jurycia
                    $user->setCentrecia($centrecia);//On affecte le compte  du juré au centre cia créateur
                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();
                }
                $jure = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['iduser' => $user]);
                if ($jure === null) {//le jurécia n'existe pas encore
                    $jureCia = new JuresCia(); //On crée ce juré cia
                    $jureCia->setIduser($user); //On associe le jurécia à compte olymphys
                    $jureCia->setNomJure($user->getNom());
                    $jureCia->setPrenomJure($user->getPrenom());
                    $jureCia->setCentrecia($centrecia);//On affecte le juré cia au centre créateur du juré cia
                    if (str_contains($slugger->slug($prenom), '-')) {//Pour éliminer les caratères non ASCII et tenir compte d'un prénom composé
                        $initiales = strtoupper(explode('-', $slugger->slug($prenom))[0][0] . explode('-', $slugger->slug($prenom))[1][0] . $slugger->slug($nom[0]));
                    } elseif (str_contains($slugger->slug($prenom), '_')) {//Pour éliminer les caratères non ASCII  et tenir compte d'un prénom composé mal saisi
                        $initiales = strtoupper(explode('-', $slugger->slug($prenom))[0][0] . explode('-', $slugger->slug($prenom))[1][0] . $slugger->slug($nom[0]));
                    } else {
                        $initiales = strtoupper($slugger->slug($prenom))[0] . strtoupper($slugger->slug($nom))[0];
                    }
                    $jureCia->setEmail($email);
                    $jureCia->setInitialesJure($initiales);
                    $this->doctrine->getManager()->persist($jureCia);
                    $this->doctrine->getManager()->flush();
                    $mailer->sendInscriptionJureCia($orgacia, $jureCia, $prenom, $centre);//envoie d'un mail au juré pour l'informer que son compte jurécia du centre où il est affecté est ouvert avec copie au comité
                } else {
                    $texte = 'Ce juré existe déjà !';
                    $this->requestStack->getSession()->set('info', $texte);

                }

                return $this->redirectToRoute('secretariatjuryCia_gestionjures', ['centre' => $centre]);
            }
            return $this->render('cyberjuryCia/accueil.html.twig', ['centre' => $centre]);
        }

        return $this->render('cyberjuryCia/creejure.html.twig', ['form' => $form->createView(), 'centre' => $centre]);


    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/gestionjures,{centre}", name: "secretariatjuryCia_gestionjures")]
    public function gestionjures(Request $request, $centre)//Cette fonction est appelée à chaque changement d'un champ du formulaire via une fontion JQUERY et ajax dans app.js
    {   //Ainsi l'organisateur peut saisir le tableau à la "volée"

        $centrecia = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);//$centrecia est un string nom du centre
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->andWhere('e.inscrite =:value')
            ->setParameters(['value' => true, 'centre' => $centrecia, 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $horaires = $this->doctrine->getRepository(HorairesSallesCia::class)->createQueryBuilder('h')
            ->leftJoin('h.equipe', 'eq')
            ->where('eq.centre =:centre')
            ->setParameter('centre', $centrecia)
            ->getQuery()->getResult();
        //$request contient les infos à traiter
        if ($request->get('idjure') !== null) {//pour la modif des données perso du juré
            $idJure = $request->get('idjure');
            $val = $request->get('value');
            $type = $request->get('type');
            $jure = $this->doctrine->getRepository(JuresCia::class)->find($idJure);
            $userJure = $this->doctrine->getRepository(User::class)->find($jure->getIduser()->getId());
            switch ($type) {
                case 'prenom':
                    $jure->setPrenomJure($val);
                    $userJure->setPrenom($val);
                    $this->doctrine->getManager()->persist($userJure);//pour être en cohérence avec la table user
                    break;
                case 'nom' :
                    $jure->setNomJure($val);
                    $userJure->setNom($val);
                    $this->doctrine->getManager()->persist($userJure);//pour être en cohérence avec la table user
                    break;
                case 'initiales':
                    $jure->setInitialesJure($val);
                    break;
                case 'numJury':
                    $jure->setNumJury(intval($val));
                    break;
            }
            $this->doctrine->getManager()->persist($jure);
            $this->doctrine->getManager()->flush();
            //$this->redirectToRoute('secretariatjuryCia_gestionjures');
        }

        if ($request->get('idequipe') !== null) {//pour la modification des attribtions des équipes
            $idJure = $request->get('idjure');
            $attrib = $request->get('value');
            $idequipe = $request->get('idequipe');
            $jure = $this->doctrine->getRepository(JuresCia::class)->find($idJure);
            $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idequipe);

            if ($attrib == 'R') {
                $jure->addEquipe($equipe);
                $rapporteur = $jure->getRapporteur();
                if ($rapporteur == null) {
                    $rapporteur[0] = $equipe->getNumero();
                    $jure->setRapporteur($rapporteur);
                }
                if (!in_array($equipe->getNumero(), $rapporteur)) {//le juré n'était pas rapporteur, il le devient
                    $rapporteur[count($rapporteur)] = $equipe->getNumero();
                    $jure->setRapporteur($rapporteur);
                }
            }
            if ($attrib == 'L') {
                $jure->addEquipe($equipe);
                $lecteur = $jure->getLecteur();
                if ($lecteur == null) {
                    $lecteur[0] = $equipe->getNumero();
                    $jure->setLecteur($lecteur);
                }
                if (!in_array($equipe->getNumero(), $lecteur)) {//le juré n'était pas lecteur , il le devient
                    $lecteur[count($lecteur)] = $equipe->getNumero();
                    $jure->setLecteur($lecteur);
                }
            }
            if ($attrib == 'E') {

                $jure->addEquipe($equipe);//la fonction add contient le test d'existence de l'équipe et ne l'ajoute que si elle n'est pas dans la liste des équipes du juré
                $rapporteur = $jure->getRapporteur();
                $lecteur = $jure->getLecteur();
                if ($rapporteur !== null) {
                    if (in_array($equipe->getNumero(), $rapporteur)) {//On change l'attribution de l'équipe au juré : il n'est plus rapporteur
                        unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);//supprime le numero de l'équipe dans la liste du champ rapporteur
                    }
                    $jure->setRapporteur($rapporteur);
                }
                if ($lecteur !== null) {
                    if (in_array($equipe->getNumero(), $lecteur)) {//On change l'attribution de l'équipe au juré : il n'est plus lecteurr
                        unset($lecteur[array_search($equipe->getNumero(), $lecteur)]);//supprime le numero de l'équipe dans la liste du champ lecteur
                    }
                    $jure->setLecteur($lecteur);
                }
            }
            if ($attrib == '') {//Le champ est vide pas d'affectation du juré à cette équipe
                $rapporteur = $jure->getRapporteur();//on teste si le juré était rapporteur
                if ($rapporteur !== null) {
                    if (in_array($equipe->getNumero(), $rapporteur)) {//On change l'attribution de l'équipe au juré : il n'est plus rapporteur
                        unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);//supprime le numero de l'équipe dans la liste du champ rapporteur
                    }
                    $jure->setRapporteur($rapporteur);
                }
                if ($jure->getEquipes()->contains($equipe)) {//Si le juré était affecté à cette équipe, on le retire de cette équipe
                    $jure->removeEquipe($equipe);
                }
            }
            $this->doctrine->getManager()->persist($jure);
            $this->doctrine->getManager()->flush();
            $listejures = $this->doctrine->getRepository(JuresCia::class)->createQueryBuilder('j')
                ->where('j.centrecia =:centre')
                ->setParameter('centre', $centrecia)
                ->orderBy('j.numJury', 'ASC')
                ->addOrderBy('j.nomJure', 'ASC')
                ->getQuery()->getResult();
            return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $centrecia->getCentre(), 'horaires' => $horaires));


        }

        if ($request->query->get('jureID') !== null) {//la fenêtre modale de confirmation de suppresion du juré a été validée, elle renvoie l'id du juré

            $idJure = $request->query->get('jureID');
            $jure = $this->doctrine->getRepository(JuresCia::class)->find($idJure);
            $notes = $jure->getNotesj();
            if ($notes !== null) {
                foreach ($notes as $note) {
                    $jure->removeNote($note);
                    $this->doctrine->getManager()->remove($note);

                }
                $repo = $this->doctrine->getRepository(RangsCia::class);
                $points = $repo->classement($jure->getCentreCia());
            }

            $this->doctrine->getManager()->remove($jure);
            $this->doctrine->getManager()->flush();
            $idJure = null;//Dans le cas où le formulaire est envoyé dès le clic sur un des input

        }


        $listejures = $this->doctrine->getRepository(JuresCia::class)->createQueryBuilder('j')
            ->where('j.centrecia =:centre')
            ->setParameter('centre', $centrecia)
            ->orderBy('j.numJury', 'ASC')
            ->addOrderBy('j.nomJure', 'ASC')
            ->getQuery()->getResult();
        return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $centrecia->getCentre(), 'horaires' => $horaires));

    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/attrib_horaires_salles_cia,{centre}", name: "attrib_horaires_salles_cia")]
    public function attrib_horaires_salles_cia(Request $request, $centre)
    {
        $centrecia = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);//$centrecia est un string nom du centre
        $idEquipe = $request->query->get('idequipe');//on récupére les datas envoyées par ajax dans le query via la méthode get
        $type = $request->query->get('type');
        $valeur = $request->query->get('value');
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idEquipe);
        $horaire = $this->doctrine->getRepository(HorairesSallesCia::class)->findOneBy(['equipe' => $equipe]);
        if ($horaire === null) {
            $horaire = new HorairesSallesCia();
            $horaire->setEquipe($equipe);
        }

        if ($type != null) {
            if ($type == 'heure') {
                if ($valeur != null) {//La methode onfocusout utilisée se déclenche dès que la zone de saisie perd le focus, même si rie, n'a été saisi
                    $heure = new DateTime($valeur);
                    $horaire->setHoraire($heure);
                }
            }
            if ($type == 'salle') {
                $horaire->setSalle($valeur);
            }
        }
        $this->doctrine->getManager()->persist($horaire);
        $this->doctrine->getManager()->flush();
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $centrecia, 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $horaires = $this->doctrine->getRepository(HorairesSallesCia::class)->createQueryBuilder('h')
            ->leftJoin('h.equipe', 'eq')
            ->where('eq.centre =:centre')
            ->setParameter('centre', $centrecia)
            ->getQuery()->getResult();
        $listejures = $this->doctrine->getRepository(JuresCia::class)->createQueryBuilder('j')
            ->where('j.centrecia =:centre')
            ->setParameter('centre', $centrecia)
            ->orderBy('j.numJury', 'ASC')
            ->addOrderBy('j.nomJure', 'ASC')
            ->getQuery()->getResult();

        return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $centrecia->getCentre(), 'horaires' => $horaires));

    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/effacer_heure_cia,{idequipe}", name: "effacer_heure_cia")]
    public function effacer_heure_cia(Request $request, $idequipe)
    {
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idequipe);
        $horaire = $this->doctrine->getRepository(HorairesSallesCia::class)->findONeBy(['equipe' => $equipe]);
        if ($horaire !== null) {
            $horaire->setHoraire(null);
            $this->doctrine->getManager()->persist($horaire);
            $this->doctrine->getManager()->flush();
        }

        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $equipe->getCentre(), 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $horaires = $this->doctrine->getRepository(HorairesSallesCia::class)->createQueryBuilder('h')
            ->leftJoin('h.equipe', 'eq')
            ->where('eq.centre =:centre')
            ->setParameter('centre', $equipe->getCentre())
            ->getQuery()->getResult();
        $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $equipe->getCentre()], ['numJury' => 'ASC']);

        return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $equipe->getCentre()->getCentre(), 'horaires' => $horaires));


    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/tableauexcel_repartition, {centre}", name: "secretariatjuryCia_tableauexcel_repartition")]
    public function tableauexcel_repartition($centre): void
    {
        $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);
        $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $centre], ['numJury' => 'ASC']);
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $centre, 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $horairesSalles = $this->doctrine->getRepository(HorairesSallesCia::class)->createQueryBuilder('h')
            ->leftJoin('h.equipe', 'eq')
            ->where('eq.centre =:centre')
            ->andWhere('eq.edition =:edition')
            ->setParameters(['centre' => $centre, 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $styleAlignment = [

            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],

        ];
        $styleArray = [

            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'inside' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],

        ];
        $styleArrayTop = [

            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'inside' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFACD',
                ],

            ],

        ];
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CIA-" . $this->getUser()->getCentrecia() . "-Tableau destiné aux organisateurs")
            ->setSubject("Tableau destiné aux organisateurs")
            ->setDescription("Office 2007 XLSX répartition des jurés")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");
        $spreadsheet->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setHorizontalCentered(true);
        $spreadsheet->getActiveSheet()->getPageMargins()->setTop(0.4);
        $spreadsheet->getActiveSheet()->getPageMargins()->setRight(0.4);
        $spreadsheet->getActiveSheet()->getPageMargins()->setLeft(0.4);
        $spreadsheet->getActiveSheet()->getPageMargins()->setBottom(0.4);;
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath('./odpf/odpf-images/site-logo-285x75.png');
        $drawing->setResizeProportional(false);
        $drawing->setHeight(37);
        $drawing->setWidth(60);
        $drawing->setCoordinates('A1');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getRowDimension(1)->setRowHeight(30, 'pt');
        $sheet->getStyle('A2')->applyFromArray($styleAlignment);
        $sheet->getStyle('A2')->getFont()->setSize(16);
        $sheet->getStyle('A3')->applyFromArray($styleAlignment);
        $sheet->getStyle('A3')->getFont()->setSize(20);

        $lettres = ['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'L', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $sheet->mergeCells('A2:' . $lettres[count($listeEquipes) - 1] . '2', Worksheet::MERGE_CELL_CONTENT_HIDE);
        $sheet->mergeCells('A3:' . $lettres[count($listeEquipes) - 1] . '3', Worksheet::MERGE_CELL_CONTENT_HIDE);
        $sheet->getRowDimension(2)->setRowHeight(20, 'pt');
        $sheet->getRowDimension(3)->setRowHeight(35, 'pt');
        $sheet->mergeCells('E5:' . $lettres[count($listeEquipes) - 1] . '5', Worksheet::MERGE_CELL_CONTENT_HIDE);
        $sheet->getStyle('E5:' . $lettres[count($listeEquipes) - 1] . '5')->applyFromArray($styleArray);
        $sheet
            ->setCellValue('A2', 'Olympiades de Physique France ' . $this->requestStack->getSession()->get('edition')->getEd() . 'e édition');

        $sheet
            ->setCellValue('A3', 'Répartition des jurés pour le centre de ' . $centre);
        $sheet
            ->setCellValue('E5', 'N° des équipes');

        $ligne = 8;

        $sheet->getRowDimension($ligne)->setRowHeight(25, 'pt');

        $sheet
            ->setCellValue('A' . $ligne, 'Prénom juré')
            ->setCellValue('B' . $ligne, 'Nom juré')
            ->setCellValue('C' . $ligne, 'Initiales')
            ->setCellValue('D' . $ligne, 'sous-jury')
            ->setCellValue('D' . $ligne - 1, 'salle')
            ->setCellValue('D' . $ligne - 2, 'horaire');
        $i = 0;
        $spreadsheet->getActiveSheet()->getStyle('A' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->applyFromArray($styleArrayTop);
        foreach ($listeEquipes as $equipe) {

            $sheet->setCellValue($lettres[$i] . $ligne, $equipe->getNumero());
            foreach ($horairesSalles as $horairesSalle) {
                $sheet->getStyle($lettres[$i] . $ligne - 2)->applyFromArray($styleArray);
                $sheet->getStyle($lettres[$i] . $ligne - 1)->applyFromArray($styleArray);
                if ($horairesSalle->getEquipe() == $equipe) {
                    if ($horairesSalle->getHoraire() != null) {
                        $sheet->setCellValue($lettres[$i] . $ligne - 2, $horairesSalle->getHoraire()->format('H:i'));
                    }
                    $sheet->setCellValue($lettres[$i] . $ligne - 1, $horairesSalle->getSalle());
                }


            }
            $i = $i + 1;
        }
        $i = 0;

        $ligne += 1;
        $sheet->getRowDimension($ligne)->setRowHeight(25, 'pt');
        foreach ($listejures as $jure) {
            $sheet
                ->setCellValue('A' . $ligne, $jure->getPrenomJure())
                ->setCellValue('B' . $ligne, $jure->getNomJure())
                ->setCellValue('C' . $ligne, $jure->getInitialesJure())
                ->setCellValue('D' . $ligne, $jure->getNumJury());
            $spreadsheet->getActiveSheet()->getStyle('A' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->applyFromArray($styleArray);
            $sheet->getRowDimension($ligne)->setRowHeight(25, 'pt');
            $equipesjure = $jure->getEquipes();
            foreach ($listeEquipes as $equipe) {
                $sheet->setCellValue($lettres[$i] . $ligne, '*');
                foreach ($equipesjure as $equipejure) {

                    if ($equipejure == $equipe) {
                        $sheet->setCellValue($lettres[$i] . $ligne, 'E');
                        if (in_array($equipe->getNumero(), $jure->getRapporteur())) {
                            $sheet->setCellValue($lettres[$i] . $ligne, 'R');
                        }
                        if (in_array($equipe->getNumero(), $jure->getLecteur())) {
                            $sheet->setCellValue($lettres[$i] . $ligne, 'L');
                        }
                    }
                }
                $i += 1;
            }
            //


            switch ($jure->getNumJury()) {
                case  1 :
                    $sheet->getStyle('D' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F0F8FF');
                    break;
                case 2 :
                    $sheet->getStyle('D' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFE0');
                    break;
                case  3 :
                    $sheet->getStyle('D' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFE4E1');
                    break;

            }
            foreach (range('A', $lettres[$i - 1]) as $letra) {
                $sheet->getColumnDimension($letra)->setAutoSize(true);
            }
            $i = 0;
            $ligne += 1;

        }
        //$sheet->getStyle('A2:' . $lettres[$i - 1] . '2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('00AA66');


        $writer = new Xls($spreadsheet);
        //$writer->save('temp/repartition_des_jures.xls');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="repartition_des_jures_de_' . $centre . '.xls"');
        header('Cache-Control: max-age=0');
        //$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        // $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');


    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/secretariatjuryCia/envoi_mail_conseils", name: "secretariatjuryCia_envoi_mail_conseils")]
    public function envoi_mail_conseils(Request $request, Mailer $mailer)//Envoie le conseil aux prof1 et prof2
    {

        $idEquipe = $request->query->get('idequipe');
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idEquipe);
        $prof1 = $equipe->getIdProf1();
        $prof2 = $equipe->getIdProf2();
        $conseil = $this->doctrine->getRepository(ConseilsjuryCia::class)->findOneBy(['equipe' => $equipe]);

        if ($conseil->isEnvoye() != true) {
            $mailer->sendConseil($conseil, $prof1, $prof2);
        }
        $conseil->setEnvoye(true);


        $this->doctrine->getManager()->persist($conseil);
        $this->doctrine->getManager()->flush();

        return $this->redirectToRoute('cyberjuryCia_gerer_conseils_equipe', ['centre' => $equipe->getCentre()->getCentre()]);
    }

    #[IsGranted('ROLE_COMITE')]
    #[Route("/cia/secretariatjuryCia/modifnotejure,{idequipe}, {idjure}", name: "modifnotejure")]
    public function modifnotejure(Request $request, $idequipe, $idjure)//Dans le cas où le jury c'est trompé d'équipe en examinant une équipe qui n'est pas de son jury mais en notant par mégarde  une autre équipe de son jury
    {
        //Il faut ajouter l'équipe au juré(qui sera considéré par défaut examinateur simple E)
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idequipe);
        $jure = $this->doctrine->getRepository(JuresCia::class)->find($idjure);
        $qb = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $jure->getCentrecia(), 'edition' => $this->requestStack->getSession()->get('edition')]);

        $noteequipe = $this->doctrine->getRepository(NotesCia::class)->findOneBy(['jure' => $jure, 'equipe' => $equipe]);

        //Il faut transporter les notes à la bonne équipe
        $form = $this->createFormBuilder()
            ->add('equipe', EntityType::class, [
                'class' => Equipesadmin::class,
                'query_builder' => $qb,
                'label' => 'Equipe qui a été réellement évaluée par le juré',
                'placeholder' => '',
            ])
            ->add('valider', SubmitType::class, ['label' => 'Valider'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            if ($noteequipe !== null) {
                $nllEquipe = $form->get('equipe')->getData();//equipe B dans laquelle la note de l'équipe A doivent être transférées
                $notenllequipe = new NotesCia();//notes de l'équipe B dans laquelle la note de l'équipe A doivent être transférées
                $notenllequipe->setJure($jure);  //transfert de la note de l'équipe A vers l'équipe B
                $notenllequipe->setEquipe($nllEquipe);
                $notenllequipe->setEcrit($noteequipe->getEcrit());
                $notenllequipe->setExper($noteequipe->getExper());
                $notenllequipe->setDemarche($noteequipe->getDemarche());
                $notenllequipe->setOral($noteequipe->getOral());
                $notenllequipe->setOrigin($noteequipe->getOrigin());
                $notenllequipe->setRepquestions($noteequipe->getRepquestions());
                $notenllequipe->setWgroupe($noteequipe->getWgroupe());
                $coefficients = $this->doctrine->getRepository(Coefficients::class)->findOneBy(['id' => 1]);
                $notenllequipe->setCoefficients($coefficients);
                $total = $notenllequipe->getPoints();
                $notenllequipe->setTotal($total);
                $this->doctrine->getManager()->persist($notenllequipe);//enregistrement de la notes de l'équipe B
                $this->doctrine->getManager()->remove($noteequipe);//suppresion de la notes de l'équipe A
                $jure->addEquipe($nllEquipe);//affectation de l'équipe B au juré
                $this->doctrine->getManager()->persist($notenllequipe);//hydratation de la base
                $jure->removeEquipe($equipe);//supression de l'équipe A dans la liste équipes du juré
                $this->doctrine->getManager()->persist($jure);//enregistrement du juré
                $this->doctrine->getManager()->flush();
            } else {

                $this->requestStack->getSession()->set('info', 'Le juré n\'a pas encore noté l\'équipe, veuillez modifier l\'affectation de ce juré dans le tableau de gestion des jurés');
            }
            return $this->redirectToRoute('secretariatjuryCia_vueglobale', ['centre' => $jure->getCentrecia()]);
        }
        return $this->render('cyberjuryCia/modifNoteJureCia.html.twig', ['form' => $form->createView(), 'equipe' => $equipe, 'centre' => $jure->getCentrecia(), 'jure' => $jure]);

    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/cia/secretariatjuryCia/modifcoordonneesjures,{idjure},{centre}", name: "modifcoordonneesjures")] //Pour que l'organisateur CIA puisse modifier l'adresse mail du juré  en cas d'erreur de saisie lors de la création du compte
    public function modifcoordonneesjures(Request $request, $idjure, $centre)
    {

        $jurecia = $this->doctrine->getRepository(JuresCia::class)->find($idjure);
        $userJure = $this->doctrine->getRepository(User::class)->findOneBy(['id' => $jurecia->getIduser()->getId()]);
        $form = $this->createFormBuilder($jurecia)
            ->add('email', EmailType::class)
            ->add('nomJure', TextType::class)
            ->add('prenomJure', TextType::class)
            ->add('valider', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $nom = $form->get('nomJure')->getData();
            $prenom = $form->get('prenomJure')->getData();
            $email = $form->get('email')->getData();
            $userJure->setEmail($email);
            $userJure->setNom(strtoupper($nom));
            $prenomNorm = ucfirst(strtolower($prenom));
            if (count(explode('-', $prenom)) > 1) {

                $prenomNorm = '';
                $i = 0;
                $arrayPrenom = explode('-', $prenom);

                foreach ($arrayPrenom as $sprenom) {
                    if ($i == 0) {
                        $prenomNorm = ucfirst(strtolower($sprenom)) . '-';
                        $i++;

                    } elseif ($i < count(explode('-', $prenom)) - 1) {

                        $prenomNorm = $prenomNorm . ucfirst(strtolower($sprenom)) . '-';

                    } elseif ($i == count(explode('-', $prenom)) - 1) {
                        $prenomNorm = $prenomNorm . ucfirst(strtolower($sprenom));

                    }
                }

            }
            $userJure->setPrenom($prenomNorm);
            $jurecia->setPrenomJure($prenomNorm);
            $jurecia->setNomJure(strtoupper($nom));

            $this->doctrine->getManager()->persist($jurecia);
            $this->doctrine->getManager()->persist($userJure);
            $this->doctrine->getManager()->flush();
            return $this->redirectToRoute('secretariatjuryCia_gestionjures', ['centre' => $centre]);

        }


        return $this->render('cyberjuryCia/modifcoordonneesjurescia.html.twig', ['form' => $form->createView(), 'jure' => $jurecia, 'centre' => $centre]);


    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/cia/JuryCia/infos_equipe_comite/{idequipe}", name: "infos_equipe_comite")]
    public function infos_equipe_cia(Request $request, $idequipe): Response
    {
        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $equipe = $repositoryEquipesadmin->find($idequipe);

        $repositoryEleves = $this->doctrine
            ->getManager()
            ->getRepository(Elevesinter::class);
        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $listEleves = $repositoryEleves->createQueryBuilder('e')
            ->where('e.equipe =:equipe')
            ->setParameter('equipe', $equipe)
            ->getQuery()->getResult();

        try {
            $memoires = $this->doctrine->getManager()
                ->getRepository(Fichiersequipes::class)->createQueryBuilder('m')
                ->where('m.equipe =:equipe')
                ->setParameter('equipe', $equipe)
                ->andWhere('m.typefichier = 0')
                ->getQuery()->getResult();
        } catch (Exception $e) {
            $memoires = null;
        }

        $idprof1 = $equipe->getIdProf1();
        $idprof2 = $equipe->getIdProf2();
        $mailprof1 = $repositoryUser->find(['id' => $idprof1])->getEmail();
        $telprof1 = $repositoryUser->find(['id' => $idprof1])->getPhone();
        if ($idprof2 != null) {
            $mailprof2 = $repositoryUser->find(['id' => $idprof2])->getEmail();
            $telprof2 = $repositoryUser->find(['id' => $idprof2])->getPhone();
        } else {
            $mailprof2 = null;
            $telprof2 = null;
        }


        $content = $this->renderView('cyberjuryCia/infos_equipe.html.twig',
            array(
                'equipe' => $equipe,
                'mailprof1' => $mailprof1,
                'mailprof2' => $mailprof2,
                'telprof1' => $telprof1,
                'telprof2' => $telprof2,
                'listEleves' => $listEleves,
                'id_equipe' => $idequipe,
                'memoires' => $memoires,
                'centre' => $equipe->getCentre()->getCentre()
            )
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/renvoimailjure,{idjure}", name: "renvoimailjure")]
    public function renvoi_mail_jure($idjure)//Renvoi des identifiants et affectation du juré
    {

        $jure = $this->doctrine->getRepository(JuresCia::class)->find($idjure);
        $user = $jure->getIduser();
        $orgacia = $this->getUser();
        $plainpwd = $user->getPrenom();
        $passwordEncoder = $this->userPasswordHasher;
        $user->setPassword($passwordEncoder->hashPassword($user, $plainpwd));
        $this->userPasswordHasher->hashPassword($user, $plainpwd); //réinitialise le mot de passe par défaut
        $this->mailer->sendInscriptionUserJure($orgacia, $user, $plainpwd, $user->getCentrecia()->getCentre());
        $this->mailer->sendInscriptionJureCia($orgacia, $jure, $plainpwd, $user->getCentrecia()->getCentre());
        return $this->redirectToRoute('secretariatjuryCia_gestionjures', ['centre' => $user->getCentrecia()]);

    }

    #[IsGranted('ROLE_COMITE')]
    #[Route("/verouiller_classement,{centre}", name: "verouiller_classement")]
    public function verouiller_classement($centre)//Permet au comité de bloquer la modification du classement une fois la délibération terminée
    {
        $centrecia = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);
        $centrecia->setVerouClassement(true);
        $this->doctrine->getManager()->persist($centrecia);
        $this->doctrine->getManager()->flush();
        return $this->redirectToRoute('secretariatjuryCia_classement', ['centre' => $centre]);


    }

    #[IsGranted('ROLE_COMITE')]
    #[Route("/deverouiller_classement,{centre}", name: "deverouiller_classement")]
    public function deverouiller_classement($centre)//Déverouille la modification du classement des équipes au cia
    {
        $centrecia = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);
        $centrecia->setVerouClassement(false);
        $this->doctrine->getManager()->persist($centrecia);
        $this->doctrine->getManager()->flush();
        return $this->redirectToRoute('secretariatjuryCia_classement', ['centre' => $centre]);


    }

    #[IsGranted('ROLE_SUPERADMIN')]
    #[Route("/remplir_des_equipes_fictives_essais", name: "remplir_des_equipes_fictives_essais")]
    public function remplir_equipes_fictives_essais()
    {//Pour éviter que le nom des équipes réelles soient utilisées

        if ($_SERVER['SERVER_NAME'] == 'olympessais.olymphys.fr') {//uniquement pour le site d'essais

            $listeEquipe = $this->doctrine->getRepository(Equipesadmin::class)->findBy(['edition' => $this->requestStack->getSession()->get('edition')]);
            foreach ($listeEquipe as $equipe) {
                $equipe->setTitreProjet('Ceci est le titre de l\'équipe ' . $equipe->getNumero());
                $equipe->setNomProf1('prof1 equipe ' . $equipe->getNumero());
                $equipe->setNomProf2('prof2 equipe ' . $equipe->getNumero());
                $equipe->
                $this->doctrine->getManager()->persist($equipe);
                $this->doctrine->getManager()->flush();
            }
        }
    }
}