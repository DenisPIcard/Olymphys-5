<?php

namespace App\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;

use App\Form\NotesType;
use App\Form\PhrasesType;
use App\Form\EquipesType;
use App\Form\JuresType;
use App\Form\CadeauxType;
use App\Form\ClassementType;
use App\Form\PrixType;
use App\Form\EditionType;
use App\Form\MemoiresType;
use App\Form\MemoiresinterType;
use App\Form\ConfirmType;
use App\Form\PrixExcelType;

use App\Entity\Equipes;
use App\Entity\Eleves;

use App\Entity\Totalequipes;
use App\Entity\Jures;
use App\Entity\Notes;
use App\Entity\Pamares;
use App\Entity\Visites;
use App\Entity\Phrases;
use App\Entity\Classement;
use App\Entity\Prix;
use App\Entity\Cadeaux;
use App\Entity\Liaison;
use App\Entity\Memoires;
use App\Entity\Memoiresinter;
use App\Entity\Equipesadmin;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextaeraType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Howtomakeaturn\PDFInfo\PDFInfo;
use Orbitale\Component\ImageMagick\Command;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SwiftmailerBundle\Swiftmailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\RichText\Run;

class SecretariatjuryController extends AbstractController
{
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }


    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/accueil", name="secretariatjury_accueil")
     *
     */
    public function accueil(Request $request)
    {


        $em = $this->getDoctrine()->getManager();
        $edition = $this->session->get('edition');

        $edition = $em->merge($edition);
        $repositoryEquipesadmin = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipesadmin');
        $repositoryEleves = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Elevesinter');
        $repositoryUser = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:User');
        $repositoryRne = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Rne');
        $listEquipes = $repositoryEquipesadmin->createQueryBuilder('e')
            ->select('e')
            ->where('e.selectionnee = TRUE')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->andWhere('e.lettre IS NOT NULL')
            ->orderBy('e.lettre', 'ASC')
            ->getQuery()
            ->getResult();


        foreach ($listEquipes as $equipe) {
            $lettre = $equipe->getLettre();
            $lesEleves[$lettre] = $repositoryEleves->findByEquipe(['equipe' => $equipe]);
            $rne = $equipe->getRne();
            $lycee[$lettre] = $repositoryRne->findByRne($rne);

        }

        $tableau = [$listEquipes, $lesEleves, $lycee];

        $this->session->set('tableau', $tableau);


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
    public function accueilJury(Request $request)
    {

        $tableau = $this->session->get('tableau');
        $listEquipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];
        $repositoryUser = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:User');
        foreach ($listEquipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findById($idprof1);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findById($idprof2);
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
     * @Route("/secretariatjury/edition_maj", name="secretariatjury_edition_maj")
     *
     */
    public function edition_maj(Request $request)
    {
        $repositoryEdition = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Edition');
        $ed = $repositoryEdition->findOneByEd('ed');
        $em = $this->getDoctrine()->getManager();
        dd($ed);
        $form = $this->createForm(EditionType::class, $ed);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($ed);
            $em->flush();
            return $this->redirectToroute('secretariatjury_accueil');
        }
        $content = $this->renderView('secretariatjury\edition_maj.html.twig', array('form' => $form->createView(),));
        return new Response($content);
    }

    /*
         private function getChoices($professeur){
              $repositoryTotalequipes= $this->getDoctrine()
                                            ->getManager()
                                            ->getRepository('App:Totalequipes');
             $i=0;
             $lettre_equipes[$i]='';
             $qb =$repositoryTotalequipes->createQueryBuilder('t')
                                         ->where('t.nomProf1=:professeur')
                                         ->setParameter('professeur', $professeur);
              $equipes_prof=$qb->getQuery()->getResult();
             foreach($equipes_prof as $equipe){
                  $lettre_equipes[$i]=$equipe->getLettreEquipe();

             return  $lettre_equipes;
             }
         }
      */


    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/vueglobale", name="secretariatjury_vueglobale")
     *
     */
    public function vueglobale(Request $request)
    {
        $repositoryNotes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Notes');

        $repositoryJures = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Jures');
        $listJures = $repositoryJures->findAll();

        $repositoryEquipes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $listEquipes = $repositoryEquipes->createQueryBuilder('e')
            ->addOrderBy('e.lettre', 'ASC')
            ->getQuery()->getResult();

        $nbre_equipes = 0;
        foreach ($listEquipes as $equipe) {
            $nbre_equipes = $nbre_equipes + 1;
            $id_equipe = $equipe->getId();
            $lettre = $equipe->getLettre();

            $nbre_jures = 0;
            foreach ($listJures as $jure) {
                $id_jure = $jure->getId();
                $nbre_jures = $nbre_jures + 1;

                $method = 'get' . ucfirst($lettre);
                $statut = $jure->$method();

                if (is_null($statut)) {
                    $progression[$nbre_equipes][$nbre_jures] = 'ras';

                } elseif ($statut == 1) {
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? 'zero' : $note->getSousTotal();
                } else {
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? 'zero' : $note->getPoints();
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
    public function classement(Request $request)
    {

        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        $repositoryNotes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Notes');
        $repositoryClassement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Classement');
        $em = $this->getDoctrine()->getManager();
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
                    $points_ecrit = $points_ecrit + $note->getEcrit() * 5;
                }


                $moyenne_oral = $points / $nbre_notes;
                $moyenne_ecrit = ($nbre_notes_ecrit) ? $points_ecrit / $nbre_notes_ecrit : 0;

                $total = $moyenne_oral + $moyenne_ecrit;
                $equipe->setTotal($total);
                $em->persist($equipe);
                $em->flush();
            }
        }


        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        $nbre_equipes = $qb->getQuery()->getSingleScalarResult();

        $classement = $repositoryEquipes->classement(0, 0, $nbre_equipes);

        $rang = 0;

        foreach ($classement as $equipe) {
            $rang = $rang + 1;
            $equipe->setRang($rang);
            $em->persist($equipe);
        }
        $em->flush();
        $liste_equipes = $qb->select('e')->addOrderBy('e.rang', 'ASC')->getQuery()->getResult();

        $nbr1 = $repositoryClassement->findOneByNiveau(['niveau' => '1er'])->getNbreprix();
        $nbr2 = $repositoryClassement->findOneByNiveau(['niveau' => '2ème'])->getNbreprix();
        $nbr3 = $repositoryClassement->findOneByNiveau(['niveau' => '3ème'])->getNbreprix();
        foreach ($liste_equipes as $equipe) {
            if ($equipe->getRang() <= $nbr1) {
                $equipe->setClassement('1er');
                $em->persist($equipe);
            }
            if (($equipe->getRang() > $nbr1) and ($equipe->getRang() <= $nbr1 + $nbr2)) {
                $equipe->setClassement('2ème');
                $em->persist($equipe);
            }
            if ($equipe->getRang() > $nbr1 + $nbr2) {
                $equipe->setClassement('3ème');
                $em->persist($equipe);
            }
            $em->flush();
        }


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
    public function lesprix(Request $request)
    {
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');
        $ListPremPrix = $repositoryPrix->findByClassement('1er');

        $ListDeuxPrix = $repositoryPrix->findByClassement('2ème');

        $ListTroisPrix = $repositoryPrix->findByClassement('3ème');

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
    public function modifier_prix(Request $request, $id_prix)
    {
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');
        $repositoryClassement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Classement');
        $prix = $repositoryPrix->find($id_prix);
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PrixType::class, $prix);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($prix);
            $em->flush();
            $classement = $repositoryClassement->findOneByNiveau('1er');
            $nbrePremPrix = $repositoryPrix->getNbrePrix('1er');
            $classement->setNbreprix($nbrePremPrix);
            $em->persist($classement);
            $classement = $repositoryClassement->findOneByNiveau('2ème');
            $nbreDeuxPrix = $repositoryPrix->getNbrePrix('2ème');
            $classement->setNbreprix($nbreDeuxPrix);
            $em->persist($classement);
            $classement = $repositoryClassement->findOneByNiveau('3ème');
            $nbreTroisPrix = $repositoryPrix->getNbrePrix('3ème');
            $classement->setNbreprix($nbreTroisPrix);
            $em->persist($classement);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Modifications bien enregistrées');
            return $this->redirectToroute('secretariatjury_lesprix');
        }
        $content = $this->renderView('secretariatjury/modifier_prix.html.twig',
            array(
                'prix' => $prix,
                'form' => $form->createView(),
            ));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/palmares", name="secretariatjury_palmares")
     *
     */
    public function palmares(Request $request)
    {

        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        $nbre_equipes = $qb->getQuery()->getSingleScalarResult();

        $repositoryClassement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Classement');


        $NbrePremierPrix = $repositoryClassement
            ->findOneByNiveau('1er')
            ->getNbreprix();

        $NbreDeuxPrix = $repositoryClassement
            ->findOneByNiveau('2ème')
            ->getNbreprix();

        $NbreTroisPrix = $repositoryClassement
            ->findOneByNiveau('3ème')
            ->getNbreprix();

        $ListPremPrix = $repositoryEquipes->classement(1, 0, $NbrePremierPrix);

        $offset = $NbrePremierPrix;
        $ListDeuxPrix = $repositoryEquipes->classement(2, $offset, $NbreDeuxPrix);

        $offset = $offset + $NbreDeuxPrix;
        $ListTroisPrix = $repositoryEquipes->classement(3, $offset, $NbreTroisPrix);


        $content = $this->renderView('secretariatjury/palmares.html.twig',
            array('ListPremPrix' => $ListPremPrix,
                'ListDeuxPrix' => $ListDeuxPrix,
                'ListTroisPrix' => $ListTroisPrix,
                'NbrePremierPrix' => $NbrePremierPrix,
                'NbreDeuxPrix' => $NbreDeuxPrix,
                'NbreTroisPrix' => $NbreTroisPrix)
        );
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/modifier_rang/{id_equipe}", name="secretariatjury_modifier_rang", requirements={"id_equipe"="\d{1}|\d{2}"}))
     *
     */
    public function modifier_rang(Request $request, $id_equipe)
    {
        $repositoryEquipes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $equipe = $repositoryEquipes->find($id_equipe);
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(EquipesType::class, $equipe,
            array(
                'Modifier_Rang' => true,
                'Attrib_Phrases' => false,
                'Attrib_Cadeaux' => false,
                'Deja_Attrib' => false,)
        );
        $ancien_rang = $equipe->getRang();
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $nouveau_rang = $equipe->getRang();
            $max = 0;
            $mod = 0;
            if ($nouveau_rang < $ancien_rang) {
                $deb = $nouveau_rang - 1;
                $max = $ancien_rang - $nouveau_rang;
                $mod = 1;
            } elseif ($ancien_rang < $nouveau_rang) {
                $deb = $ancien_rang;
                $max = $nouveau_rang - $deb;
                $mod = -1;
            } elseif ($ancien_rang == $nouveau_rang) {
                $deb = $ancien_rang;
                $max = 0;
                $mod = 0;
            }

            $qb = $repositoryEquipes->createQueryBuilder('e');
            $qb->orderBy('e.rang', 'ASC')
                ->setFirstResult($deb)
                ->setMaxResults($max);
            $list = $qb->getQuery()->getResult();

            foreach ($list as $eq) {
                $rang = $eq->getRang();
                $eq->setRang($rang + $mod);
            }
            $em->persist($equipe);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Modifications bien enregistrées');
            return $this->redirectToroute('secretariatjury_palmares_ajuste');

        }
        $content = $this->renderView('secretariatjury/modifier_rang.html.twig',
            array(
                'equipe' => $equipe,
                'form' => $form->createView(),
            ));
        return new Response($content);
    }


    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/palmares_ajuste", name="secretariatjury_palmares_ajuste")
     *
     */
    public function palmares_ajuste(Request $request)
    {
        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        $nbre_equipes = $qb->getQuery()->getSingleScalarResult();

        $repositoryClassement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Classement');


        $NbrePremierPrix = $repositoryClassement
            ->findOneByNiveau('1er')
            ->getNbreprix();

        $NbreDeuxPrix = $repositoryClassement
            ->findOneByNiveau('2ème')
            ->getNbreprix();

        $NbreTroisPrix = $repositoryClassement
            ->findOneByNiveau('3ème')
            ->getNbreprix();

        $ListPremPrix = $repositoryEquipes->palmares(1, 0, $NbrePremierPrix);

        $offset = $NbrePremierPrix;
        $ListDeuxPrix = $repositoryEquipes->palmares(2, $offset, $NbreDeuxPrix);

        $offset = $offset + $NbreDeuxPrix;
        $ListTroisPrix = $repositoryEquipes->palmares(3, $offset, $NbreTroisPrix);

        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->orderBy('e.rang', 'ASC')
            ->setFirstResult($NbrePremierPrix + $NbreDeuxPrix)
            ->setMaxResults($NbreTroisPrix);
        $ListTroisPrix = $qb->getQuery()->getResult();

        $content = $this->renderView('secretariatjury/palmares_ajuste.html.twig',
            array('ListPremPrix' => $ListPremPrix,
                'ListDeuxPrix' => $ListDeuxPrix,
                'ListTroisPrix' => $ListTroisPrix,
                'NbrePremierPrix' => $NbrePremierPrix,
                'NbreDeuxPrix' => $NbreDeuxPrix,
                'NbreTroisPrix' => $NbreTroisPrix)
        );
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/palmares_definitif", name="secretariatjury_palmares_definitif")
     *
     */
    public function palmares_definitif(Request $request)
    {
        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $em = $this->getDoctrine()->getManager();

        $repositoryClassement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Classement');

        $NbrePremierPrix = $repositoryClassement
            ->findOneByNiveau('1er')
            ->getNbreprix();

        $NbreDeuxPrix = $repositoryClassement
            ->findOneByNiveau('2ème')
            ->getNbreprix();

        $NbreTroisPrix = $repositoryClassement
            ->findOneByNiveau('3ème')
            ->getNbreprix();

        $ListPremPrix = $repositoryEquipes->palmares(1, 0, $NbrePremierPrix);
        $offset = $NbrePremierPrix;
        $ListDeuxPrix = $repositoryEquipes->palmares(2, $offset, $NbreDeuxPrix);
        $offset = $offset + $NbreDeuxPrix;
        $ListTroisPrix = $repositoryEquipes->palmares(3, $offset, $NbreTroisPrix);

        $rang = 0;

        foreach ($ListPremPrix as $equipe) {
            $niveau = '1er';
            $equipe->setClassement($niveau);
            $rang = $rang + 1;
            $equipe->setRang($rang);
            $em->persist($equipe);
        }

        foreach ($ListDeuxPrix as $equipe) {
            $niveau = '2ème';
            $equipe->setClassement($niveau);
            $rang = $rang + 1;
            $equipe->setRang($rang);
            $em->persist($equipe);
        }
        foreach ($ListTroisPrix as $equipe) {
            $niveau = '3ème';
            $equipe->setClassement($niveau);
            $rang = $rang + 1;
            $equipe->setRang($rang);
            $em->persist($equipe);

        }
        $em->flush();
        $content = $this->renderView('secretariatjury/palmares_definitif.html.twig',
            array('ListPremPrix' => $ListPremPrix,
                'ListDeuxPrix' => $ListDeuxPrix,
                'ListTroisPrix' => $ListTroisPrix,
                'NbrePremierPrix' => $NbrePremierPrix,
                'NbreDeuxPrix' => $NbreDeuxPrix,
                'NbreTroisPrix' => $NbreTroisPrix)
        );
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/mise_a_zero", name="secretariatjury_mise_a_zero")
     *
     */
    public function RaZ(Request $request)
    {
        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $repositoryPalmares = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Palmares');
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');
        $repositoryCadeaux = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Cadeaux');
        $repositoryVisites = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Visites');
        $ListPrix = $repositoryPrix->findAll();
        $prix = $repositoryPalmares->findOneByCategorie('prix');
        $em = $this->getDoctrine()->getManager();
        $ListeEquipes = $repositoryEquipes->findAll();
        $listecadeaux = $repositoryCadeaux->findAll();
        $listevisites = $repositoryVisites->findAll();
        foreach ($ListeEquipes as $equipe) {
            $equipe->setPrix(null);
            $equipe->setCadeau(null);
            $equipe->setVisite(null);
            $em->persist($equipe);
        }
        foreach (range('A', 'Z') as $i) {
            $method = 'set' . ucfirst($i);
            if (method_exists($prix, $method)) {
                $prix = $prix->$method(null);
                $em->persist($prix);

            }
        }
        foreach ($ListPrix as $Prix) {
            $Prix->setAttribue(FALSE);
            $em->persist($Prix);
        }
        foreach ($listecadeaux as $cadeau) {
            $cadeau->setAttribue(FALSE);
            $em->persist($cadeau);
        }
        foreach ($listevisites as $visite) {
            $visite->setAttribue(FALSE);
            $em->persist($visite);
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
        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $repositoryClassement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Classement');
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');
        $repositoryPalmares = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Palmares');
        $ListEquipes = $repositoryEquipes->findByClassement($niveau_court);

        $NbrePrix = $repositoryClassement->findOneByNiveau($niveau_court)
            ->getNbreprix();
        /*$qb = $repositoryPrix->createQueryBuilder('p')
                             ->where('p.classement=:niveau')
                             ->setParameter('niveau', $niveau_court);
        $listPrix=$repositoryPrix->findOneByClassement($niveau_court)->getPrix();*/


        $prix = $repositoryPalmares->findOneByCategorie('prix');
        $i = 0;
        foreach ($ListEquipes as $equipe) {
            $qb2[$i] = $repositoryPrix->createQueryBuilder('p')
                ->where('p.classement = :niveau')
                ->setParameter('niveau', $niveau_court);
            $attribue = 0;
            $Prix_eq = $equipe->getPrix();
            $intitule_prix = '';
            if ($Prix_eq != null) {
                $intitule_prix = $Prix_eq->getPrix();
                $qb2[$i]->andwhere('p.id = :prix_sel')
                    ->setParameter('prix_sel', $Prix_eq->getId());
            }
            if (!$Prix_eq) {
                $qb2[$i]->andwhere('p.attribue = :attribue')
                    ->setParameter('attribue', $attribue);
            }

            $formBuilder[$i] = $this->get('form.factory')->createBuilder(FormType::class, $prix);
            $lettre = strtoupper($equipe->getLettre());
            $titre = $equipe->getTitreProjet();

            //$titre_form[$i]=$lettre." : ".$titre.".  Prix :  ".$intitule_prix;
            $formBuilder[$i]->add($lettre, EntityType::class, [
                    'class' => 'App:Prix',
                    'query_builder' => $qb2[$i],
                    'choice_label' => 'getPrix',
                    'multiple' => false,
                    'label' => $lettre . " : " . $titre . ".  Prix :  " . $intitule_prix]
            );
            $formBuilder[$i]->add('Enregistrer', SubmitType::class);
            $formBuilder[$i]->add('Effacer', SubmitType::class);
            $form[$i] = $formBuilder[$i]->getForm();
            $formtab[$i] = $form[$i]->createView();
            if ($request->isMethod('POST') && $form[$i]->handleRequest($request)->isValid()) {

                $em = $this->getDoctrine()->getManager();
                foreach (range('A', 'Z') as $lettre_equipe) {
                    if (isset($form[$i][$lettre_equipe])) {
                        $equipe = $repositoryEquipes->findOneByLettre($lettre_equipe);
                        if ($form[$i]->get('Enregistrer')->isClicked()) {
                            $prix = $form[$i]->get($lettre)->getData();;
                            $equipe->setPrix($prix);
                            $em->persist($equipe);
                            $prix->setAttribue(1);
                            $em->persist($prix);
                            $em->flush();
                            $request->getSession()->getFlashBag()->add('notice', 'Prix bien enregistrés');
                            return $this->redirectToroute('secretariatjury_attrib_prix', array('niveau' => $niveau));

                        }
                        if ($form[$i]->get('Effacer')->isClicked()) {

                            $prix = $equipe->getPrix();
                            $prix->setAttribue(0);
                            $em->persist($prix);
                            $equipe->setPrix(null);
                            $em->persist($equipe);
                            $em->flush();
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
    public function edition_prix(Request $request)
    {
        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
            ->createQueryBuilder('e')
            ->addOrderBy('e.lettre', 'ASC')
            ->getQuery()->getResult();

        $content = $this->renderView('secretariatjury/edition_prix.html.twig', array('listEquipes' => $listEquipes));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/edition_visites", name="secretariatjury_edition_visites")
     *
     */
    public function edition_visites(Request $request)
    {
        //$user=$this->getUser();
        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
            ->getEquipesVisites();
        $content = $this->renderView('secretariatjury/edition_visites.html.twig', array('listEquipes' => $listEquipes));
        return new Response($content);
    }


    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/attrib_cadeaux/{id_equipe}", name="secretariatjury_attrib_cadeaux",  requirements={"id_equipe"="\d{1}|\d{2}"}))
     *
     */
    public function attrib_cadeaux(Request $request, $id_equipe)
    {
        $repositoryEquipes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        $equipe = $repositoryEquipes->find($id_equipe);
        $repositoryCadeaux = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Cadeaux');
        $cadeau = $equipe->getCadeau();

        if (is_null($cadeau)) {
            $flag = 0;
            $form = $this->createForm(EquipesType::class, $equipe,
                array(
                    'Attrib_Phrases' => false,
                    'Attrib_Cadeaux' => true,
                    'Deja_Attrib' => false,
                ));
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($equipe);
                $cadeau = $equipe->getCadeau();
                $cadeau->setAttribue(1);
                $em->persist($cadeau);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Notes bien enregistrées');
                return $this->redirectToroute('secretariatjury_attrib_cadeaux', array('id_equipe' => $id_equipe));
            }
            $content = $this->renderView('secretariatjury/attrib_cadeaux.html.twig',
                array(
                    'equipe' => $equipe,
                    'form' => $form->createView(),
                    'attribue' => $flag,
                ));

            return new Response($content);
        } else {
            $flag = 1;
            $em = $this->getDoctrine()->getManager();

            $form = $this->createForm(EquipesType::class, $equipe,
                array(
                    'Attrib_Phrases' => false,
                    'Attrib_Cadeaux' => true,
                    'Deja_Attrib' => true,
                ));

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->persist($cadeau);

                if ($cadeau->getAttribue()) {
                    $em->persist($equipe);

                } else {
                    $equipe->setCadeau(NULL);
                    $em->persist($equipe);
                }
                $em->flush();
                $request->getSession()->getFlashBag()->add('notice', 'Notes bien enregistrées');
                return $this->redirectToroute('secretariatjury_attrib_cadeaux', array('id_equipe' => $id_equipe));
            }

            $content = $this->renderView('secretariatjury/attrib_cadeaux.html.twig',
                array(
                    'equipe' => $equipe,
                    'form' => $form->createView(),
                    'attribue' => $flag,
                ));

            return new Response($content);
        }

    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/lescadeaux/{compteur}", name="secretariatjury_lescadeaux", requirements={"compteur"="\d{1}|\d{2}"}))
     *
     */
    public function lescadeaux(Request $request, $compteur = 1)
    {
        $repositoryCadeaux = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Cadeaux');
        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');
        $nbreEquipes = $repositoryEquipes->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
        $listEquipesCadeaux = $repositoryEquipes->getEquipesCadeaux();
        $listEquipesPrix = $repositoryEquipes->getEquipesPrix();
        $equipe = $repositoryEquipes->findOneByRang($compteur);
        if (is_null($equipe)) {
            $content = $this->renderView('secretariatjury/edition_cadeaux.html.twig',
                array(
                    'listEquipesCadeaux' => $listEquipesCadeaux,
                    'listEquipesPrix' => $listEquipesPrix,
                    'nbreEquipes' => $nbreEquipes,
                    'compteur' => $compteur,));
            return new Response($content);
        }
        $id_equipe = $equipe->getId();
        $cadeau = $equipe->getCadeau();
        $em = $this->getDoctrine()->getManager();
        if (is_null($cadeau)) {
            $flag = 0;
            $form = $this->createForm(EquipesType::class, $equipe,
                array(
                    'Attrib_Phrases' => false,
                    'Attrib_Cadeaux' => true,
                    'Deja_Attrib' => false,
                ));
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($equipe);
                $cadeau = $equipe->getCadeau();
                $cadeau->setAttribue(1);
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
        } else {
            $flag = 1;
            $em = $this->getDoctrine()->getManager();
            $form = $this->createForm(EquipesType::class, $equipe,
                array(
                    'Attrib_Phrases' => false,
                    'Attrib_Cadeaux' => true,
                    'Deja_Attrib' => true,
                ));
            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $em->persist($cadeau);
                if ($cadeau->getAttribue()) {
                    $em->persist($equipe);
                } else {
                    $equipe->setCadeau(NULL);
                    $em->persist($equipe);
                }
                $em->flush();
                $request->getSession()->getFlashBag()->add('notice', 'cadeaux bien enregistrés');

                if ($compteur < $nbreEquipes) {
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
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/edition_cadeaux", name="secretariatjury_edition_cadeaux")
     *
     */
    public function edition_cadeaux(Request $request)
    {
        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
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
    public function edition_phrases(Request $request)
    {
        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
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
    public function tableau_palmares_complet(Request $request)
    {
        $session = new Session();
        $tableau = $session->get('tableau');

        $equipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];

        $repositoryUser = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:User');

        foreach ($equipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findById($idprof1);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findById($idprof2);
        }

        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
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
     */
    public function tableau_excel_palmares_site(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $edition = $this->session->get('edition');

        $edition = $em->merge($edition);

        $tableau = $this->session->get('tableau');

        $equipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];

        $repositoryUser = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:User');
        foreach ($equipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findById($idprof1);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findById($idprof2);

        }

        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
            ->getEquipesPalmares();

        $repositoryEquipes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        $nbreEquipes = $repositoryEquipes
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
        $repositoryEdition = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Edition');

        $date = $edition->getConcourscn();
        $result = $date->format('d/m/Y');
        $ed = $edition->getEd();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("Palmarès de la " . $ed . "ème édition - " . $result)
            ->setSubject("Palmarès")
            ->setDescription("Palmarès avec Office 2005 XLSX, generated using PHP classes.")
            ->setKeywords("office 2005 openxml php")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);


        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(6);

        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);

        $borderArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];
        $centerArray = [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'textRotation' => 0,
            'wrapText' => TRUE
        ];
        $vcenterArray = [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
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
            $lettre = $equipe->getLettre();

            $ligne4 = $ligne + 3;
            $sheet->mergeCells('A' . $ligne . ':A' . $ligne);
            if ($lycee[$lettre][0] != null) {
                $sheet->setCellValue('A' . $ligne, strtoupper($lycee[$lettre][0]->getAcademie()))
                    ->setCellValue('B' . $ligne, 'Lycée ' . $lycee[$lettre][0]->getNom() . " - " . $lycee[$lettre][0]->getCommune());
            }
            $sheet->setCellValue('C' . $ligne, $prof1[$lettre][0]->getPrenom() . " " . strtoupper($prof1[$lettre][0]->getNom()))
                ->setCellValue('D' . $ligne, $equipe->getClassement() . ' ' . 'prix');
            if ($equipe->getPhrases() !== null) {
                $sheet->setCellValue('E' . $ligne, $equipe->getPhrases()->getPhrase() . ' ' . $equipe->getLiaison()->getLiaison() . ' ' . $equipe->getPhrases()->getPrix());
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

            if ($equipe->getClassement() == '1er') {
                $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('ffccff');
            } elseif ($equipe->getClassement() == '2ème') {
                $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('99ffcc');
            } else {
                $sheet->getStyle('D' . $ligne . ':E' . $ligne)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('ccff99');
            }

            $ligne = $ligne + 1;

            $ligne3 = $ligne + 1;
            $sheet->mergeCells('B' . $ligne . ':B' . $ligne3);
            $sheet->setCellValue('B' . $ligne, $equipe->getTitreProjet());
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


            if ($equipe->getClassement() == '1er') {
                $sheet->setCellValue('D' . $ligne, PRIX::PREMIER . '€');
            } elseif ($equipe->getClassement() == '2ème') {
                $sheet->setCellValue('D' . $ligne, PRIX::DEUXIEME . '€');
            } else {
                $sheet->setCellValue('D' . $ligne, PRIX::TROISIEME . '€');
            }
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
                $sheet->setCellValue('D' . $ligne, $equipe->getCadeau()->getContenu() . ' offert par ' . $equipe->getCadeau()->getFournisseur());
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

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save('php://output');


    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/excel_jury", name="secretariatjury_tableau_excel_palmares_jury")
     *
     */
    public function tableau_excel_palmares_jury(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $edition = $this->session->get('edition');

        $edition = $em->merge($edition);

        //$lesEleves=$tableau[1];
        $tableau = $this->session->get('tableau');
        $lycee = $tableau[2];

        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $nbreEquipes = $repositoryEquipes->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()
            ->getSingleScalarResult();
        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
            ->getEquipesPalmaresJury();
        $repositoryEleves = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Eleves');
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
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(6);
        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        $sheet = $spreadsheet->getActiveSheet();
        $borderArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];
        $vcenterArray = [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'textRotation' => 0,
            'wrapText' => TRUE
        ];
        $styleText = array('font' => array(
            'bold' => false,
            'size' => 14,
            'name' => 'Calibri',
        ),);
        $styleTitre = array('font' => array(
            'bold' => true,
            'size' => 14,
            'name' => 'Calibri',
        ),);
        $ligne = 1;
        foreach ($listEquipes as $equipe) {
            $sheet->getRowDimension($ligne)->setRowHeight(30);
            $lettre = $equipe->getLettre();
            $sheet->mergeCells('B' . $ligne . ':C' . $ligne);
            $sheet->setCellValue('B' . $ligne, 'Remise du ' . $equipe->getClassement() . ' Prix');

            $sheet->getStyle('A' . $ligne . ':E' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $ligne . ':E' . $ligne)
                ->applyFromArray($styleText);
            if ($equipe->getPrix() !== null) {
                $voix = $equipe->getPrix()->getVoix();

                if ($voix) {
                    $sheet->setCellValue('A' . $ligne, $voix);
                } else {
                    $sheet->setCellValue('A' . $ligne, 'Voix Off');
                }
                $sheet->setCellValue('D' . $ligne, 'par ' . $equipe->getPrix()->getRemisPar());

                $sheet->getStyle('A' . $ligne . ':E' . $ligne)->applyFromArray($borderArray);

                $sheet->setCellValue('D' . $ligne, $equipe->getPrix()->getPrix());
                $sheet->getStyle('A' . $ligne . ':E' . $ligne)
                    ->applyFromArray($styleText);
                $ligne += 1;
                $sheet->getRowDimension($ligne)->setRowHeight(30);
                $sheet->mergeCells('B' . $ligne . ':C' . $ligne);
                $voix = $equipe->getPrix()->getVoix();

                if ($voix) {
                    $sheet->setCellValue('A' . $ligne, $voix);
                } else {
                    $sheet->setCellValue('A' . $ligne, 'Voix Off');
                }
                $sheet->setCellValue('B' . $ligne, 'Ce prix sera remis par : ');
                $sheet->setCellValue('D' . $ligne, $equipe->getPrix()->getRemisPar());
                $sheet->getStyle('A' . $ligne . ':E' . $ligne)
                    ->applyFromArray($styleText);

                if ($equipe->getPrix()->getIntervenant()) {
                    $ligne += 1;
                    $sheet->mergeCells('B' . $ligne . ':D' . $ligne);
                    $voix = $equipe->getPrix()->getVoix();

                    if ($voix) {
                        $sheet->setCellValue('A' . $ligne, $voix);
                    } else {
                        $sheet->setCellValue('A' . $ligne, 'Camille');
                    }
                    $sheet->setCellValue('B' . $ligne, $equipe->getPrix()->getIntervenant());
                    $sheet->mergeCells('B' . $ligne . ':D' . $ligne);
                    $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                        ->applyFromArray($styleText);
                    $sheet->getStyle('A' . $ligne . ':E' . $ligne)->applyFromArray($borderArray);
                }
            }


            $ligne += 1;
            $sheet->getRowDimension($ligne)->setRowHeight(30);

            $sheet->mergeCells('B' . $ligne . ':D' . $ligne);
            $voix = $equipe->getPrix()->getVoix();

            if ($voix) {
                $sheet->setCellValue('A' . $ligne, $voix);
            } else {
                $sheet->setCellValue('A' . $ligne, 'Camille');
            }
            if ($equipe->getPhrases() != null) {
                $sheet->setCellValue('B' . $ligne, $equipe->getPhrases()->getPhrase() . ' ' . $equipe->getLiaison()->getLiaison() . ' ' . $equipe->getPhrases()->getPrix());
            }
            $sheet->getStyle('B' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                ->applyFromArray($styleText);
            $sheet->getStyle('A' . $ligne . ':E' . $ligne)->applyFromArray($borderArray);

            $ligne += 1;
            $sheet->getRowDimension($ligne)->setRowHeight(40);
            $sheet->setCellValue('A' . $ligne, 'Camille');
            $sheet->setCellValue('B' . $ligne, 'Vous êtes invités à visiter');
            $sheet->mergeCells('C' . $ligne . ':D' . $ligne);
            if ($equipe->getVisite() !== null) {
                $sheet->setCellValue('C' . $ligne, $equipe->getVisite()->getIntitule());

                $sheet->getStyle('C' . $ligne . ':D' . $ligne)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                    ->applyFromArray($styleText);
                $sheet->getStyle('A' . $ligne . ':E' . $ligne)->applyFromArray($borderArray);

                $ligne += 1;
                $sheet->getRowDimension($ligne)->setRowHeight(40);
                $sheet->setCellValue('A' . $ligne, 'Camille');
                $sheet->setCellValue('B' . $ligne, 'Votre lycée recevra');
                $sheet->mergeCells('C' . $ligne . ':D' . $ligne);
            }
            if ($equipe->getCadeau() !== null) {
                $sheet->setCellValue('C' . $ligne, $equipe->getCadeau()->getContenu() . ' offert par ' . $equipe->getCadeau()->getFournisseur());
            }
            $sheet->getStyle('C' . $ligne . ':D' . $ligne)->getAlignment()->setWrapText(true);
            $sheet->getStyle('A' . $ligne . ':D' . $ligne)
                ->applyFromArray($styleText);
            $sheet->getStyle('A' . $ligne . ':E' . $ligne)->applyFromArray($borderArray);

            $ligne = $ligne + 1;
            $lignep = $ligne + 1;
            $sheet->getRowDimension($ligne)->setRowHeight(20);
            $sheet->setCellValue('A' . $ligne, 'Camille');
            $sheet->mergeCells('B' . $ligne . ':B' . $lignep);
            $sheet->setCellValue('B' . $ligne, 'J\'appelle')
                ->setCellValue('C' . $ligne, 'l\'equipe ' . $equipe->getLettre())
                ->setCellValue('D' . $ligne, $equipe->getTitreProjet());
            $sheet->getStyle('D' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('B' . $ligne)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $aligne = $ligne;
            $ligne = $ligne + 1;
            $sheet->getRowDimension($ligne)->setRowHeight(30);
            $sheet->setCellValue('C' . $ligne, 'AC. ' . $lycee[$lettre][0]->getAcademie())
                ->setCellValue('D' . $ligne, 'Lycee ' . $lycee[$lettre][0]->getNom() . "\n" . $lycee[$lettre][0]->getCommune());
            $sheet->getStyle('C' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('D' . $ligne)->getAlignment()->applyFromArray($vcenterArray);
            $sheet->getStyle('A' . $aligne . ':D' . $ligne)
                ->applyFromArray($styleText);
            $sheet->getStyle('A' . $aligne . ':E' . $lignep)->applyFromArray($borderArray);
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
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        $writer->save('php://output');
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatjury/remplissage_nbnotes_equipes", name="secretariatjury_remplissage_nbnotes_equipes")
     *
     */

    public function remplissage_nbnotes_equipes()
    {//lors de  la conception pour simuler la saisie des notes par les jurés
        $em = $this->getDoctrine()->getManager();
        $equipesRepositiory = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');

        $notesRepository = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Notes');
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');

        $listeEquipes = $equipesRepositiory->findAll();

        foreach ($listeEquipes as $equipe) {
            $nombrenotes = 0;
            $total = 0;
            $listenotes = $notesRepository->createQueryBuilder('n')
                ->where('n.equipe =:equipe')
                ->setParameter('equipe', $equipe)
                ->getQuery()->getResult();
            foreach ($listenotes as $notes) {
                $total = $total + $notes->getPoints();

                $nombrenotes = $nombrenotes + (($notes->getEcrit()) ? 6 : 5);

            }

            $equipe->setNbNotes($nombrenotes);
            $equipe->setTotal($total);

            $em->persist($equipe);
            $em->flush();

        }
        return $this->redirectToRoute('secretariatjury_accueil');


    }

    /**
     *
     * @Security("is_granted('ROLE_JURY')")
     *
     *
     * @Route("/secretariatjury/preparation_tableau_excel_palmares_jury", name = "secretariatjury_preparation_tableau_excel_palmares_jury")
     */
    public function preparation_tableau_excel_palmares_jury(Request $request)
    {

        $em = $this->getDoctrine()->getManager();


        $repositoryEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $repositoryPrix = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Prix');
        $classement = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:User')
            ->findAll();

        $equipes = $repositoryEquipes->findAll();
        $listEquipes = $this->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes')
            ->createQueryBuilder('e')
            ->orderBy('e.classement', 'DESC')
            ->leftJoin('e.infoequipe', 'i')
            ->addOrderBy('i.lyceeAcademie', 'ASC')
            ->addOrderBy('i.lyceeLocalite', 'ASC')
            ->addOrderBy('i.nomLycee', 'ASC')
            ->getQuery()->getResult();
        $listPrix = $repositoryPrix->findAll();
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
     * @Route("/secretariatjury/remplissage_partiel_table_equipes", name="secretariatjury_remplissage_partiel_table_equipes")
     *
     */
    //pour visios de l'édition 28
    public function remplissage_partiel_table_equipes(Request $request)
    {//Permet d'ajouter ou mettre à jour les salles, salles secours, hotes, interlocuteur, observateur à partir du tableau excel Visioconférences-

        $repositoryEquipes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:Equipes');
        $repositoryUser = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('App:User');


        $form = $this->createFormBuilder()
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            $em = $this->getDoctrine()->getManager();

            for ($row = 2; $row <= $highestRow; $row++) {

                $lettre = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $equipe = $repositoryEquipes->createQueryBuilder('e')
                    ->where('e.lettre =:lettre')
                    ->setParameter('lettre', $lettre)
                    ->getQuery()->getSingleResult();

                //	horaire	lettre	sous-jury	interlocuteur	courrielinterlocuteur	telinterlocuteur	hote	lien	lien_principal	code_principal     	lien_secours	code_secours             sujet	    lycée	        civilité	prénom_prof1	nom_prof1	       courriel_prof1	   Tél_prof1	prenom_prof2       nom_prof 2	     tel_prof2        courriel_hote	telhote	observateur	Courielcomit
                //           1              2                   3                   4                                       5                           6                             7                8                    9                           10                                     11                        12                                 13                14                  15                              16                             17                        18                      19                         20                            21                             22                    23              24                  25                                   26
                $horaire = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $equipe->setHeure($horaire);
                $mailinterlocuteur = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $interlocuteur = $repositoryUser->findOneByEmail(['Email' => $mailinterlocuteur]);
                $equipe->setInterlocuteur($interlocuteur);
                $mailhote = $worksheet->getCellByColumnAndRow(27, $row)->getValue();
                $hote = $repositoryUser->findOneByEmail(['Email' => $mailhote]);
                $equipe->setHote($hote);
                $mailobservateur = $worksheet->getCellByColumnAndRow(30, $row)->getValue();
                $observateur = $repositoryUser->findOneByEmail(['Email' => $mailobservateur]);
                $equipe->setObservateur($observateur);
                $salle = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                $equipe->setSalle($salle);
                $code = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                if ($code != null) {
                    $equipe->setCode($code);
                } else {
                    $code = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                    $equipe->setCode($code);
                }
                $sallesecours = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                $equipe->setSallesecours($sallesecours);

                $em->persist($equipe);


                $em->flush();

            }

            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatjury\charge_donnees_excel_equipes.html.twig', array('form' => $form->createView(), 'titre' => 'Compléter les équipes'));
        return new Response($content);
    }

}
    
    
    
    
    
    
    


