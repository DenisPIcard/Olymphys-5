<?php
// src/Controller/JuryController.php
namespace App\Controller\Cia;


use App\Entity\Cia\JuresCia;
use App\Entity\Cia\NotesCia;
use App\Entity\Coefficients;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Jures;
use App\Entity\Notes;
use App\Entity\User;
use App\Form\NotesType;
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
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => true, 'EST_Lecteur' => true,));
                $flag = 1;
            } else {
                $notes->setEcrit(0);
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => true, 'EST_Lecteur' => false,));
            }
        } else {
            $notes = $this->doctrine
                ->getManager()
                ->getRepository(Notes::class)
                ->EquipeDejaNotee($jure, $id);
            $progression = 1;
            $nllNote = false;
            if (in_array($equipe->getNumero(), $attrib)) {
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => false, 'EST_Lecteur' => true,));
                $flag = 1;
            } else {
                $notes->setEcrit('0');
                $form = $this->createForm(NotesType::class, $notes, array('EST_PasEncoreNotee' => false, 'EST_Lecteur' => false,));
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
                $nbNotes = count($notesequipe);

                $equipe->setNbNotes($nbNotes + 1);
                $em->persist($equipe);
            }
            $em->persist($notes);
            $em->flush();

            //$request->getSession()->getFlashBag()->add('notice', 'Notes bien enregistrées');
            // puis on redirige vers la page de visualisation de cette note dans le tableau de bord
            return $this->redirectToroute('cyberjury_tableau_de_bord');
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

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/tableau_de_bord_cia,{critere},{sens}", name: "cyberjury_tableau_de_bord_cia")]
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
            'TOT' => 'DESC');
        $ordre[$critere] = $sens;
        $MonClassement = $this->classement($critere, $sens, $id_jure)->getQuery()->getResult();

        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);
        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class);

        $rangs = $repositoryNotes->get_rangs($id_jure);

        $memoires = array();
        $listEquipes = array();
        $j = 1;
        foreach ($MonClassement as $notes) {
            $id = $notes->getEquipe();
            $equipe = $repositoryEquipes->find($id);

            $listEquipes[$j]['id'] = $equipe->getId();
            $listEquipes[$j]['infoequipe'] = $equipe->getEquipeinter();
            $listEquipes[$j]['lettre'] = $equipe->getEquipeinter()->getLettre();
            $listEquipes[$j]['titre'] = $equipe->getEquipeinter()->getTitreProjet();
            $listEquipes[$j]['exper'] = $notes->getExper();
            $listEquipes[$j]['demarche'] = $notes->getDemarche();
            $listEquipes[$j]['oral'] = $notes->getOral();
            $listEquipes[$j]['origin'] = $notes->getOrigin();
            $listEquipes[$j]['wgroupe'] = $notes->getWgroupe();
            $listEquipes[$j]['ecrit'] = $notes->getEcrit();
            $listEquipes[$j]['points'] = $notes->getPoints();
            $listEquipes[$j]['total'] = $notes->getTotal();
            $memoires[$j] = $repositoryMemoires->createQueryBuilder('m')
                ->andWhere('m.equipe =:equipe')
                ->setParameter('equipe', $equipe->getEquipeinter())
                ->andWhere('m.national =:valeur')
                ->setParameter('valeur', 1)
                ->andWhere('m.typefichier =:typefichier')
                ->setParameter('typefichier', '0')
                ->getQuery()->getOneOrNullResult();

            $j++;

        }

        $content = $this->renderView('cyberjury/tableau.html.twig',
            array('listEquipes' => $listEquipes, 'jure' => $jure, 'memoires' => $memoires, 'ordre' => $ordre, 'critere' => $critere, 'rangs' => $rangs)
        );
        return new Response($content);
    }


    public function classement($critere, $sens, $id_jure): QueryBuilder
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

}