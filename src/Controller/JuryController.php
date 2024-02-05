<?php
// src/Controller/JuryController.php
namespace App\Controller;

use App\Entity\Attributions;
use App\Entity\Cadeaux;
use App\Entity\Coefficients;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Jures;
use App\Entity\Liaison;
use App\Entity\Notes;
use App\Entity\Phrases;
use App\Entity\Prix;
use App\Entity\RecommandationsJuryCn;
use App\Entity\Repartprix;
use App\Entity\User;
use App\Form\NotesType;
use App\Form\PhrasesType;
use App\Form\RecommandationsCnType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JuryController extends AbstractController
{
    private RequestStack $requestStack;
    private EntityManagerInterface $em;
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, RequestStack $requestStack, EntityManagerInterface $em)
    {

        $this->requestStack = $requestStack;
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("cyberjury/accueil", name: "cyberjury_accueil")]
    public function accueil(Request $request): Response

    {
        $session = $this->requestStack->getSession();

        $edition = $session->get('edition');
        $editionN1 = $session->get('editionN1');
        $date = new \DateTime('now');
        if ($date < $edition->getDateOuvertureSite() and $date > $editionN1->getConcoursCn()) {//Dans le cas où l'édition N+1 a été créée il que les jurés puisse accéder aux équipes de l'édition N
            $edition = $editionN1;
        }

        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);
        if ($jure === null) {
            $request->getSession()->set('info', 'Vous n\'êtes pas membre du jury national cette année');
            return $this->redirectToRoute('core_home');
        }


        $id_jure = $jure->getId();

        $attrib = $repositoryJures->getAttribution($jure);

        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);

        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);

        $progression = array();
        $memoires = array();
        $listeEquipes = $repositoryEquipes->createQueryBuilder('e')
            ->addOrderBy('e.ordre', 'ASC')
            ->getQuery()->getResult();
        foreach ($listeEquipes as $equipe) {

            foreach ($attrib as $key => $value) {

                if ($equipe->getEquipeinter()->getLettre() == $key) {

                    $id = $equipe->getId();
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id);
                    $progression[$key] = (!is_null($note)) ? 1 : 0;

                    try {
                        $memoires[$key] = $repositoryMemoires->createQueryBuilder('m')
                            ->where('m.edition =:edition')
                            ->setParameter('edition', $edition)
                            ->andWhere('m.typefichier = 0')
                            ->andWhere('m.equipe =:equipe')
                            ->setParameter('equipe', $equipe->getEquipeinter())
                            ->getQuery()->getSingleResult();
                    } catch (Exception $e) {
                        $memoires[$key] = null;
                    }
                }
            }
        }

        $content = $this->renderView('cyberjury/accueil.html.twig',
            array('listeEquipes' => $listeEquipes, 'progression' => $progression, 'jure' => $jure, 'memoires' => $memoires, 'attributions' => $attrib)
        );


        return new Response($content);


    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/infos_equipe/{id}", name: "cyberjury_infos_equipe", requirements: ["id_equipe" => "\d{1}|\d{2}"])]
    public function infos_equipe(Request $request, $id): Response
    {
        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);
        $equipe = $this->doctrine->getRepository(Equipes::class)->find($id);
        if ($jure === null) {
            $request->getSession()
                ->getFlashBag()->add('alert', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        $id_jure = $jure->getId();
        $note = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class)
            ->EquipeDejaNotee($id_jure, $id);
        $progression = (!is_null($note)) ? 1 : 0;

        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $equipeadmin = $repositoryEquipesadmin->find(['id' => $equipe->getEquipeinter()->getId()]);

        $repositoryEleves = $this->doctrine
            ->getManager()
            ->getRepository(Elevesinter::class);
        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $listEleves = $repositoryEleves->createQueryBuilder('e')
            ->where('e.equipe =:equipe')
            ->setParameter('equipe', $equipeadmin)
            ->getQuery()->getResult();

        try {
            $memoires = $this->doctrine->getManager()
                ->getRepository(Fichiersequipes::class)->createQueryBuilder('m')
                ->where('m.equipe =:equipe')
                ->setParameter('equipe', $equipeadmin)
                ->andWhere('m.typefichier = 0')
                ->getQuery()->getResult();
        } catch (Exception $e) {
            $memoires = null;
        }

        $idprof1 = $equipe->getEquipeinter()->getIdProf1();
        $idprof2 = $equipe->getEquipeinter()->getIdProf2();
        $mailprof1 = $repositoryUser->find(['id' => $idprof1])->getEmail();
        $telprof1 = $repositoryUser->find(['id' => $idprof1])->getPhone();
        if ($idprof2 != null) {
            $mailprof2 = $repositoryUser->find(['id' => $idprof2])->getEmail();
            $telprof2 = $repositoryUser->find(['id' => $idprof2])->getPhone();
        } else {
            $mailprof2 = null;
            $telprof2 = null;
        }


        $content = $this->renderView('cyberjury/infos.html.twig',
            array(
                'equipe' => $equipe,
                'mailprof1' => $mailprof1,
                'mailprof2' => $mailprof2,
                'telprof1' => $telprof1,
                'telprof2' => $telprof2,
                'listEleves' => $listEleves,
                'id_equipe' => $id,
                'progression' => $progression,
                'jure' => $jure,
                'memoires' => $memoires
            )
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/lescadeaux", name: "cyberjury_lescadeaux")]
    public function lescadeaux(Request $request): RedirectResponse|Response
    {
        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);
        if ($jure === null) {
            $request->getSession()
                ->getFlashBag()->add('alert', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }

        $repositoryCadeaux = $this->doctrine
            ->getManager()
            ->getRepository(Cadeaux::class);
        $ListCadeaux = $repositoryCadeaux->findAll();

        $content = $this->renderView('cyberjury/lescadeaux.html.twig',
            array('ListCadeaux' => $ListCadeaux,
                'jure' => $jure)
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/lesprix", name: "cyberjury_lesprix")]
    public function lesprix(Request $request): RedirectResponse|Response
    {
        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);
        if ($jure === null) {
            $request->getSession()
                ->getFlashBag()->add('alert', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        $repositoryPrix = $this->doctrine
            ->getManager()
            ->getRepository(Prix::class);


        $ListPremPrix = $repositoryPrix->findBy(['niveau' => '1er']);
        $ListDeuxPrix = $repositoryPrix->findBy(['niveau' => '2ème']);
        $ListTroisPrix = $repositoryPrix->findBy(['niveau' => '3ème']);

        $content = $this->renderView('cyberjury/lesprix.html.twig',
            array('ListPremPrix' => $ListPremPrix,
                'ListDeuxPrix' => $ListDeuxPrix,
                'ListTroisPrix' => $ListTroisPrix,
                'jure' => $jure)
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("palmares", name: "cyberjury_palmares")]
    public function palmares(Request $request): RedirectResponse|Response
    {
        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);
        if ($jure === null) {
            $request->getSession()
                ->getFlashBag()->add('alert', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $em = $this->doctrine->getManager();

        $repositoryRepartprix = $this->doctrine
            ->getManager()
            ->getRepository(Repartprix::class);

        $NbrePremierPrix = $repositoryRepartprix
            ->findOneBy(['niveau' => '1er'])
            ->getNbreprix();

        $NbreDeuxPrix = $repositoryRepartprix
            ->findOneBy(['niveau' => '2ème'])
            ->getNbreprix();

        $NbreTroisPrix = $repositoryRepartprix
            ->findOneBy(['niveau' => '3ème'])
            ->getNbreprix();

        $ListPremPrix = $repositoryEquipes->palmares(1, 0, $NbrePremierPrix); // classement par rang croissant
        $offset = $NbrePremierPrix;
        $ListDeuxPrix = $repositoryEquipes->palmares(2, $offset, $NbreDeuxPrix);
        $offset = $offset + $NbreDeuxPrix;
        $ListTroisPrix = $repositoryEquipes->palmares(3, $offset, $NbreTroisPrix);

        $content = $this->renderView('cyberjury/palmares.html.twig',
            array('ListPremPrix' => $ListPremPrix,
                'ListDeuxPrix' => $ListDeuxPrix,
                'ListTroisPrix' => $ListTroisPrix,
                'NbrePremierPrix' => $NbrePremierPrix,
                'NbreDeuxPrix' => $NbreDeuxPrix,
                'NbreTroisPrix' => $NbreTroisPrix,
                'jure' => $jure)
        );
        return new Response($content);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/evaluer_une_equipe/{id}", name: "cyberjury_evaluer_une_equipe", requirements: ["id_equipe" => "\d{1}|\d{2}"])]
    public function evaluer_une_equipe(Request $request, $id): RedirectResponse|Response
    {
        //$id est l'iD de l'équipe.
        $user = $this->getUser();
        $repositoryJure = $this->doctrine->getRepository(Jures::class);
        $jure = $repositoryJure->findOneBy(['iduser' => $user]);
        $repositoryEquipes = $this->doctrine
            ->getRepository(Equipes::class);
        $equipe = $repositoryEquipes->find($id);
        $lettre = $equipe->getEquipeinter()->getLettre();


        $attrib = $repositoryJure->getAttribution($jure);

        $em = $this->doctrine->getManager();

        $notes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class)
            ->EquipeDejaNotee($jure, $id);

        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        try {

            $memoire = $repositoryMemoires->createQueryBuilder('m')
                ->where('m.equipe =:equipe')
                ->setParameter('equipe', $equipe->getEquipeinter())
                ->andWhere('m.typefichier = 0')
                ->andWhere('m.national = 1')
                ->getQuery()->getSingleResult();

        } catch (Exception $e) {
            $memoire = null;

        }

        $flag = 0;//Indique au twig si le juré évalue l'écrit 0 : n'évalue pas l'écrit

        if (is_null($notes)) {//l'équipe n'est pas encore notée par ce juré
            $notes = new Notes();
            $notes->setEquipe($equipe);
            $notes->setJure($jure);
            $progression = 0;//Sert dans le twig formulaire de notation pour l'aspect de la lettre de l'équipe(bleue si déjà notée)
            $nllNote = true;//C'est une nouvelle note
            if ($attrib[$lettre] == 1 or $attrib[$lettre] == 2) {//Le juré peut être lecteur ou rapporteur
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => true, 'EST_Lecteur' => true,));
                $flag = 1;//Le juré évalue l'écrit
            } else {
                $notes->setEcrit(0);//Le juré examine sans étudier le mémoire on attribue 0 à la note du mémoire
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => true, 'EST_Lecteur' => false,));
            }
        } else {// L'équipe a déjà une note de ce juré, il la met à jour
            $notes = $this->doctrine
                ->getManager()
                ->getRepository(Notes::class)
                ->EquipeDejaNotee($jure, $id);
            $progression = 1;//Sert dans le twig formulaire de notation pour l'aspect de la lettre de l'équipe(blanche si déjà notée)
            $nllNote = false;//Ce n'est pas un nouvelle note
            if ($attrib[$lettre] == 1 or $attrib[$lettre] == 2) {
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => false, 'EST_Lecteur' => true,));
                $flag = 1;//Le juré évalue l'écrit
            } else {
                $notes->setEcrit('0');;//Le juré examine sans étudier le mémoire on attribue 0 à la note du mémoire
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => false, 'EST_Lecteur' => false,));
            }
        }
        $coefficients = $this->doctrine->getRepository(Coefficients::class)->findOneBy(['id' => 1]);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $coefficients = $this->doctrine->getRepository(Coefficients::class)->findOneBy(['id' => 1]);
            $notes->setCoefficients($coefficients);// renseigne les coefficeints appliquées
            $total = $notes->getPoints();//Le total sans l'écrit est inscrit dans le tableau des notes
            $notes->setTotal($total);
            if ($nllNote) {//Si c'est une nouvelle note on augemente le nombre de notes de l'équipe
                $nbNotes = count($equipe->getNotess());

                $equipe->setNbNotes($nbNotes + 1);
                $em->persist($equipe);
            }

            $em->persist($notes);
            $em->flush();

            //$request->getSession()->getFlashBag()->add('notice', 'Notes bien enregistrées');
            // puis on redirige vers la page de visualisation de cette note dans le tableau de bord
            return $this->redirectToroute('cyberjury_tableau_de_bord', array('critere' => 'TOT', 'sens' => 'DESC'));
        }


        $content = $this->renderView('cyberjury/evaluer.html.twig',
            array(
                'equipe' => $equipe,
                'form' => $form->createView(),
                'flag' => $flag,
                'progression' => $progression,
                'jure' => $jure,
                'coefficients' => $coefficients,
                'memoire' => $memoire
            ));
        return new Response($content);

    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/tableau_de_bord,{critere},{sens}", name: "cyberjury_tableau_de_bord")]
    public function tableau($critere, $sens): Response//$critère indique le champ et $sens l'ordre décroissant ou croissant
    {
        $user = $this->getUser();
        $jure = $this->doctrine->getRepository(Jures::class)->findOneBy(['iduser' => $user]);
        $attributions = $this->doctrine->getRepository(Jures::class)->getAttribution($jure);
        $id_jure = $jure->getId();
        $ordre = array(//Par défaut les critères ont classés par ordre décroissant, l'équipe la mieux notée est en haut
            'EXP' => 'DESC',
            'DEM' => 'DESC',
            'ORI' => 'DESC',
            'REP' => 'DESC',
            'TRE' => 'DESC',
            'ORA' => 'DESC',
            'TOT' => 'DESC');
        $ordre[$critere] = $sens;//On modifie l'odre du critère choisi dans le tableau
        $MonClassement = $this->tri($critere, $sens, $id_jure)->getQuery()->getResult();//On appelle le classement selon le critère et le sens choisi

        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class);
        $repositoryCoef = $this->doctrine
            ->getManager()
            ->getRepository(Coefficients::class);
        $coefs = $repositoryCoef->find(1);//La table coeffcient ne comporte qu'une ligne d'iD = 1.

        $rangs = $repositoryNotes->get_rangs($id_jure, $coefs);//Met à jour le rang des équipes selon les points du juré uniquement et obtention d'un tableau de rangs correspondant à chaque équipe

        $memoires = array();
        $listEquipes = array();
        $j = 1;
        foreach ($MonClassement as $notes) {// création du tableau des notes des équipes du juré
            $equipe = $notes->getEquipe();

            $listEquipes[$j]['id'] = $equipe->getId();
            $listEquipes[$j]['infoequipe'] = $equipe->getEquipeinter();
            $listEquipes[$j]['lettre'] = $equipe->getEquipeinter()->getLettre();
            $listEquipes[$j]['titre'] = $equipe->getEquipeinter()->getTitreProjet();
            $listEquipes[$j]['exper'] = $notes->getExper();
            $listEquipes[$j]['demarche'] = $notes->getDemarche();
            $listEquipes[$j]['oral'] = $notes->getOral();
            $listEquipes[$j]['repquestions'] = $notes->getRepquestions();
            $listEquipes[$j]['origin'] = $notes->getOrigin();
            $listEquipes[$j]['wgroupe'] = $notes->getWgroupe();
            $listEquipes[$j]['ecrit'] = $notes->getEcrit();
            // $listEquipes[$j]['points'] = $notes->getPoints();
            $listEquipes[$j]['total'] = $notes->getTotal();//Points sans l'écrit
            $memoires[$j] = $repositoryMemoires->createQueryBuilder('m')
                ->andWhere('m.equipe =:equipe')
                ->setParameter('equipe', $equipe->getEquipeinter())
                ->andWhere('m.national =:valeur')
                ->setParameter('valeur', 1)
                ->andWhere('m.typefichier =:typefichier')
                ->setParameter('typefichier', 0)
                ->getQuery()->getResult();

            $j++;

        }

        $content = $this->renderView('cyberjury/tableau.html.twig',
            array('listEquipes' => $listEquipes,
                'jure' => $jure,
                'memoires' => $memoires,
                'ordre' => $ordre,
                'critere' => $critere,
                'rangs' => $rangs,
                'attributions' => $attributions)
        );
        return new Response($content);
    }

    public function tri($critere, $sens, $id_jure): QueryBuilder
    {
        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class);

        $queryBuilder = $repositoryNotes->createQueryBuilder('n');
        $queryBuilder
            ->where('n.jure=:id_jure')
            ->setParameter('id_jure', $id_jure);
        switch ($critere) {//On ordonne le tableau des équipes du juré selon le critère et le sens choisi
            case 'EXP':
                $queryBuilder->orderBy('n.exper', $sens);
                break;
            case('ORI') :
                $queryBuilder->orderBy('n.origin', $sens);
                break;
            case('ORA') :
                $queryBuilder->orderBy('n.oral', $sens);
            case('REP') :
                $queryBuilder->orderBy('n.repquestions', $sens);
                break;
            case('DEM') :
                $queryBuilder->orderBy('n.demarche', $sens);
                break;
            case('TRE') :
                $queryBuilder->orderBy('n.wgroupe', $sens);
                break;
            case('TOT') :
                $queryBuilder->orderBy('n.total', $sens);
                break;

        }

        return $queryBuilder;
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/liste_phrases_amusantes/{id}", name: "cyberjury_phrases_amusantes", requirements: ["id_equipe" => "\d{1}|\d{2}"])]
    public function liste_phrases_amusantes(Request $request, $id): Response
    {
        $user = $this->getUser();
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $repositoryPhrases = $this->doctrine
            ->getManager()
            ->getRepository(Phrases::class);
        $repositoryJure = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $jure = $repositoryJure->findOneBy(['iduser' => $user]);
        $id_jure = $jure->getId();
        $notes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class)
            ->EquipeDejaNotee($id_jure, $id);
        $equipe = $repositoryEquipes->findOneBy(['id' => $id]);
        $phrases = $repositoryPhrases->findBy(['equipe' => $equipe]);


        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        try {
            $memoire = $repositoryMemoires->createQueryBuilder('m')
                ->where('m.equipe =:equipe')
                ->setParameter('equipe', $equipe->getEquipeinter())
                ->andWhere('m.typefichier = 0')
                ->andWhere('m.national = TRUE')
                ->getQuery()->getSingleResult();
        } catch (Exception $e) {
            $memoire = null;
        }

        $progression = (!is_null($notes)) ? 1 : 0;
        //dd($equipe,$phrases,$progression,$jure);
        $content = $this->renderView('cyberjury\listephrases.html.twig',
            array(
                'equipe' => $equipe,
                'phrases' => $phrases,
                'memoires' => $memoire,
                'progression' => $progression,
                'jure' => $jure,
            ));

        return new Response($content);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/edit_phrases/{id}", name: "cyberjury_edit_phrases_amusantes")]
    public function edit_phrases(Request $request, $id): RedirectResponse|Response
    {

        $user = $this->getUser();
        $repositoryJure = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $jure = $repositoryJure->findOneBy(['iduser' => $user]);
        $id_jure = $jure->getId();
        $notes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class)
            ->EquipeDejaNotee($id_jure, $id);
        $progression = (!is_null($notes)) ? 1 : 0;
        $repositoryPhrases = $this->doctrine
            ->getManager()
            ->getRepository(Phrases::class);
        $repositoryLiaison = $this->doctrine
            ->getManager()
            ->getRepository(Liaison::class);
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $equipe = $repositoryEquipes->find($id);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        try {
            $memoire = $repositoryMemoires->createQueryBuilder('m')
                ->where('m.equipe =:equipe')
                ->setParameter('equipe', $equipe->getEquipeinter())
                ->andWhere('m.typefichier = 0')
                ->andWhere('m.national = TRUE')
                ->getQuery()->getSingleResult();
        } catch (Exception $e) {
            $memoire = null;
        }
        $phrase = $repositoryPhrases->findOneBy(['jure' => $jure, 'equipe' => $equipe]) == null ? $phrase = new Phrases() : $phrase = $repositoryPhrases->findOneBy(['jure' => $jure, 'equipe' => $equipe]);

        $em = $this->doctrine->getManager();
        $form = $this->createForm(PhrasesType::class, $phrase);
        $phrases = 0;
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $phrase = $form->getdata();
            $phrase->setJure($jure);
            $phrase->setEquipe($equipe);
            $equipe->addPhrase($phrase);
            $em->persist($phrase);
            $em->persist($equipe);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Phrase et prix amusants bien enregistrés');
            return $this->redirectToroute('cyberjury_phrases_amusantes', ['id' => $equipe->getId()]);
        }
        $content = $this->renderView('cyberjury\phrases.html.twig',
            array(
                'equipe' => $equipe,
                'form' => $form->createView(),
                'progression' => $progression,
                'jure' => $jure,
                'phrases' => $phrases,
                'memoires' => $memoire
            ));
        return new Response($content);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/supr_phrase/{idphrase}", name: "cyberjury_suprim_phrase_amusante")]
    public function supr_phrase(Request $request, $idphrase): Response
    {
        $user = $this->getUser();
        $repositoryJure = $this->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $jure = $repositoryJure->findOneBy(['iduser' => $user]);


        $phrase = $this->doctrine->getRepository(Phrases::class)->findOneBy(['id' => $idphrase]);
        $equipe = $phrase->getEquipe();
        $idEquipe = $equipe->getId();
        $equipe->removePhrase($phrase);
        $phrase->setJure(null);
        $phrase->setEquipe(null);
        $this->em->remove($phrase);
        $this->em->flush();
        $phrases = $equipe->getPhrases();
        $notes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class)
            ->EquipeDejaNotee($jure->getId(), $idEquipe);
        $progression = (!is_null($notes)) ? 1 : 0;
        $content = $this->renderView('cyberjury\listephrases.html.twig',
            array(
                'equipe' => $equipe,
                'phrases' => $phrases,
                'progression' => $progression,
                'jure' => $jure,
            ));
        return new Response($content);


    }

    #[IsGranted('ROLE_JURY')]
    #[Route("/Jury/transposeAttributions", name: "transpose_attribution")]
    public function transposeAttributions()
    {
        //fonction outil pour transférer les attributions des équipes vers la nouvelle méthode, a effacer dès après sont utilisation
        /*  $jures = $this->doctrine->getRepository(Jures::class)->findAll();
          $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => 30]);
          foreach ($jures as $jure) {


              foreach (range('A', 'Z') as $i) {
                  // On récupère le nom du getter correspondant à l'attribut.
                  $method = 'get' . ucfirst($i);

                  if (method_exists($jure, $method)) {
                      if ($jure->$method() !== null) {


                          $equipeinter = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
                              ->where('e.lettre =:lettre')
                              ->setParameter('lettre', $i)
                              ->andWhere('e.edition =:edition')
                              ->setParameter('edition', $edition)
                              ->getQuery()->getResult();

                          $equipe = $this->doctrine->getRepository(Equipes::class)->findOneBy(['equipeinter' => $equipeinter]);

                          $jure->addEquipe($equipe);
                          $statut = $jure->$method();

                          if ($statut == 1) {

                              $attributions = $jure->getAttributions();
                              if ($attributions == null) {
                                  $attributions[0] = $equipe->getId();
                                  $jure->setAttributions($attributions);
                              }
                              if (!in_array($equipe->getId(), $attributions)) {//le juré n'était pas rapporteur, il le devient
                                  $attributions[count($attributions)] = $equipe->getId();
                                  $jure->setAttributions($attributions);
                              }


                          }

                      }
                      $this->doctrine->getManager()->persist($jure);
                      $this->doctrine->getManager()->flush();

                  }

              }


          }

  */

        $jures = $this->doctrine->getRepository(Jures::class)->findAll();
        $attributionsRepository = $this->doctrine->getRepository(Attributions::class);
        $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => 30]);
        foreach ($jures as $jure) {
            $attributions = $attributionsRepository->findBy(['jure' => $jure]);

            if ($attributions === []) {
                $attributions[0] = new Attributions();

            }
            foreach (range('A', 'Z') as $i) {
                // On récupère le nom du getter correspondant à l'attribut.
                $method = 'get' . ucfirst($i);

                if (method_exists($jure, $method)) {
                    $equipeinter = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
                        ->where('e.lettre =:lettre')
                        ->setParameter('lettre', $i)
                        ->andWhere('e.edition =:edition')
                        ->setParameter('edition', $edition)
                        ->getQuery()->getResult();

                    $equipe = $this->doctrine->getRepository(Equipes::class)->findOneBy(['equipeinter' => $equipeinter]);
                    foreach ($attributions as $attribution)
                        if ($attribution->getEquipe() != null) {

                            $attribution->setEstLecteur($jure->$method());
                        } else {
                            $attribution = new Attributions();
                            $attribution->setEquipe($equipe);
                            $attribution->setJure($jure);
                            $attribution->setEstLecteur($jure->$method());

                        }
                    $this->em->persist($attribution);
                    $jure->addAttribution($attribution);
                    $this->em->persist($jure);
                    $this->em->flush();
                }
            }

        }


    }

    #[IsGranted('ROLE_JURY')]
    #[Route("recommandations,{id},{origin}", name: "cyberjury_recommandations")]
    public function recommandations(Request $request, $id, $origin)
    {

        $jure = $this->doctrine->getRepository(Jures::class)->findOneBy(['iduser' => $this->getUser()]);
        $equipe = $this->doctrine->getRepository(Equipes::class)->find($id);
        $memoire = $this->doctrine->getRepository(Fichiersequipes::class)->findOneBy(['equipe' => $equipe->getEquipeinter(), 'typefichier' => 0]);
        $attributions = null;
        $attributions = $this->doctrine->getRepository(Jures::class)->getAttribution($jure);

        $recommandation = $this->doctrine->getRepository(RecommandationsJuryCN::class)->findOneBy(['equipe' => $equipe]);
        if ($recommandation === null) {
            $recommandation = new RecommandationsJuryCN();
            $recommandation->setEquipe($equipe);
        }
        $form = $this->createForm(RecommandationsCnType::class, $recommandation);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            $this->doctrine->getManager()->persist($recommandation);
            $this->doctrine->getManager()->flush();
            if ($origin == 'evaluer') return $this->redirectToRoute('cyberjury_evaluer_une_equipe', ['id' => $equipe->getId()]);
            if ($origin == 'liste') return $this->redirectToRoute('cyberjury_liste_recommandations');

        }
        return $this->render('cyberjury/recommandations.html.twig', ['form' => $form->createView(), 'equipe' => $equipe, 'jure' => $jure, 'memoire' => $memoire, 'attributions' => $attributions]);
    }

    #[IsGranted('ROLE_JURY')]
    #[Route("liste_recommandations", name: "cyberjury_liste_recommandations")]
    public function liste_recommandations(Request $request): Response
    {
        $jure = $this->doctrine->getRepository(Jures::class)->findOneBy(['iduser' => $this->getUser()]);
        $attributions = $this->doctrine->getRepository(Jures::class)->getAttribution($jure);
        $equipes = $this->doctrine->getRepository(Equipes::class)->findAll();
        $recommandations = null;

        for ($i = 0; $i < count($attributions); $i++) {

            foreach ($equipes as $equipe) {

                if ($equipe->getEquipeinter()->getLettre() == key($attributions)) {

                    if ($attributions[$equipe->getEquipeinter()->getLettre()] > 0) {
                        $recommandations[$equipe->getId()] = $this->doctrine->getRepository(RecommandationsJuryCn::class)->findOneBy(['equipe' => $equipe]);
                        if ($recommandations[$equipe->getId()] === null) {
                            $recommandations[$equipe->getId()] = new RecommandationsJuryCn();
                            $recommandations[$equipe->getId()]->setEquipe($equipe);
                            $this->doctrine->getManager()->persist($recommandations[$equipe->getId()]);
                            $this->doctrine->getManager()->flush();
                        }
                    }
                }
            }
            next($attributions);

        }

        return $this->render('cyberjury/liste_recommandations.html.twig', ['recommandations' => $recommandations, 'jure' => $jure]);


    }
}