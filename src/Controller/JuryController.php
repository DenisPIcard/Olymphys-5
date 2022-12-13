<?php
// src/Controller/JuryController.php
namespace App\Controller;

use App\Entity\Cadeaux;
use App\Entity\Coefficients;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Jures;
use App\Entity\Liaison;
use App\Entity\Notes;
use App\Entity\Phrases;
use App\Entity\Prix;
use App\Entity\Repartprix;
use App\Entity\User;
use App\Form\NotesType;
use App\Form\PhrasesType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Proxies\__CG__\App\Entity\Fichiersequipes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


    /**
     * @Route("cyberjury/accueil", name="cyberjury_accueil")
     */
    public function accueil(Request $request): Response

    {
        $session = $this->requestStack->getSession();
        $edition = $session->get('edition');


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


        $id_jure = $jure->getId();

        $attrib = $jure->getAttributions();

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
//dd($memoires);
        $content = $this->renderView('cyberjury/accueil.html.twig',
            array('listeEquipes' => $listeEquipes, 'progression' => $progression, 'jure' => $jure, 'memoires' => $memoires)
        );


        return new Response($content);


    }

    /**
     * @Security("is_granted('ROLE_JURY')")
     *
     * @Route( "/infos_equipe/{id}", name ="cyberjury_infos_equipe",requirements={"id_equipe"="\d{1}|\d{2}"})
     * @throws NonUniqueResultException
     */
    public function infos_equipe(Request $request, Equipes $equipe, $id): Response
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

    /**
     * @Security("is_granted('ROLE_JURY')")
     *
     * @Route("/lescadeaux", name="cyberjury_lescadeaux")
     *
     */
    public function lescadeaux(Request $request)
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

    /**
     * @Security("is_granted('ROLE_JURY')")
     *
     * @Route("/lesprix", name="cyberjury_lesprix")
     *
     */
    public function lesprix(Request $request)
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

    /**
     * @Security("is_granted('ROLE_JURY')")
     *
     * @Route("palmares", name="cyberjury_palmares")
     *
     */
    public function palmares(Request $request)
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
            ->getRepository(User::class);
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

    /**
     *
     * @Security("is_granted('ROLE_JURY')")
     *
     * @Route("/evaluer_une_equipe/{id}", name="cyberjury_evaluer_une_equipe", requirements={"id_equipe"="\d{1}|\d{2}"})
     *
     * @throws NonUniqueResultException
     */
    public function evaluer_une_equipe(Request $request, Equipes $equipe, $id)
    {
        $user = $this->getUser();
        $jure = $this->doctrine->getRepository(Jures::class)->findOneBy(['iduser' => $user]);
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(User::class);

        $lettre = $equipe->getEquipeinter()->getLettre();


        $attrib = $jure->getAttributions();

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

        $flag = 0;

        if (is_null($notes)) {
            $notes = new Notes();
            $notes->setEquipe($equipe);
            $notes->setJure($jure);
            $progression = 0;
            $nllNote = true;
            if ($attrib[$lettre] == 1) {
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
            if ($attrib[$lettre] == 1) {
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
            if ($nllNote == true) {
                $nbNotes = count($equipe->getNotess());

                $equipe->setNbNotes($nbNotes + 1);

            }
            $em->persist($notes);
            $em->flush();

            //$request->getSession()->getFlashBag()->add('notice', 'Notes bien enregistrées');
            // puis on redirige vers la page de visualisation de cette note dans le tableau de bord
            return $this->redirectToroute('cyberjury_tableau_de_bord');
        }

        $type_salle = 'zoom';

        $content = $this->renderView('cyberjury/evaluer.html.twig',
            array(
                'equipe' => $equipe,
                'type_salle' => $type_salle,

                'form' => $form->createView(),
                'flag' => $flag,
                'progression' => $progression,
                'jure' => $jure,
                'coefficients' => $coefficients,
                'memoire' => $memoire
            ));
        return new Response($content);

    }

    /**
     * @Security("is_granted('ROLE_JURY')")
     *
     * @Route("/tableau_de_bord", name ="cyberjury_tableau_de_bord")
     *
     * @throws NonUniqueResultException
     */
    public function tableau(): Response
    {
        $user = $this->getUser();
        $jure = $this->doctrine->getRepository(Jures::class)->findOneBy(['iduser' => $user]);
        $id_jure = $jure->getId();

        $repositoryNotes = $this->doctrine
            ->getManager()
            ->getRepository(Notes::class);

        $queryBuilder = $repositoryNotes->createQueryBuilder('n');
        $queryBuilder
            ->where('n.jure=:id_jure')
            ->setParameter('id_jure', $id_jure)
            ->orderBy('n.total', 'DESC');

        $MonClassement = $queryBuilder->getQuery()->getResult();

        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $repositoryMemoires = $this->doctrine
            ->getManager()
            ->getRepository(Fichiersequipes::class);


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
            array('listEquipes' => $listEquipes, 'jure' => $jure, 'memoires' => $memoires)
        );
        return new Response($content);
    }

    /**
     *
     * @Security("is_granted('ROLE_JURY')")
     *
     *
     * @Route("/liste_phrases_amusantes/{id}", name = "cyberjury_phrases_amusantes",requirements={"id_equipe"="\d{1}|\d{2}"})
     * @throws NonUniqueResultException
     */
    public function liste_phrases_amusantes(Request $request, $id): Response
    {
        $user = $this->getUser();
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
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

    /**
     *
     * @Security("is_granted('ROLE_JURY')")
     *
     *
     * @Route("/edit_phrases/{id}", name = "cyberjury_edit_phrases_amusantes",requirements={"id_equipe"="\d{1}|\d{2}"})
     * @throws NonUniqueResultException
     */
    public function edit_phrases(Request $request, Equipes $equipe, $id)
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
            ->getRepository(User::class);
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

    /**
     *
     * @Security("is_granted('ROLE_JURY')")
     *
     *
     * @Route("/supr_phrase/{idphrase}", name = "cyberjury_suprim_phrase_amusante")
     */
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
        $equipe->removePhrases($phrase);
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

}