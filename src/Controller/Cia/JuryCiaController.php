<?php
// src/Controller/JuryController.php
namespace App\Controller\Cia;


use App\Entity\Centrescia;
use App\Entity\Cia\ConseilsjuryCia;
use App\Entity\Cia\JuresCia;
use App\Entity\Cia\NotesCia;
use App\Entity\Cia\RangsCia;
use App\Entity\Coefficients;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Notes;
use App\Entity\User;
use App\Form\ConseilJuryCiaType;
use App\Form\NotesCiaType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class JuryCiaController extends AbstractController
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

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("cyberjuryCia/accueil", name: "cyberjuryCia_accueil")]
    public function accueil(Request $request): Response

    {
        $session = $this->requestStack->getSession();
        $edition = $session->get('edition');


        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(JuresCia::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);

        if ($jure === null) {
            $request->getSession()->set('info', 'Vous avez été déconnecté');
            return $this->redirectToRoute('core_home');
        }


        $id_jure = $jure->getId();

        $equipes = $jure->getEquipes();

        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);

        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(NotesCia::class);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);

        $progression = array();
        $memoires = array();
        $listeEquipes = $repositoryEquipes->createQueryBuilder('e')
            ->where('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->andWhere('e.centre =:centre')
            ->setParameter('centre', $this->getUser()->getCentrecia())
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();
        foreach ($listeEquipes as $equipe) {

            foreach ($equipes as $equipejure) {

                if ($equipejure == $equipe) {
                    $key = $equipe->getNumero();
                    $id = $equipe->getId();
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id);
                    $progression[$key] = (!is_null($note)) ? 1 : 0;

                    try {
                        $memoires[$key] = $repositoryMemoires->createQueryBuilder('m')
                            ->where('m.edition =:edition')
                            ->setParameter('edition', $edition)
                            ->andWhere('m.typefichier = 0')
                            ->andWhere('m.equipe =:equipe')
                            ->setParameter('equipe', $equipe)
                            ->getQuery()->getSingleResult();
                    } catch (Exception $e) {
                        $memoires[$key] = null;
                    }
                }
            }
        }

        $content = $this->renderView('cyberjuryCia/accueil_jury.html.twig',
            array('listeEquipes' => $listeEquipes, 'progression' => $progression, 'jure' => $jure, 'memoires' => $memoires)
        );


        return new Response($content);


    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/cia/JuryCia/infos_equipe_cia/{id}", name: "cyberjuryCia_infos_equipe", requirements: ["id_equipe" => "\d{1}|\d{2}"])]
    public function infos_equipe_cia(Request $request, Equipesadmin $equipe, $id): Response
    {
        $repositoryJures = $this->doctrine
            ->getManager()
            ->getRepository(JuresCia::class);
        $user = $this->getUser();
        $jure = $repositoryJures->findOneBy(['iduser' => $user]);

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
        $equipeadmin = $repositoryEquipesadmin->find(['id' => $equipe->getId()]);

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
                'id_equipe' => $id,
                'progression' => $progression,
                'jure' => $jure,
                'memoires' => $memoires
            )
        );
        return new Response($content);
    }


    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/evaluer_une_equipe_cia/{id}", name: "cyberjuryCia_evaluer_une_equipe", requirements: ["id_equipe" => "\d{1}|\d{2}"])]
    public function evaluer_une_equipe_cia(Request $request, Equipesadmin $equipe, $id): RedirectResponse|Response
    {
        $user = $this->getUser();
        $jure = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['iduser' => $user]);
        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(NotesCia::class);

        $numero = $equipe->getNumero();


        $attrib = $jure->getRapporteur();

        $em = $this->doctrine->getManager();

        $notes = $this->doctrine
            ->getManager()
            ->getRepository(NotesCia::class)
            ->EquipeDejaNotee($jure, $id);

        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        try {

            $memoire = $repositoryMemoires->createQueryBuilder('m')
                ->where('m.equipe =:equipe')
                ->setParameter('equipe', $equipe)
                ->andWhere('m.typefichier = 0')
                ->andWhere('m.national = 0')
                ->getQuery()->getSingleResult();

        } catch (Exception $e) {
            $memoire = null;

        }

        $flag = 0;

        if (is_null($notes)) {
            $notes = new NotesCia();
            $notes->setEquipe($equipe);
            $notes->setJure($jure);
            $progression = 0;
            $nllNote = true;
            if (in_array($equipe->getNumero(), $attrib)) {
                $form = $this->createForm(NotesCiaType::class, $notes, array('EST_PasEncoreNotee' => true, 'EST_Lecteur' => true,));
                $flag = 1;
            } else {
                $notes->setEcrit(0);
                $form = $this->createForm(NotesCiaType::class, $notes, array('EST_PasEncoreNotee' => true, 'EST_Lecteur' => false,));
            }
        } else {
            $notes = $this->doctrine
                ->getManager()
                ->getRepository(NotesCia::class)
                ->EquipeDejaNotee($jure, $id);
            $progression = 1;
            $nllNote = false;
            if (in_array($equipe->getNumero(), $attrib)) {
                $form = $this->createForm(NotesCiaType::class, $notes, array('EST_PasEncoreNotee' => false, 'EST_Lecteur' => true,));
                $flag = 1;
            } else {
                $notes->setEcrit('0');
                $form = $this->createForm(NotesCiaType::class, $notes, array('EST_PasEncoreNotee' => false, 'EST_Lecteur' => false,));
            }
        }
        $coefficients = $this->doctrine->getRepository(Coefficients::class)->findOneBy(['id' => 1]);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $coefficients = $this->doctrine->getRepository(Coefficients::class)->findOneBy(['id' => 1]);
            $notes->setCoefficients($coefficients);
            $total = $notes->getPoints();
            $notes->setTotal($total);
            if ($nllNote) {
                $notesequipe = $repositoryNotes->createQueryBuilder('n')
                    ->where('n.equipe =:equipe')
                    ->setParameter('equipe', $equipe)
                    ->getQuery()->getResult();

            }
            $em->persist($notes);
            $em->flush();
            $repo = $this->doctrine->getRepository(RangsCia::class);
            $points = $repo->classement($this->getUser()->getCentreCia());
            //$request->getSession()->getFlashBag()->add('notice', 'Notes bien enregistrées');
            // puis on redirige vers la page de visualisation de cette note dans le tableau de bord
            return $this->redirectToroute('cyberjuryCia_tableau_de_bord', array('critere' => 'TOT', 'sens' => 'DESC'));
        }


        $content = $this->renderView('cyberjuryCia/evaluerCia.html.twig',
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

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/cyberjuryCia/tableau_de_bord,{critere},{sens}", name: "cyberjuryCia_tableau_de_bord")]
    public function tableau($critere, $sens): Response
    {
        $user = $this->getUser();
        $jure = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['iduser' => $user]);
        $id_jure = $jure->getId();
        $ordre = array(
            'EXP' => 'DESC',
            'DEM' => 'DESC',
            'ORI' => 'DESC',
            'TRE' => 'DESC',
            'ORA' => 'DESC',
            'REP' => 'DESC',
            'TOT' => 'DESC');
        $ordre[$critere] = $sens;
        $MonClassement = $this->tri($critere, $sens, $id_jure)->getQuery()->getResult();

        $repositorycoef = $this->doctrine
            ->getManager()
            ->getRepository(Coefficients::class);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(NotesCia::class);

        $rangs = $repositoryNotes->get_rangs($id_jure);

        $memoires = array();
        $listEquipes = array();
        $j = 1;
        foreach ($MonClassement as $notes) {
            $equipe = $notes->getEquipe();
            $listEquipes[$j]['id'] = $equipe->getId();
            $listEquipes[$j]['infoequipe'] = $equipe;
            $listEquipes[$j]['lettre'] = $equipe->getNumero();
            $listEquipes[$j]['titre'] = $equipe->getTitreProjet();
            $listEquipes[$j]['exper'] = $notes->getExper();
            $listEquipes[$j]['demarche'] = $notes->getDemarche();
            $listEquipes[$j]['oral'] = $notes->getOral();
            $listEquipes[$j]['repq'] = $notes->getRepquestions();
            $listEquipes[$j]['origin'] = $notes->getOrigin();
            $listEquipes[$j]['wgroupe'] = $notes->getWgroupe();
            $listEquipes[$j]['ecrit'] = $notes->getEcrit();
            $listEquipes[$j]['points'] = $notes->getPoints();

            $memoires[$j] = $repositoryMemoires->createQueryBuilder('m')
                ->andWhere('m.equipe =:equipe')
                ->setParameter('equipe', $equipe)
                ->andWhere('m.national =:valeur')
                ->setParameter('valeur', 1)
                ->andWhere('m.typefichier =:typefichier')
                ->setParameter('typefichier', '0')
                ->getQuery()->getOneOrNullResult();

            $j++;

        }
        $coefs = $repositorycoef->findAll();
        $coef = $coefs[0];
        $content = $this->renderView('cyberjuryCia/tableau.html.twig',
            array('listEquipes' => $listEquipes, 'jure' => $jure, 'coef' => $coef, 'memoires' => $memoires, 'ordre' => $ordre, 'critere' => $critere, 'rangs' => $rangs)
        );
        return new Response($content);
    }


    public function tri($critere, $sens, $id_jure): QueryBuilder
    {
        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(NotesCia::class);

        $queryBuilder = $repositoryNotes->createQueryBuilder('n');
        $queryBuilder
            ->where('n.jure=:id_jure')
            ->setParameter('id_jure', $id_jure);
        switch ($critere) {
            case 'EXP':
                $queryBuilder->orderBy('n.exper', $sens);
                break;
            case('ORI') :
                $queryBuilder->orderBy('n.origin', $sens);
                break;
            case('ORA') :
                $queryBuilder->orderBy('n.oral', $sens);
                break;
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

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/cyberjuryCia/rediger_conseils_equipe,{idEquipe},{page}", name: "cyberjuryCia_rediger_conseils_equipe")]
    public function rediger_conseils_jury(Request $request, $idEquipe, $page)
    {
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $jure = $repositoryJures->findOneBy(['iduser' => $this->getUser()]);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $equipe = $repositoryEquipes->find($idEquipe);
        $repositoryConseils = $this->doctrine->getRepository(ConseilsjuryCia::class);
        $conseil = $repositoryConseils->findOneBy(['equipe' => $equipe]);

        if ($conseil === null) {
            $conseil = new ConseilsjuryCia();
            //$conseil->setJure($jure);
            $conseil->setEquipe($equipe);
        }
        $form = $this->createForm(ConseilJuryCiaType::class, $conseil);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->doctrine->getManager()->persist($conseil);
            $this->doctrine->getManager()->flush();
            if ($page == 'evaluation') {
                return $this->redirectToRoute('cyberjuryCia_evaluer_une_equipe', ['id' => $equipe->getId()]);
            }
            if ($page == 'classement') {
                return $this->redirectToRoute('secretariatjuryCia_classement', ['centre' => $this->getUser()->getCentrecia()]);
            }
        }
        return $this->render('cyberjuryCia/conseils_jury_cia.html.twig', array('equipe' => $equipe, 'form' => $form->createView(),));
    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/cyberjuryCia/gerer_conseils_equipe,{centre}", name: "cyberjuryCia_gerer_conseils_equipe")]
    public function gerer_conseils_jury(Request $request, $centre)
    {
        $edition = $this->requestStack->getSession()->get('edition');
        $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $equipes = $repositoryEquipes->findBy(['centre' => $centre, 'edition' => $edition]);
        $repositoryConseils = $this->doctrine->getRepository(ConseilsjuryCia::class);
        $conseils = $repositoryConseils->createQueryBuilder('c')
            ->select()
            ->leftJoin('c.equipe', 'eq')
            ->andWhere('eq.edition =:edition')
            ->andWhere('eq.centre =:centre')
            ->setParameters(['edition' => $edition, 'centre' => $centre])
            ->getQuery()->getResult();

        return $this->render('cyberjuryCia/gestion_conseils_jury_cia.html.twig', array('equipes' => $equipes, 'conseils' => $conseils, 'centre' => $centre));
    }

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/cia/JuryCia/modif_rang_equipe_cia, {idRang},{sens}", name: "cyberjuryCia_modif_rang_equipe_cia", requirements: ["equipe" => "\d{1}|\d{2}"])]
    public function modif_rang_equipe_cia(Request $request, $idRang, $sens): Response
    {
        $rangEquipe = $this->doctrine->getRepository(RangsCia::class)->find($idRang);

        if ($sens == 'up') {
            $rangEquipeUp = $this->doctrine->getRepository(RangsCia::class)->createQueryBuilder('r')
                ->leftJoin('r.equipe', 'eq')
                ->where('eq.centre =:centre')
                ->andWhere('r.rang =:rang')
                ->setParameters(['centre' => $this->getUser()->getCentrecia(), 'rang' => $rangEquipe->getRang() - 1])
                ->getQuery()->getOneOrNullResult();

            $nouveauRang = $rangEquipe->getRang() - 1;
            $rangEquipeUp->setRang($rangEquipe->getRang());
            $rangEquipe->setRang($nouveauRang);
            $this->doctrine->getManager()->persist($rangEquipeUp);
            $this->doctrine->getManager()->persist($rangEquipe);
            $this->doctrine->getManager()->flush();
        }
        if ($sens == 'down') {
            $rangEquipeDown = $this->doctrine->getRepository(RangsCia::class)->createQueryBuilder('e')
                ->leftJoin('e.equipe', 'eq')
                ->where('eq.centre =:centre')
                ->andWhere('e.rang =:rang')
                ->setParameters(['centre' => $this->getUser()->getCentrecia(), 'rang' => $rangEquipe->getRang() + 1])
                ->getQuery()->getOneOrNullResult();
            $nouveauRang = $rangEquipe->getRang() + 1;
            $rangEquipeDown->setRang($rangEquipe->getRang());
            $rangEquipe->setRang($nouveauRang);
            $this->doctrine->getManager()->persist($rangEquipeDown);
            $this->doctrine->getManager()->persist($rangEquipe);
            $this->doctrine->getManager()->flush();
        }

        return $this->redirectToRoute('secretariatjuryCia_classement', ['centre' => $this->getUser()->getCentrecia()]);

    }
}