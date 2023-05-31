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

    #[IsGranted('ROLE_COMITE')]
    #[Route("/Cia/SecretariatjuryCia/accueil_comite", name: "secretariatjuryCia_accueil_comite")]
    public function accueil_comite(): Response
    {
        $listeCentres = $this->doctrine->getRepository(Centrescia::class)->findBy(['actif' => true]);
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
            array('rangs' => $rangs, 'equipes' => $listEquipes, 'centre' => $centrecia->getCentre())
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
    #[Route("/secretariatjuryCia/creeJure", name: "secretariatjuryCia_creeJure")]
    public function creeJure(Request $request, UserPasswordHasherInterface $passwordEncoder, Mailer $mailer): Response    //Creation du juré par l'organisateur cia
    {

        $orgacia = $this->getUser();
        $this->requestStack->getSession()->set('info', '');
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
                    try {
                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        $user->setEmail($email);
                        $user->setRoles(['ROLE_JURYCIA']);
                        $user->setCentrecia($orgacia->getCentrecia());
                        $username = $slugger->slug($prenom[0]) . '_' . $slugger->slug($nom);
                        $prenom = $slugger->slug($prenom);
                        $i = 1;
                        while ($repositoryUser->findBy(['username' => $username])) {//pour éviter des logins identiques
                            $username = $username . $i;
                            $i = +1;
                        }
                        $user->setUsername($username);
                        $user->setPassword($passwordEncoder->hashPassword($user, $prenom));
                        $this->doctrine->getManager()->persist($user);
                        $this->doctrine->getManager()->flush();
                        $mailer->sendInscriptionUserJure($orgacia, $user, $prenom);
                    } catch (\Exception $e) {
                        $texte = 'Une erreur est survenue lors de l\'inscription de ce jure :' . $e;
                        $this->requestStack->getSession()->set('info', $texte);
                    }

                }
                $jure = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['iduser' => $user]);
                if ($jure === null) {

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
                } else {
                    $texte = 'Ce juré existe déjà !';
                    $this->requestStack->getSession()->set('info', $texte);

                }

                return $this->redirectToRoute('secretariatjuryCia_gestionjures');
            }
            return $this->render('cyberjuryCia/accueil.html.twig');
        }

        return $this->render('cyberjuryCia/creejure.html.twig', ['form' => $form->createView()]);


    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/gestionjures", name: "secretariatjuryCia_gestionjures")]
    public function gestionjures(Request $request)
    {

        $idJure = $request->get('jure');
        // dd($request);
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
            $idJure = null;//Le formulaire est envoyé dès le clic sur un des input

        }

        $listejures = $this->doctrine->getRepository(JuresCia::class)->findBy(['centrecia' => $this->getUser()->getCentrecia()]);
        $listeEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->where('e.centre =:centre')
            ->andWhere('e.edition =:edition')
            ->setParameters(['centre' => $this->getUser()->getCentrecia(), 'edition' => $this->requestStack->getSession()->get('edition')])
            ->getQuery()->getResult();

        if ($idJure !== null) {

            $nom = $request->get('nom');
            $prenom = $request->get('prenom');
            $initiales = $request->get('initiales');
            $numJury = $request->get('numJury' . $idJure);

            $jureModifie = $this->doctrine->getRepository(JuresCia::class)->findOneBy(['id' => $idJure]);
            $jureModifie->setNomJure($nom);
            $jureModifie->setPrenomJure($prenom);
            $jureModifie->setInitialesJure($initiales);
            $jureModifie->setNumJury($numJury);
            foreach ($listeEquipes as $equipe) {

                $attribs = $request->get('equipe-' . $equipe->getId());

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
                                unset($rapporteur[array_search($equipe->getNumero(), $rapporteur)]);//supprime le numero de l'équipe dans la liste du champ rapporteur
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

        return $this->render('cyberjuryCia/gestionjures.html.twig', array('listejures' => $listejures, 'listeEquipes' => $listeEquipes, 'centre' => $this->getUser()->getCentrecia()));

    }

    #[IsGranted('ROLE_ORGACIA')]
    #[Route("/secretariatjuryCia/tableauexcel_repartition", name: "secretariatjuryCia_tableauexcel_repartition")]
    public function tableauexcel_repartition(): void
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
            ->setSubject("Tableau destiné aux organisateurs")
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

    #[IsGranted('ROLE_JURYCIA')]
    #[Route("/secretariatjuryCia/envoi_mail_conseils,{idEquipe}", name: "secretariatjuryCia_envoi_mail_conseils")]
    public function envoi_mail_conseils($idEquipe, Mailer $mailer)
    {
        $equipe = $this->doctrine->getRepository(Equipesadmin::class)->find($idEquipe);
        $prof1 = $equipe->getIdProf1();
        $prof2 = $equipe->getIdProf2();
        $conseil = $this->doctrine->getRepository(ConseilsjuryCia::class)->findOneBy(['equipe' => $equipe]);


        $mailer->sendConseil($conseil, $prof1, $prof2);


        return $this->redirectToRoute('cyberjuryCia_gerer_conseils_equipe', ['centre' => $equipe->getCentre()->getCentre()]);
    }
}