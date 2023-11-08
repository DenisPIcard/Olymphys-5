<?php

namespace App\Controller\Cia;


use App\Entity\Centrescia;
use App\Entity\Cia\ConseilsjuryCia;
use App\Entity\Cia\RangsCia;
use App\Entity\Coefficients;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Cia\JuresCia;
use App\Entity\Cia\NotesCia;
use App\Entity\Uai;
use App\Form\JuresCiaType;
use App\Form\NotesCiaType;
use App\Service\Mailer;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
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

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    #[IsGranted('ROLE_COMITE')]
    #[Route("/Cia/SecretariatjuryCia/accueil_comite", name: "secretariatjuryCia_accueil_comite")]
    public function accueil_comite(): Response
    {
        $listeCentres = $this->doctrine->getRepository(Centrescia::class)->findBy(['actif' => true], ['centre' => 'ASC']);
        $content = $this->renderView('cyberjuryCia/accueil_comite.html.twig',
            array('centres' => $listeCentres));

        return new Response($content);


    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/Cia/SecretariatjuryCia/accueil,{centre}", name: "secretariatjuryCia_accueil")]
    public function accueil($centre): Response
    {
        $em = $this->doctrine->getManager();
        $edition = $this->requestStack->getSession()->get('edition');

        if (new \DateTime('now') < $this->requestStack->getSession()->get('edition')->getDateouverturesite()) {
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
            ->setParameters(['edition' => $edition, 'numero' => 100, 'centre' => $centre])
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
        $session = $this->requestStack->getSession();
        $session->set('tableau', $tableau);
        $content = $this->renderView('cyberjuryCia/accueil.html.twig',
            array('centre' => $centre, 'equipes' => $listEquipes));

        return new Response($content);
    }


    #[IsGranted('ROLE_ORGACIA')]
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
        $listEquipes = $repositoryEquipes->findBy(['edition' => $this->requestStack->getSession()->get('edition'), 'centre' => $centrecia]);

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
                $statut = $repositoryJures->getAttribution($jure, $equipe);//vérifie l'attribution du juré ! 0 si assiste, 1 si lecteur sinon Null
                //récupère l'évaluation de l'équipe par le juré dans $note pour l'afficher(mémoire compris) :
                if (is_null($statut)) { //Si le juré n'a pas à évaluer l'équipe le statut est null
                    $progression[$nbre_equipes][$nbre_jures] = 'ras';

                } elseif ($statut == 1) {// le juré évalue le mémoire(il est rapporteur)
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getTotalPoints();
                } else { // Le juré n'évalue pas le mémoire, il est simple examinateur
                    $note = $repositoryNotes->EquipeDejaNotee($id_jure, $id_equipe);
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getPoints();
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
        $repositoryCentres = $this->doctrine->getRepository(Centrescia::class);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $repositoryRangs = $this->doctrine->getRepository(RangsCia::class);
        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $numJury = $repositoryJures->findOneBy(['iduser' => $this->getUser()])->getNumJury();
        $centrecia = $repositoryCentres->findOneBy(['centre' => $centre]);
        $equipesSousJury = $repositoryJures->getEquipesSousJury($centrecia, $numJury);
        $rangs = $repositoryRangs->classementSousJury($equipesSousJury);

        $content = $this->renderView('cyberjuryCia/classement_sous_jury.html.twig',
            array('rangs' => $rangs, 'equipes' => $equipesSousJury, 'centre' => $centrecia->getCentre())
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

                if ($user === null) {
                    $user = new User();
                    try {
                        $user->setNom(strtoupper($nom));
                        $user->setPrenom(ucfirst(strtolower($prenom)));
                        $user->setEmail($email);
                        $user->setRoles(['ROLE_JURYCIA']);
                        $user->setCentrecia($centrecia);
                        $username = $slugger->slug($prenom[0]) . '_' . $slugger->slug($nom);
                        $pwd = $slugger->slug($prenom);
                        $i = 1;
                        while ($repositoryUser->findBy(['username' => $username])) {//pour éviter des logins identiques
                            $username = $username . $i;
                            $i = +1;
                        }
                        $user->setUsername($username);
                        $user->setPassword($passwordEncoder->hashPassword($user, $pwd));
                        $this->doctrine->getManager()->persist($user);
                        $this->doctrine->getManager()->flush();
                        $mailer->sendInscriptionUserJure($orgacia, $user, $pwd, $centre);
                    } catch (\Exception $e) {
                        $texte = 'Une erreur est survenue lors de l\'inscription de ce jure :' . $e;
                        $this->requestStack->getSession()->set('info', $texte);
                    }

                } else {
                    $user->setRoles(['ROLE_JURYCIA']);
                    $user->setCentrecia($centrecia);
                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();
                }
                $jure = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['iduser' => $user]);
                if ($jure === null) {
                    $jureCia = new JuresCia();
                    $jureCia->setIduser($user);
                    //$jureCia->setNomJure($nom);
                    //$jureCia->setPrenomJure($prenom);
                    $jureCia->setCentrecia($centrecia);
                    if (str_contains($slugger->slug($prenom), '-')) {
                        $initiales = strtoupper(explode('-', $slugger->slug($prenom))[0][0] . explode('-', $slugger->slug($prenom))[1][0] . $slugger->slug($nom[0]));
                    } elseif (str_contains($slugger->slug($prenom), '_')) {
                        $initiales = strtoupper(explode('-', $slugger->slug($prenom))[0][0] . explode('-', $slugger->slug($prenom))[1][0] . $slugger->slug($nom[0]));
                    } else {
                        $initiales = strtoupper($slugger->slug($prenom))[0] . strtoupper($slugger->slug($nom))[0];
                    }
                    $jureCia->setEmail($email);
                    $jureCia->setInitialesJure($initiales);
                    $this->doctrine->getManager()->persist($jureCia);
                    $this->doctrine->getManager()->flush();
                    $mailer->sendInscriptionJureCia($orgacia, $jureCia, $prenom, $centre);
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
    public function gestionjures(Request $request, $centre)
    {

        $centre = $this->doctrine->getRepository(Centrescia::class)->findOneBy(['centre' => $centre]);//$centrecia est un string nom du centre
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $centre, 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();

        if ($request->get('idjure') !== null) {//pour la modif des données perso du juré
            $idJure = $request->get('idjure');
            $val = $request->get('value');
            $type = $request->get('type');
            $jure = $this->doctrine->getRepository(JuresCia::class)->find($idJure);
            switch ($type) {
                case 'prenom':
                    $jure->setPrenomJure($val);
                    break;
                case 'nom' :
                    $jure->setNomJure($val);
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
            if ($attrib == 'E') {

                $jure->addEquipe($equipe);//la fonctionadd contient le test d'existence de l'équipe et ne l'ajoute que si elle n'est pas dans la liste des équipes du juré
                $rapporteur = $jure->getRapporteur();
                if ($rapporteur !== null) {
                    if (in_array($equipe->getNumero(), $rapporteur)) {//On change l'attribution de l'équipe au juré : il n'est plus rapporteur
                        unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);//supprime le numero de l'équipe dans la liste du champ rapporteur
                    }
                    $jure->setRapporteur($rapporteur);
                }
            }
            if ($attrib == '') {
                $rapporteur = $jure->getRapporteur();
                if ($rapporteur !== null) {
                    if (in_array($equipe->getNumero(), $rapporteur)) {//On change l'attribution de l'équipe au juré : il n'est plus rapporteur
                        unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);//supprime le numero de l'équipe dans la liste du champ rapporteur
                    }
                    $jure->setRapporteur($rapporteur);
                }
                if ($jure->getEquipes()->contains($equipe)) {
                    $jure->removeEquipe($equipe);
                }
            }
            $this->doctrine->getManager()->persist($jure);
            $this->doctrine->getManager()->flush();
            $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $this->getUser()->getCentrecia()], ['numJury' => 'ASC']);

            return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $centre->getCentre()));


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
            }

            $this->doctrine->getManager()->remove($jure);
            $this->doctrine->getManager()->flush();
            $idJure = null;//Dans le cas où le formulaire est envoyé dès le clic sur un des input

        }

        $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $centre], ['numJury' => 'ASC']);
        return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $centre->getCentre()));

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

        $ligne = 6;
        $sheet
            ->setCellValue('A' . $ligne, 'Prénom juré')
            ->setCellValue('B' . $ligne, 'Nom juré')
            ->setCellValue('C' . $ligne, 'Initiales')
            ->setCellValue('D' . $ligne, 'sous-jury');
        $sheet->getRowDimension($ligne)->setRowHeight(25, 'pt');
        $i = 0;
        $spreadsheet->getActiveSheet()->getStyle('A' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->applyFromArray($styleArrayTop);
        foreach ($listeEquipes as $equipe) {

            $sheet->setCellValue($lettres[$i] . $ligne, $equipe->getNumero());
            $i = $i + 1;
        }

        $ligne += 1;

        $sheet->getRowDimension($ligne)->setRowHeight(25, 'pt');
        foreach ($listejures as $jure) {
            $spreadsheet->getActiveSheet()->getStyle('A' . $ligne . ':' . $lettres[count($listeEquipes) - 1] . $ligne)->applyFromArray($styleArray);
            $sheet->getRowDimension($ligne)->setRowHeight(25, 'pt');
            $equipesjure = $jure->getEquipes();
            $sheet->setCellValue('A' . $ligne, $jure->getPrenomJure());
            $sheet->setCellValue('B' . $ligne, $jure->getNomJure());
            $sheet->setCellValue('C' . $ligne, $jure->getInitialesJure());
            $sheet->setCellValue('D' . $ligne, $jure->getNumJury());
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
            $i = 0;

            foreach ($listeEquipes as $equipe) {
                $sheet->setCellValue($lettres[$i] . $ligne, '*');
                foreach ($equipesjure as $equipejure) {

                    if ($equipejure == $equipe) {
                        $sheet->setCellValue($lettres[$i] . $ligne, 'E');
                        if (in_array($equipe->getNumero(), $jure->getRapporteur())) {
                            $sheet->setCellValue($lettres[$i] . $ligne, 'R');
                        }
                    }
                }
                $i += 1;
            }
            $ligne += 1;
        }
        //$sheet->getStyle('A2:' . $lettres[$i - 1] . '2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('00AA66');

        foreach (range('A', $lettres[$i - 1]) as $letra) {
            $sheet->getColumnDimension($letra)->setAutoSize(true);
        }


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
    #[Route("/secretariatjuryCia/envoi_mail_conseils,{idEquipe}", name: "secretariatjuryCia_envoi_mail_conseils")]
    public function envoi_mail_conseils($idEquipe, Mailer $mailer)//Envoie le conseil aux prof1 et prof2
    {
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idEquipe);
        $prof1 = $equipe->getIdProf1();
        $prof2 = $equipe->getIdProf2();
        $conseil = $this->doctrine->getRepository(ConseilsjuryCia::class)->findOneBy(['equipe' => $equipe]);


        $mailer->sendConseil($conseil, $prof1, $prof2);


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

            $nllEquipe = $form->get('equipe')->getData();
            $notenllequipe = new NotesCia();
            $notenllequipe->setJure($jure);
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
            $this->doctrine->getManager()->persist($notenllequipe);
            $this->doctrine->getManager()->remove($noteequipe);
            $jure->addEquipe($nllEquipe);
            $this->doctrine->getManager()->persist($notenllequipe);
            $jure->removeEquipe($equipe);
            $this->doctrine->getManager()->persist($jure);
            $this->doctrine->getManager()->flush();
            return $this->redirectToRoute('secretariatjuryCia_vueglobale', ['centre' => $jure->getCentrecia()]);
        }
        return $this->render('cyberjuryCia/modifNoteJureCia.html.twig', ['form' => $form->createView(), 'equipe' => $equipe, 'centre' => $jure->getCentrecia(), 'jure' => $jure]);

    }


}