<?php

namespace App\Controller;

use App\Entity\Coefficients;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Jures;
use App\Entity\Notes;
use App\Entity\Prix;
use App\Entity\Repartprix;
use App\Entity\Rne;
use App\Form\EquipesType;
use App\Form\PrixExcelType;
use App\Form\PrixType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Proxies\__CG__\App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class SecretariatjuryController extends AbstractController
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }


    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/accueil", name="secretariatjury_accueil")
     *
     */
    public function accueil(Request $request): Response
    {
        $edition = $this->requestStack->getSession()->get('edition');
        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryEleves = $this->doctrine
            ->getManager()
            ->getRepository(Elevesinter::class);
        $repositoryRne = $this->doctrine
            ->getManager()
            ->getRepository(Rne::class);
       /* $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);*/
        $listEquipes = $repositoryEquipesadmin->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.edition =:edition')
            ->andWhere('e.numero <:numero')
            ->setParameters(['edition' => $edition, 'numero' => 100])
            ->andWhere('e.selectionnee= TRUE')
            ->orderBy('e.lettre', 'ASC')
            ->getQuery()
            ->getResult();
        $lesEleves = [];
        $lycee = [];

        foreach ($listEquipes as $equipe) {
            $lettre = $equipe->getLettre();
            $lesEleves[$lettre] = $repositoryEleves->findBy(['equipe' => $equipe]);
            $rne = $equipe->getRne();
            $lycee[$lettre] = $repositoryRne->findBy(['rne' => $rne]);
        }

        $tableau = [$listEquipes, $lesEleves, $lycee];
        $session = $this->requestStack->getSession();
        $session->set('tableau', $tableau);
        $content = $this->renderView('secretariatjury/accueil.html.twig',
            array(''));

        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/accueil_jury", name="secretariatjury_accueil_jury")
     *
     */
    public function accueilJury(Request $request): Response
    {
        $tableau = $this->requestStack->getSession()->get('tableau');
        $listEquipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];
        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $prof1=[];
        $prof2=[];
        foreach ($listEquipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findBy(['id' => $idprof1]);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findBy(['id' => $idprof2]);
        }

        $content = $this->renderView('secretariatjury/accueil_jury.html.twig',
            array('listEquipes' => $listEquipes,
                'lesEleves' => $lesEleves,
                'prof1' => $prof1,
                'prof2' => $prof2,
                'lycee' => $lycee));

        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/vueglobale", name="secretariatjury_vueglobale")
     *
     */
    public function vueglobale(): Response
    {
        $repositoryNotes = $this
            ->doctrine
            ->getManager()
            ->getRepository(Notes::class);

        $repositoryJures = $this
            ->doctrine
            ->getManager()
            ->getRepository(Jures::class);
        $listJures = $repositoryJures->findAll();

        $repositoryEquipes = $this
            ->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $listEquipes = $repositoryEquipes->findAll();

        $nbre_equipes = 0;
        $progression = [];
        $nbre_jures = 0;
        foreach ($listEquipes as $equipe) {
            $nbre_equipes = $nbre_equipes + 1;
            $id_equipe = $equipe->getId();
            $lettre = $equipe->getEquipeinter()->getLettre();
            $nbre_jures = 0;
            foreach ($listJures as $jure) {
                $id_jure = $jure->getId();
                $nbre_jures += 1;
                //vérifie l'attribution du juré ! 0 si assiste, 1 si lecteur sinon Null
                $method = 'get' . ucfirst($lettre);
                $statut = $jure->$method();
                //récupère l'évaluation de l'équipe par le juré dans $note pour l'afficher
                if (is_null($statut)) {
                    $progression[$nbre_equipes][$nbre_jures] = 'ras';

                } elseif ($statut == 1) {
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getSousTotal();
                } else {
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getPoints();
                }
            }
        }

        $content = $this->renderView('secretariatjury/vueglobale.html.twig', array(
            'listJures' => $listJures,
            'listEquipes' => $listEquipes,
            'progression' => $progression,
            'nbre_equipes' => $nbre_equipes,
            'nbre_jures' => $nbre_jures,
        ));

        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/classement", name="secretariatjury_classement")
     *
     */
    public function classement(): Response
    {
        // affiche les équipes dans l'ordre de la note brute
        $em = $this->doctrine->getManager();
        $repositoryEquipes = $em->getRepository(Equipes::class);

        $coefficients = $em->getRepository(Coefficients::class)->findOneBy(['id' => 1]);

        $listEquipes = $repositoryEquipes->findAll();

        foreach ($listEquipes as $equipe) {
            $listesNotes = $equipe->getNotess();
            $nbre_notes = $equipe->getNbNotes();

            $nbre_notes_ecrit = 0;
            $points_ecrit = 0;
            $points = 0;


            if ($nbre_notes == 0) {
                $equipe->setTotal(0);
                $em->persist($equipe);
                $em->flush();
            } else {
                foreach ($listesNotes as $note) {
                    $points = $points + $note->getPoints();

                    $nbre_notes_ecrit = ($note->getEcrit()) ? $nbre_notes_ecrit + 1 : $nbre_notes_ecrit;
                    $points_ecrit = $points_ecrit + $note->getEcrit() * $coefficients->getEcrit();
                }
            }
        }

        $nbre_equipes = 0;
        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        try {
            $nbre_equipes = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }

        $classement = $repositoryEquipes->classement(0, 0, $nbre_equipes);


        $em->flush();

        $content = $this->renderView('secretariatjury/classement.html.twig',
            array('classement' => $classement)
        );
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/lesprix", name="secretariatjury_lesprix")
     *
     */
    public function lesprix(): Response
    { //affiche la liste des prix prévus
        $repositoryPrix = $this->doctrine
            ->getManager()
            ->getRepository(Prix::class);
        $ListPremPrix = $repositoryPrix->findBy(['niveau' => '1er']);

        $ListDeuxPrix = $repositoryPrix->findBy(['niveau' => '2ème']);

        $ListTroisPrix = $repositoryPrix->findBy(['niveau' => '3ème']);

        $content = $this->renderView('secretariatjury/lesprix.html.twig',
            array('ListPremPrix' => $ListPremPrix,
                'ListDeuxPrix' => $ListDeuxPrix,
                'ListTroisPrix' => $ListTroisPrix)
        );
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/modifier_prix/{id_prix}", name="secretariatjury_modifier_prix", requirements={"id_prix"="\d{1}|\d{2}"}))
     */
    public function modifier_prix(Request $request, $id_prix): Response
    { //permet de modifier le niveau d'un prix(id_prix), modifie alors le 'repartprix" (répartition des prix)

        $em = $this->doctrine->getManager();
        $repositoryPrix = $em->getRepository(Prix::class);
        $repositoryRepartprix = $em->getRepository(Repartprix::class);
        $prix = $repositoryPrix->find($id_prix);

        $form = $this->createForm(PrixType::class, $prix);
        $nbrePremPrix = 0;
        $nbreDeuxPrix = 0;
        $nbreTroisPrix = 0;
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($prix);
            $em->flush();
            $niveau = $repositoryRepartprix->findOneBy(['niveau' => '1er']);
            try {
                $nbrePremPrix = $repositoryPrix->getNbrePrix('1er');
            } catch (NoResultException|NonUniqueResultException $e) {
            }
            $niveau->setNbreprix($nbrePremPrix);
            $em->persist($niveau);
            $niveau = $repositoryRepartprix->findOneBy(['niveau' => '2ème']);
            try {
                $nbreDeuxPrix = $repositoryPrix->getNbrePrix('2ème');
            } catch (NoResultException|NonUniqueResultException $e) {
            }
            $niveau->setNbreprix($nbreDeuxPrix);
            $em->persist($niveau);
            $niveau = $repositoryRepartprix->findOneBy(['niveau' => '3ème']);
            try {
                $nbreTroisPrix = $repositoryPrix->getNbrePrix('3ème');
            } catch (NoResultException|NonUniqueResultException $e) {
            }
            $niveau->setNbreprix($nbreTroisPrix);
            $em->persist($niveau);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Modifications bien enregistrées');
            return $this->redirectToroute('secretariatjury_lesprix');
        }
        $content = $this->renderView('secretariatjury/modifier_prix.html.twig',
            array(
                'prix' => $prix,
                'id_prix' => $id_prix,
                'form' => $form->createView(),
            ));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/approche", name="secretariatjury_approche")
     *
     */
    public function approche(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $repositoryEquipes = $em->getRepository(Equipes::class);
        $nbre_equipes = 0;
        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        try {
            $nbre_equipes = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }

        $classement = $repositoryEquipes->classement(0, 0, $nbre_equipes);

        foreach (range('A', 'Z') as $lettre) {

            if ($request->query->get($lettre) != null) {

                $couleur = $request->query->get($lettre);
                $idequipe = $request->query->get('idEquipe');

                $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
                $equipe->setCouleur($couleur);

                $em->persist($equipe);
                $em->flush();
            }

        }

        $content = $this->renderView('secretariatjury/approche.html.twig',
            array('classement' => $classement,
            )
        );

        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/classement_definitif", name="secretariatjury_classement_definitif")
     *
     */
    public function classementdefinitif(): Response
    {
        $em = $this->doctrine->getManager();

        $repositoryEquipes = $em->getRepository(Equipes::class);
        $qb = $repositoryEquipes->createQueryBuilder('e')
            ->orderBy('e.couleur', 'ASC')
            ->leftJoin('e.equipeinter', 'i')
            ->addSelect('i')
            ->addOrderBy('i.lettre', 'ASC');

        $classement = $qb->getQuery()->getResult();
        $class = null;
        foreach ($classement as $equipe) {
            $couleur = $equipe->getCouleur();
            switch ($couleur) {
                case 1 :
                    $class = '1er';
                    break;
                case 2 :
                    $class = '2ème';
                    break;
                case 3 :
                    $class = '3ème';
                    break;

            }
            $equipe->setClassement($class);
            $em->persist($equipe);
        }
        $em->flush();

        $content = $this->renderView('secretariatjury/classement_definitif.html.twig',
            array('classement' => $classement,)
        );
        return new Response($content);

    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/mise_a_zero", name="secretariatjury_mise_a_zero")
     *
     */
    public function RaZ(): Response
    {
        $em = $this->doctrine->getManager();
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);

        $repositoryPrix = $this->doctrine
            ->getManager()
            ->getRepository(Prix::class);
        $ListPrix = $repositoryPrix->findAll();
        $ListeEquipes = $repositoryEquipes->findAll();
        foreach ($ListeEquipes as $equipe) {
            $equipe->setPrix(null);
            $em->persist($equipe);
        }

        foreach ($ListPrix as $Prix) {
            $Prix->setAttribue(0);// rajouter $em->persist($Prix);?
        }
        $em->flush();
        $content = $this->renderView('secretariatjury/RaZ.html.twig');
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/attrib_prix/{niveau}", name="secretariatjury_attrib_prix", requirements={"niveau"="\d{1}"}))
     *
     */
    public function attrib_prix(Request $request, $niveau)
    {
        $niveau_court = "";
        $niveau_long = "";
        switch ($niveau) {
            case 1:
                $niveau_court = '1er';
                $niveau_long = 'premiers';
                break;

            case 2:
                $niveau_court = '2ème';
                $niveau_long = 'deuxièmes';
                break;
            case 3:
                $niveau_court = '3ème';
                $niveau_long = 'troisièmes';
                break;
        }
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $repositoryRepartprix = $this->doctrine
            ->getManager()
            ->getRepository(Repartprix::class);
        $repositoryPrix = $this->doctrine
            ->getManager()
            ->getRepository(Prix::class);

        $ListEquipes = $repositoryEquipes->findBy(['classement' => $niveau_court]);
        $NbrePrix = $repositoryRepartprix->findOneBy(['niveau' => $niveau_court])
            ->getNbreprix();

        $i = 0;
        $formtab = [];
        foreach ($ListEquipes as $equipe) {
            $qb2[$i] = $repositoryPrix->createQueryBuilder('p')
                ->where('p.niveau = :nivo')
                ->setParameter('nivo', $niveau_court);
            $attribue = 0;
            $Prix_eq = $equipe->getPrix();
            $intitule_prix = '';
            if ($Prix_eq != null) { //réunir les deux "if" en un seul ?
                $intitule_prix = $Prix_eq->getPrix();
                $qb2[$i]->andwhere('p.id = :prix_sel')
                    ->setParameter('prix_sel', $Prix_eq->getId());
            }
            if (!$Prix_eq) {
                $qb2[$i]->andwhere('p.attribue = :attribue')
                    ->setParameter('attribue', $attribue);
            }

            $formBuilder[$i] = $this->createFormBuilder($equipe);
            $lettre = strtoupper($equipe->getEquipeinter()->getLettre());
            $titre = $equipe->getEquipeinter()->getTitreProjet();
            $id = $equipe->getId();
            //$titre_form[$i]=$lettre." : ".$titre.".  Prix :  ".$intitule_prix;
            $formBuilder[$i]->add('prix', EntityType::class, [
                    'class' => Prix::class,
                    'query_builder' => $qb2[$i],
                    'choice_label' => 'getPrix',
                    'multiple' => false,
                    'label' => $lettre . " : " . $titre . "      " . $intitule_prix]
            );
            $formBuilder[$i]->add('lettre', HiddenType::class, ['data' => $equipe->getEquipeinter()->getLettre(), 'mapped' => false]);
            $formBuilder[$i]->add('id', HiddenType::class, ['data' => $id, 'mapped' => false]);
            $formBuilder[$i]->add('Enregistrer', SubmitType::class);
            $formBuilder[$i]->add('Effacer', SubmitType::class);
            $form[$i] = $formBuilder[$i]->getForm();
            $formtab[$i] = $form[$i]->createView();
            if ($request->isMethod('POST') && $form[$i]->handleRequest($request)->isValid()) {
                $em = $this->doctrine->getManager();


                foreach (range('A', 'Z') as $lettre_equipe) {
                    if ($form[$i]->get('lettre')->getData() == $lettre_equipe) {
                        $equipe = $repositoryEquipes->findOneBy(['id' => $form[$i]->get('id')->getData()]);
                        //$lettre_equipe = $equipe->getEquipeinter()->getLettre();
                        $prix = $equipe->getPrix();
                        if ($form[$i]->get('Enregistrer')->isClicked()) {

                            $prix->setAttribue(1);
                            $em->persist($equipe);

                            $em->persist($prix);
                            $em->flush();
                            $request->getSession()->getFlashBag()->add('notice', 'Prix bien enregistrés');
                            return $this->redirectToroute('secretariatjury_attrib_prix', array('niveau' => $niveau));

                        }
                        if ($form[$i]->get('Effacer')->isClicked()) {
                            if ($prix !== null) {
                                $equipe->setPrix(null);
                                $prix->setAttribue(false);
                                $em->persist($equipe);
                                $em->persist($prix);
                                $em->flush();
                            }
                            $request->getSession()->getFlashBag()->add('notice', 'Prix bien effacé');
                            return $this->redirectToroute('secretariatjury_attrib_prix', array('niveau' => $niveau));

                        }
                    }

                }
            }
            $i = $i + 1;
        }

        $content = $this->renderView('secretariatjury/attrib_prix.html.twig',
            array('ListEquipes' => $ListEquipes,
                'NbrePrix' => $NbrePrix,
                'niveau' => $niveau_long,
                'formtab' => $formtab,
            )
        );
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/edition_prix", name="secretariatjury_edition_prix")
     *
     */
    public function edition_prix(): Response
    {
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesPrix();
        $content = $this->renderView('secretariatjury/edition_prix.html.twig', array('listEquipes' => $listEquipes));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/edition_visites", name="secretariatjury_edition_visites")
     *
     */
    public function edition_visites(): Response
    {
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesVisites();
        $content = $this->renderView('secretariatjury/edition_visites.html.twig', array('listEquipes' => $listEquipes));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/lescadeaux", name="secretariatjury_lescadeaux")
     *
     */
    public function lescadeaux(Request $request, $compteur = 1): RedirectResponse|Response
    {
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $nbreEquipes = 0;
        try {
            $nbreEquipes = $repositoryEquipes->createQueryBuilder('e')
                ->select('COUNT(e)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }
        $compteur > $nbreEquipes ? $compteur = 1 : $compteur = $compteur;
        $listEquipesCadeaux = $repositoryEquipes->getEquipesCadeaux();
        $listEquipesPrix = $repositoryEquipes->getEquipesPrix();
        $equipe = $repositoryEquipes->findOneBy(['rang' => $compteur]);
        if (is_null($equipe)) {
            $content = $this->renderView('secretariatjury/edition_cadeaux.html.twig',
                array(
                    'listEquipesCadeaux' => $listEquipesCadeaux,
                    'listEquipesPrix' => $listEquipesPrix,
                    'nbreEquipes' => $nbreEquipes,
                    'compteur' => $compteur,));
            return new Response($content);
        }
        $cadeau = $equipe->getCadeau();
        if (is_null($cadeau)) {
            $flag = 0;
            $array = array(
                'Attrib_Phrases' => false,
                'Attrib_Cadeaux' => true,
                'Deja_Attrib' => false,
                'Attrib_Couleur' => false,
            );
        } else {
            $flag = 1;
            $array = array(
                'Attrib_Phrases' => false,
                'Attrib_Cadeaux' => true,
                'Deja_Attrib' => true,
                'Attrib_Couleur' => false,
            );
        }
        $form = $this->createForm(EquipesType::class, $equipe, $array);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($equipe);
            if ($form->get('cadeau')->getData()->getAttribue() == false) {
                $cadeau->setAttribue(false);
                $flag = 0;
                $equipe->setCadeau();
            }
            $em->persist($cadeau);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Cadeaux bien enregistrés');
            if ($compteur <= $nbreEquipes) {
                return $this->redirectToroute('secretariatjury_lescadeaux', array('compteur' => $compteur + 1));
            } else {
                $content = $this->renderView('secretariatjury/edition_cadeaux.html.twig',
                    array('equipe' => $equipe,
                        'form' => $form->createView(),
                        'attribue' => $flag,
                        'listEquipesCadeaux' => $listEquipesCadeaux,
                        'listEquipesPrix' => $listEquipesPrix,
                        'nbreEquipes' => $nbreEquipes,
                        'compteur' => $compteur,));
                return new Response($content);
            }
        }

        $content = $this->renderView('secretariatjury/edition_cadeaux.html.twig',
            array('equipe' => $equipe,
                'form' => $form->createView(),
                'attribue' => $flag,
                'listEquipesCadeaux' => $listEquipesCadeaux,
                'listEquipesPrix' => $listEquipesPrix,
                'nbreEquipes' => $nbreEquipes,
                'compteur' => $compteur,));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/edition_cadeaux", name="secretariatjury_edition_cadeaux")
     *
     */
    public function edition_cadeaux(): Response
    {
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesCadeaux();

        $content = $this->renderView('secretariatjury/edition_cadeaux2.html.twig', array('listEquipes' => $listEquipes));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/edition_phrases", name="secretariatjury_edition_phrases")
     *
     */
    public function edition_phrases(): Response
    {
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesPhrases();

        $content = $this->renderView('secretariatjury/edition_phrases.html.twig', array('listEquipes' => $listEquipes));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/palmares_complet", name="secretariatjury_edition_palmares_complet")
     *
     */
    public function tableau_palmares_complet(): Response
    {
        $tableau = $this->requestStack->getSession()->get('tableau');

        $equipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];

        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $prof1 = [];
        $prof2 = [];
        foreach ($equipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findBy(['id' => $idprof1]);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findBy(['id' => $idprof2]);
        }

        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesPalmares();
        $content = $this->renderView('secretariatjury/edition_palmares_complet.html.twig',
            array('listEquipes' => $listEquipes,
                'lesEleves' => $lesEleves,
                'lycee' => $lycee,
                'prof1' => $prof1,
                'prof2' => $prof2));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/excel_site", name="secretariatjury_tableau_excel_palmares_site")
     *
     * @throws Exception
     */
    public function tableau_excel_palmares_site()
    {
        $tableau = $this->requestStack->getSession()->get('tableau');
        $equipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];
        $nbreEquipes = 0;
        $prof1 = [];
        $prof2 = [];
        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        foreach ($equipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findBy(['id' => $idprof1]);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findBy(['id' => $idprof2]);

        }
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesPalmares();

        $repositoryEquipes = $this
            ->doctrine
            ->getManager()
            ->getRepository(Equipes::class);

        try {
            $nbreEquipes = $repositoryEquipes
                ->createQueryBuilder('e')
                ->select('COUNT(e)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }
        $edition = $this->requestStack->getSession()->get('edition');
        $date = $edition->getDate();
        $result = $date->format('d/m/Y');
        $edition = $edition->getEd();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("Palmarès de la " . $edition . "ème édition - " . $result)
            ->setSubject("Palmarès")
            ->setDescription("Palmarès avec Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);


        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(6);

        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);

        $borderArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];
        $centerArray = [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'textRotation' => 0,
            'wrapText' => TRUE
        ];
        $vcenterArray = [
            'vertical' => Alignment::VERTICAL_CENTER,
            'textRotation' => 0,
            'wrapText' => TRUE
        ];
        $nblignes = $nbreEquipes * 4 + 3;

        $ligne = 3;

        $sheet->setCellValue('A' . $ligne, 'Académie')
            ->setCellValue('B' . $ligne, 'Lycée, sujet, élèves')
            ->setCellValue('C' . $ligne, 'Professeurs')
            ->mergeCells('D' . $ligne . ':E' . $ligne)
            ->setCellValue('D' . $ligne, 'Prix - Visite de laboratoire - Prix en matériel scientifique');
        $sheet->getStyle('A' . $ligne)->applyFromArray($borderArray);
        $sheet->getStyle('B' . $ligne)->applyFromArray($borderArray);
        $sheet->getStyle('C' . $ligne)->applyFromArray($borderArray);
        $sheet->getStyle('D' . $ligne)->applyFromArray($borderArray);
        $sheet->getStyle('E' . $ligne)->applyFromArray($borderArray);
        $sheet->getStyle('A' . $ligne . ':D' . $ligne)->getAlignment()->applyFromArray($centerArray);
        $ligne += 1;

        foreach ($listEquipes as $equipe) {
            $lettre = $equipe->getEquipeinter()->getLettre();

            $ligne4 = $ligne + 3;
            $sheet->mergeCells('A' . $ligne . ':A' . $ligne);
            $sheet->setCellValue('A' . $ligne, strtoupper($lycee[$lettre][0]->getAcademie()))
                ->setCellValue('B' . $ligne, 'Lycée ' . $lycee[$lettre][0]->getNom() . " - " . $lycee[$lettre][0]->getCommune())
                ->setCellValue('C' . $ligne, $prof1[$lettre][0]->getPrenom() . " " . strtoupper($prof1[$lettre][0]->getNom()))
                ->setCellValue('D' . $ligne, $equipe->getClassement() . ' ' . 'prix');
            if ($equipe->getPhrase() !== null) {
                $sheet->setCellValue('E' . $ligne, $equipe->getPhrase()->getPhrase() . ' ' . $equipe->getPhrase()->getLiaison()->getLiaison() . ' ' . $equipe->getPhrase()->getPrix());
            } else {
                $sheet->setCellValue('E' . $ligne, 'Phrase');
            }
            $sheet->getStyle('A' . $ligne)->getFont()->setSize(7)->setBold(2);
            $sheet->getStyle('A' . $ligne . ':A' . $ligne4)->applyFromArray($borderArray);
            $sheet->getStyle('C' . $ligne)->getAlignment()->applyFromArray($centerArray);
            $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $ligne . ':A' . $ligne4)->getAlignment()->applyFromArray($centerArray);
            $sheet->getStyle('A' . $ligne . ':E' . $ligne)->getFont()->getColor()->setRGB('000099');

            $lignes = $ligne + 3;
            $sheet->getStyle('D' . $ligne . ':E' . $lignes)->applyFromArray($borderArray);
            $sheet->getStyle('C' . $ligne . ':C' . $lignes)->applyFromArray($borderArray);
            $classement = $equipe->getClassement();
            $couleur = '0000';
            switch ($classement) {
                case '1er':
                    $couleur = 'ffccff';
                    break;
                case '2ème':
                    $couleur = '99ffcc';
                    break;
                case '3ème' :
                    $couleur = 'ccff99';
                    break;
            }
            $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($couleur);

            $ligne = $ligne + 1;

            $ligne3 = $ligne + 1;
            $sheet->mergeCells('B' . $ligne . ':B' . $ligne3);
            $sheet->setCellValue('B' . $ligne, $equipe->getEquipeinter()->getTitreProjet());
            if ($prof2[$lettre] != []) {
                $sheet->setCellValue('C' . $ligne, $prof2[$lettre][0]->getPrenom() . ' ' . strtoupper($prof2[$lettre][0]->getNom()));
            }
            if ($equipe->getPrix() !== null) {
                $sheet->setCellValue('E' . $ligne, $equipe->getPrix()->getPrix());
            }
            $sheet->getStyle('B' . $ligne . ':B' . $ligne3)->applyFromArray($borderArray);
            $sheet->getStyle('B' . $ligne)->getAlignment()->applyFromArray($centerArray);
            $sheet->getStyle('B' . $ligne . ':B' . $ligne3)->getFont()->setBold(2)->getColor()->setRGB('ff0000');
            $sheet->getStyle('C' . $ligne)->getFont()->getColor()->setRGB('000099');
            $sheet->getStyle('C' . $ligne)->getAlignment()->applyFromArray($centerArray);
            $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getAlignment()->applyFromArray($vcenterArray);

            $sheet->getStyle('D' . $ligne)->getAlignment()->applyFromArray($vcenterArray);


            $ligne = $ligne + 1;
            $sheet->setCellValue('D' . $ligne, 'Visite :');
            if ($equipe->getVisite() !== null) {
                $sheet->setCellValue('E' . $ligne, $equipe->getVisite()->getIntitule());
            }
            $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getAlignment()->applyFromArray($vcenterArray);


            $ligne = $ligne + 1;
            $sheet->mergeCells('D' . $ligne . ':E' . $ligne);
            if ($equipe->getCadeau() !== null) {
                $sheet->setCellValue('D' . $ligne, $equipe->getCadeau()->getRaccourci() . ' offert par ' . $equipe->getCadeau()->getFournisseur());
            }
            $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getAlignment()->applyFromArray($vcenterArray);

            $listeleves = '';
            $nbre = count($lesEleves[$lettre]);
            $eleves = $lesEleves[$lettre];

            for ($i = 0; $i <= $nbre - 1; $i++) {
                $eleve = $eleves[$i];
                $prenom = $eleve->getPrenom();
                $nom = strtoupper($eleve->getNom());
                if ($i < $nbre - 1) {
                    $listeleves .= $prenom . ' ' . $nom . ', ';
                } else {
                    $listeleves .= $prenom . ' ' . $nom;
                }
            }

            $sheet->setCellValue('B' . $ligne, $listeleves);
            $sheet->getStyle('B' . $ligne)->applyFromArray($borderArray);
            $sheet->getStyle('B' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('B' . $ligne)->getFont()->getColor()->setRGB('000099');

            $ligne = $ligne + 1;
        }

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(80);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(110);
        $spreadsheet->getActiveSheet()->getStyle('A1:F' . $nblignes)
            ->getAlignment()->setWrapText(true);


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="palmares.xls"');
        header('Cache-Control: max-age=0');

        $writer = new Xls($spreadsheet);
        $writer->save('php://output');


    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/excel_jury", name="secretariatjury_tableau_excel_palmares_jury")
     *
     * @throws Exception
     */
    public function tableau_excel_palmares_jury()
    {

        $nbreEquipes = 0;

        $tableau = $this->requestStack->getSession()->get('tableau');

        $lycee = $tableau[2];

        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        try {
            $nbreEquipes = $repositoryEquipes->createQueryBuilder('e')
                ->select('COUNT(e)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->getEquipesPalmaresJury();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("Palmarès de la 27ème édition - Février 2020")
            ->setSubject("Palmarès")
            ->setDescription("Palmarès avec Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");
        $spreadsheet->getActiveSheet()->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(6);
        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        $sheet = $spreadsheet->getActiveSheet();
        $borderArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];
        $vcenterArray = [
            'vertical' => Alignment::VERTICAL_CENTER,
            'textRotation' => 0,
            'wrapText' => TRUE
        ];
        $styleText = array('font' => array(
            'bold' => false,
            'size' => 14,
            'name' => 'Calibri',
        ),);
        $ligne = 1;
        foreach ($listEquipes as $equipe) {
            $sheet->getRowDimension($ligne)->setRowHeight(30);
            $lettre = $equipe->getEquipeinter()->getLettre();
            $sheet->mergeCells('B' . $ligne . ':C' . $ligne);
            $sheet->setCellValue('A' . $ligne, 'Nathalie');
            $sheet->setCellValue('B' . $ligne, 'Remise du ' . $equipe->getClassement() . ' Prix');
            $sheet->getStyle('A' . $ligne . ':D' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                ->applyFromArray($styleText);
            if ($equipe->getPrix() !== null) {
                // $voix=$equipe->getPrix()->getVoix();

                $sheet->getStyle('A' . $ligne . ':D' . $ligne)->applyFromArray($borderArray);

                $sheet->setCellValue('D' . $ligne, $equipe->getPrix()->getPrix());
                $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                    ->applyFromArray($styleText);
                $sheet->mergeCells('B' . $ligne . ':C' . $ligne);
                $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                    ->applyFromArray($styleText);
                $sheet->getRowDimension($ligne)->setRowHeight(30);
                if ($equipe->getPrix()->getIntervenant()) {
                    $ligne += 1;
                    $sheet->getRowDimension($ligne)->setRowHeight(30);
                    $sheet->mergeCells('B' . $ligne . ':D' . $ligne);
                    // $voix=$equipe->getPrix()->getVoix();
                    $sheet->setCellValue('A' . $ligne, 'Nathalie');
                    $sheet->setCellValue('B' . $ligne, 'Ce prix est remis par ' . $equipe->getPrix()->getIntervenant());
                    $sheet->mergeCells('B' . $ligne . ':D' . $ligne);
                    $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                        ->applyFromArray($styleText);
                    $sheet->getStyle('A' . $ligne . ':D' . $ligne)->applyFromArray($borderArray);
                }
            }


            $ligne += 1;
            $sheet->getRowDimension($ligne)->setRowHeight(30);

            $sheet->mergeCells('B' . $ligne . ':D' . $ligne);
            $remispar = 'Philippe'; //remplacer $remispar par $voix1 et $voix2

            if ($equipe->getPhrase() != null) {
                $sheet->setCellValue('A' . $ligne, $remispar);
                $sheet->setCellValue('B' . $ligne, $equipe->getPhrase()->getPhrase() . ' ' . $equipe->getPhrase()->getLiaison()->getLiaison() . ' ' . $equipe->getPhrase()->getPrix());
            }
            $sheet->getStyle('B' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                ->applyFromArray($styleText);
            $sheet->getStyle('A' . $ligne . ':D' . $ligne)->applyFromArray($borderArray);

            $ligne += 1;
            $remispar = 'Nathalie';
            $sheet->getRowDimension($ligne)->setRowHeight(40);
            if ($equipe->getVisite() !== null) {
                $sheet->setCellValue('A' . $ligne, $remispar);
                $sheet->setCellValue('B' . $ligne, 'Vous visiterez');
                $sheet->mergeCells('C' . $ligne . ':D' . $ligne);

                $sheet->setCellValue('C' . $ligne, $equipe->getVisite()->getIntitule());
            }
            $ligne = $this->getLigne($sheet, $ligne, $styleText, $borderArray);
            $sheet->getRowDimension($ligne)->setRowHeight(40);
            $sheet->setCellValue('A' . $ligne, $remispar);
            $sheet->setCellValue('B' . $ligne, 'Votre lycée recevra');
            $sheet->mergeCells('C' . $ligne . ':D' . $ligne);
            if ($equipe->getCadeau() !== null) {
                $sheet->setCellValue('C' . $ligne, $equipe->getCadeau()->getRaccourci() . ' offert par ' . $equipe->getCadeau()->getFournisseur());
            }
            $ligne = $this->getLigne($sheet, $ligne, $styleText, $borderArray);
            $remispar = 'Philippe';
            $lignep = $ligne + 1;
            $sheet->getRowDimension($ligne)->setRowHeight(20);
            $sheet->setCellValue('A' . $ligne, $remispar);

            $sheet->mergeCells('B' . $ligne . ':B' . $lignep);
            $sheet->setCellValue('B' . $ligne, 'J\'appelle')
                ->setCellValue('C' . $ligne, 'l\'equipe ' . $equipe->getEquipeinter()->getLettre())
                ->setCellValue('D' . $ligne, $equipe->getEquipeinter()->getTitreProjet());
            $sheet->getStyle('D' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('B' . $ligne)->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
            $aligne = $ligne;
            $ligne += 1;
            $sheet->getRowDimension($ligne)->setRowHeight(30);

            $sheet->setCellValue('C' . $ligne, 'AC. ' . $lycee[$lettre][0]->getAcademie())
                ->setCellValue('D' . $ligne, 'Lycee ' . $lycee[$lettre][0]->getNom() . "\n" . $lycee[$lettre][0]->getCommune());
            $sheet->getStyle('C' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('D' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $aligne . ':D' . $ligne)
                ->applyFromArray($styleText);
            $sheet->getStyle('A' . $aligne . ':D' . $lignep)->applyFromArray($borderArray);
            $ligne = $ligne + 2;
            $sheet->mergeCells('A' . $ligne . ':D' . $ligne);

            $spreadsheet->getActiveSheet()->getStyle('A' . $ligne)->getFont()->getColor()->setARGB(Color::COLOR_RED);
            $spreadsheet->getActiveSheet()->getStyle('A' . $ligne)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F3333333');

            $ligne = $ligne + 2;
        }
        $nblignes = 5 * $nbreEquipes + 2;
        $sheet->getColumnDimension('A')->setWidth(32);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(120);
        $sheet->getColumnDimension('E')->setWidth(80);

        $spreadsheet->getActiveSheet()->getStyle('A1:F' . $nblignes)
            ->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        $spreadsheet->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
        $spreadsheet->getActiveSheet()->getPageSetup()->setVerticalCentered(true);
        $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('RPage &P sur &N');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="proclamation.xls"');
        header('Cache-Control: max-age=0');
        $writer = new Xls($spreadsheet);
        $writer->save('php://output');
    }

    /**
     * @param Worksheet $sheet
     * @param $ligne
     * @param array $styleText
     * @param array $borderArray
     * @return int
     */
    public function getLigne(Worksheet $sheet, $ligne, array $styleText, array $borderArray): int
    {
        $sheet->getStyle('C' . $ligne . ':D' . $ligne)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A' . $ligne . ':D' . $ligne)
            ->applyFromArray($styleText);
        $sheet->getStyle('A' . $ligne . ':D' . $ligne)->applyFromArray($borderArray);

        $ligne += 1;
        return $ligne;
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/preparation_tableau_excel_palmares_jury", name = "secretariatjury_preparation_tableau_excel_palmares_jury")
     */
    public function preparation_tableau_excel_palmares_jury(Request $request): RedirectResponse|Response
    { //À quoi ça sert ? Qui l'appelle ? Semble servir à remplir voix et intervenant, équipe par équipe

        $em = $this->doctrine->getManager();
        $formtab = [];
        $listEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipes::class)
            ->createQueryBuilder('e')
            ->orderBy('e.classement', 'DESC')
            ->leftJoin('e.infoequipe', 'i')
            ->addOrderBy('i.lyceeAcademie', 'ASC')
            ->addOrderBy('i.lyceeLocalite', 'ASC')
            ->addOrderBy('i.nomLycee', 'ASC')
            ->getQuery()->getResult();

        $i = 0;
        foreach ($listEquipes as $equipe) {
            $prix = $equipe->getPrix();
            $formBuilder[$i] = $this->get('form.factory')->createNamedBuilder('Form' . $i, PrixExcelType::class, $prix, ['voix' => $prix->getVoix(), 'intervenant' => $prix->getIntervenant()]);

            $form[$i] = $formBuilder[$i]->getForm();

            $formtab[$i] = $form[$i]->createView();

            if ($request->isMethod('POST') && $request->request->has('Form' . $i)) { //$id=$form[$i]->get('id')->getData();


                $prix->setVoix($request->get('Form' . $i)['voix']);
                $prix->setIntervenant($request->get('Form' . $i)['intervenant']);


                $em->persist($prix);
                $em->flush();
                return $this->redirectToRoute('secretariatjury_preparation_tableau_excel_palmares_jury');
            }
            $i++;
        }
        $content = $this
            ->renderView('secretariatjury\preparation_palmares.html.twig', array(

                    'listequipes' => $listEquipes, 'formtab' => $formtab
                )
            );
        return new Response($content);

    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/excel_prix", name="secretariatjury_excel_prix")
     *
     */
    public function excel_prix(Request $request): RedirectResponse|Response
    {  //fonction appelée à partir de l'admin page les prix

        $defaultData = ['message' => 'Charger le fichier excel pour le palmares'];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();

            $em = $this->doctrine->getManager();


            for ($row = 2; $row <= $highestRow; ++$row) {
                $prix = new Prix();
                $niveau = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $prix->setNiveau($niveau);
                $prix_nom = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $prix->setPrix($prix_nom);
                $attribue = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $prix->setAttribue($attribue);
                $voix = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $prix->setVoix($voix);
                $intervenant = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $prix->setIntervenant($intervenant);
                $remisPar = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $prix->setRemisPar($remisPar);


                $em->persist($prix);
                $em->flush();

            }

            return $this->redirectToRoute('dashboard');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('titre' => 'Remplissage des prix', 'form' => $form->createView(),));
        return new Response($content);


    }


}