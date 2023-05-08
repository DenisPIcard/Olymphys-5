<?php

namespace App\Controller\Cia;


use App\Entity\Coefficients;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Cia\JuresCia;
use App\Entity\Cia\NotesCia;
use App\Entity\Uai;
use App\Form\JuresCiaType;
use App\Service\Mailer;
use Doctrine\DBAL\Types\StringType;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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


    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/Cia/SecretariatjuryCia/accueil", name: "secretariatjuryCia_accueil")]
    public function accueil(): Response
    {
        $em = $this->doctrine->getManager();
        $edition = $this->requestStack->getSession()->get('edition');

        if (new \DateTime('now') < $this->requestStack->getSession()->get('edition')->getDateouverturesite()) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => $edition->getEd() - 1]);
        }
        $repositoryEquipesadmin = $this->doctrine->getRepository(Equipesadmin::class);
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryUai = $this->doctrine->getRepository(Uai::class);
        $listEquipes = $repositoryEquipesadmin->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.edition =:edition')
            ->andWhere('e.numero <:numero')
            ->andWhere('e.centre =:centre')
            ->setParameters(['edition' => $edition, 'numero' => 100, 'centre' => $this->getUser()->getCentrecia()])
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
            array(''));

        return new Response($content);
    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/accueilOrgaCia", name: "secretariatjuryCia_accueilOrgaCia")]
    public function accueilPresident(): Response  // Le président de jury peut gérer le classement des équipes
    {
        $tableau = $this->requestStack->getSession()->get('tableau');
        $listEquipes = $tableau[0];
        $lesEleves = $tableau[1];
        $lycee = $tableau[2];
        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $prof1 = [];
        $prof2 = [];
        foreach ($listEquipes as $equipe) {
            $lettre = $equipe->getLettre();
            $idprof1 = $equipe->getIdProf1();
            $prof1[$lettre] = $repositoryUser->findBy(['id' => $idprof1]);
            $idprof2 = $equipe->getIdProf2();
            $prof2[$lettre] = $repositoryUser->findBy(['id' => $idprof2]);
        }

        $content = $this->renderView('secretariatjuryCia/accueil_jury.html.twig',
            array('listEquipes' => $listEquipes,
                'lesEleves' => $lesEleves,
                'prof1' => $prof1,
                'prof2' => $prof2,
                'lycee' => $lycee));

        return new Response($content);
    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/vueglobale", name: "secretariatjuryCia_vueglobale")]
    public function vueglobale(): Response
    {
        $em = $this->doctrine->getManager();
        $repositoryNotes = $this->doctrine->getRepository(NotesCia::class);

        $repositoryJures = $this->doctrine->getRepository(JuresCia::class);
        $listJures = $repositoryJures->findAll();

        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
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
                    $progression[$nbre_equipes][$nbre_jures] = (is_null($note)) ? '*' : $note->getTotalPoints();
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

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/classement", name: "secretariatjuryCia_classement")]
    public function classement(): Response
    {
        // affiche les équipes dans l'ordre de la note brute
        $em = $this->doctrine->getManager();

        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);

        $coefficients = $this->doctrine->getRepository(Coefficients::class)->findOneBy(['id' => 1]);

        $listEquipes = $repositoryEquipes->findAll();

        foreach ($listEquipes as $equipe) {
            $listesNotes = $equipe->getNotess();
            $nbre_notes = count($equipe->getNotess());//a la place de $equipe->getNbNotes();

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
                $nbNotes = count($equipe->getNotess());//met à jour le nb de notes et le total lors des essais
                $equipe->setNbNotes($nbNotes);
                $equipe->setTotal($points / $nbre_notes);
                $em->persist($equipe);
                $em->flush();
            }

        }

        $nbre_equipes = 0;
        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        try {
            $nbre_equipes = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
        }

        $classement = $repositoryEquipes->classement(0, 0, $nbre_equipes);


        $i = 1;
        foreach ($classement as $equipe) {
            $equipe->setRang($i);
            $em->persist($equipe);
            $i = $i + 1;

        }

        $em->flush();
        //dd($classement);
        $content = $this->renderView('secretariatjury/classement.html.twig',
            array('classement' => $classement)
        );
        return new Response($content);
    }


    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/approche", name: "secretariatjuryCia_approche")]
    public function approche(Request $request): Response
    {
        $em = $this->doctrine->getManager();

        $repositoryEquipes = $em->getRepository(Equipesadmin::class);
        $nbre_equipes = 0;

        $qb = $repositoryEquipes->createQueryBuilder('e');
        $qb->select('COUNT(e)');
        try {
            $nbre_equipes = $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
        }

        $classement = $repositoryEquipes->classement(0, 0, $nbre_equipes);
        /*$classement=$repositoryEquipes->createQueryBuilder('e')->select('e')
                                        ->orderBy('e.couleur','ASC')
                                        ->leftJoin('e.equipeinter','eq')
                                        ->addOrderBy('e.total','DESC')
                                        ->getQuery()->getResult();
*/
        foreach (range('A', 'Z') as $lettre) {

            if ($request->query->get($lettre) != null) {

                $couleur = $request->query->get($lettre);
                $idequipe = $request->query->get('idEquipe');

                $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
                /*  switch ($couleur){
                      case 1: $newclassement='1er';
                              break;
                      case 2: $newclassement='2ème';
                              break;
                      case 3: $newclassement='3ème';
                              break;


                  }
                */
                $equipe->setCouleur($couleur);
                //$equipe->setClassement($newclassement);
                $em->persist($equipe);
                $em->flush();
                /*$classement=$repositoryEquipes->createQueryBuilder('e')->select('e')
                    ->orderBy('e.couleur','ASC')
                    ->leftJoin('e.equipeinter','eq')
                    ->addOrderBy('e.total','DESC')
                    ->getQuery()->getResult();
                */
                //$couleur>$couleurini?$monte=true:$monte=false;
                //$repositoryEquipes->echange_rang($equipe,$monte);
            }

        }

        $content = $this->renderView('secretariatjury/approche.html.twig',
            array('classement' => $classement,
            )
        );

        return new Response($content);
    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/classement_definitif", name: "secretariatjuryCia_classement_definitif")]
    public function classementdefinitif(): Response
    {
        $em = $this->doctrine->getManager();

        $repositoryEquipes = $em->getRepository(Equipesadmin::class);
        $qb = $repositoryEquipes->createQueryBuilder('e')
            ->orderBy('e.couleur', 'ASC')
            ->leftJoin('e.equipeinter', 'i')
            ->addSelect('i')
            ->addOrderBy('i.lettre', 'ASC');

        $classement = $qb->getQuery()->getResult();

        $class = null;
        $i = 0;
        foreach ($classement as $equipe) {
            $i += 1;
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
            $equipe->setRang($i);
            $em->persist($equipe);
        }
        $em->flush();


        $content = $this->renderView('secretariatjuryCia/classement_definitif.html.twig',
            array('classement' => $classement,)
        );
        return new Response($content);

    }


    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/creeJure", name: "secretariatjuryCia_CreeJure")]
    public function creeJure(Request $request, UserPasswordHasherInterface $passwordEncoder, Mailer $mailer): Response    //Creation du juré par l'organisateur cia
    {

        $orgacia = $this->getUser();

        $slugger = new AsciiSlugger();
        $repositoryUser = $this->doctrine->getRepository(User::class);
        $jureCia = new JuresCia();
        $form = $this->createFormBuilder($jureCia)
            ->add('email', EmailType::class)
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
                    $user->setNom($nom);
                    $user->setPrenom($prenom);
                    $user->setEmail($email);
                    $user->setRoles(['ROLE_JURYCIA']);
                    $user->getCentrecia($orgacia->getCentrecia());
                    $user->setUsername($slugger->slug($prenom[0]) . '_' . $slugger->slug($nom));
                    $user->setPassword($passwordEncoder->hashPassword($user, $prenom));
                    $this->doctrine->getManager()->persist($user);
                    $this->doctrine->getManager()->flush();
                    $mailer->sendInscriptionUserJure($orgacia, $user, $prenom);

                }

                $jureCia->setIduser($user);
                //$jureCia->setNomJure($nom);
                //$jureCia->setPrenomJure($prenom);
                $jureCia->setCentrecia($orgacia->getCentrecia());
                if (str_contains($slugger->slug($prenom), '-')) {
                    $initiales = strtoupper(explode('-', $slugger->slug($prenom))[0][0] . explode('-', $slugger->slug($prenom))[1][0] . $slugger->slug($nom[0]));
                } elseif (str_contains($slugger->slug($prenom), '_')) {
                    $initiales = strtoupper(explode('-', $slugger->slug($prenom))[0][0] . explode('-', $slugger->slug($prenom))[1][0] . $slugger->slug($nom[0]));
                } else {
                    $initiales = strtoupper($slugger->slug($prenom[0]) . $slugger->slug($nom[0]));
                }

                $jureCia->setInitialesJure($initiales);

                $this->doctrine->getManager()->persist($jureCia);
                $this->doctrine->getManager()->flush();

                return $this->redirectToRoute('cyberjuryCia_accueil');
            }
            return $this->render('cyberjuryCia/accueil.html.twig');
        }

        return $this->render('cyberjuryCia/creejure.html.twig', ['form' => $form->createView()]);


    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/gestionjures", name: "secretariatjuryCia_gestionjures")]
    public function gestionjures(Request $request)
    {

        $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $this->getUser()->getCentrecia()]);
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $this->getUser()->getCentrecia(), 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $idJure = $request->query->get('jure');
        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $initiales = $request->query->get('initiales');
        $attribs = [];
        $i = 0;

        if ($idJure !== null) {
            $jureModifie = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['id' => $idJure]);
            $jureModifie->setNomJure($nom);
            $jureModifie->setPrenomJure($prenom);
            $jureModifie->setInitialesJure($initiales);

            foreach ($listeEquipes as $equipe) {

                $attribs = $request->query->get('equipe-' . $equipe->getId());

                if ($attribs != '') {
                    $jureModifie->addEquipe($equipe);
                    if ($attribs == 'R') {
                        $rapporteur = $jureModifie->getRapporteur();
                        if ($rapporteur == null) {
                            $rapporteur[0] = $equipe->getNumero();
                            $jureModifie->setRapporteur($rapporteur);
                        }
                        if (!in_array($equipe->getNumero(), $rapporteur)) {//le juré n'était pas rapporteur, il le devient
                            $rapporteur[count($rapporteur)] = $equipe->getNumero();
                            $jureModifie->setRapporteur($rapporteur);
                        }
                    }
                    if ($attribs == 'E') {
                        $jureModifie->addEquipe($equipe);
                        $rapporteur = $jureModifie->getRapporteur();
                        if ($rapporteur !== null) {
                            if (in_array($equipe->getNumero(), $rapporteur)) {//On change l'attribution de l'équipe au juré : il n'est plus rapporteur
                                unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);
                            }
                            $jureModifie->setRapporteur($rapporteur);
                        }
                    }
                }
                if ($attribs == '') {

                    if ($jureModifie->getEquipes() !== null) {
                        foreach ($jureModifie->getEquipes() as $equipetest) {
                            if ($equipe == $equipetest) {
                                $jureModifie->removeEquipe($equipe);
                                $rapporteur = $jureModifie->getRapporteur();
                                if (in_array($equipe->getNumero(), $rapporteur)) {//On change l'attribution de l'équipe au juré : il n'est plus rapporteur
                                    unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);
                                }
                                $jureModifie->setRapporteur($rapporteur);
                            }
                        }

                    }
                }
                $this->doctrine->getManager()->persist($jureModifie);
                $this->doctrine->getManager()->flush();
            }


        }
        return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes));

    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/tableauexcel_repartition", name: "secretariatjuryCia_tableau excel_repartition")]
    public function tableauexcel_repartition()
    {
        $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $this->getUser()->getCentrecia()]);
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $this->getUser()->getCentrecia(), 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CIA-" . $this->getUser()->getCentrecia() . "-Tableau destiné aux organisateurs")
            ->setSubject("Tableau destiné aux organisateurds")
            ->setDescription("Office 2007 XLSX répartition des jurés")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        $ligne = 1;

        $sheet
            ->setCellValue('A' . $ligne, 'Prénom juré')
            ->setCellValue('B' . $ligne, 'Nom juré')
            ->setCellValue('C' . $ligne, 'Initiales');

        $lettres = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'L', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        $i = 0;
        foreach ($listeEquipes as $equipe) {

            $sheet->setCellValue($lettres[$i] . $ligne, $equipe->getNumero());
            $i = $i + 1;
        }

        $ligne += 1;
        $styleArray = ['strikethrough' => 'on'];

        foreach ($listejures as $jure) {
            $equipesjure = $jure->getEquipes();
            $sheet->setCellValue('A' . $ligne, $jure->getPrenomJure());
            $sheet->setCellValue('B' . $ligne, $jure->getNomJure());
            $sheet->setCellValue('C' . $ligne, $jure->getInitialesJure());
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

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->getUser()->getCentrecia() . '-répartition des jurés.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        //$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        // $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');

    }
}