<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Jures;
use App\Entity\Rne;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SecretariatadminController extends AbstractController
{

    public $password;
    private UserPasswordHasherInterface $passwordEncoder;
    private EntityManagerInterface $em;
    private ManagerRegistry $doctrine;
    private RequestStack $requestStack;

    public function __construct(EntityManagerInterface      $em,
                                ValidatorInterface          $validator,
                                ManagerRegistry             $doctrine,
                                UserPasswordHasherInterface $passwordEncoder, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;


        $this->passwordEncoder = $passwordEncoder;


    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Route("/secretariatadmin/charge_rne", name="secretariatadmin_charge_rne")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function charge_rne(Request $request)
    {
        $defaultData = ['message' => 'Charger le fichier des élèves '];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $repositoryRne = $this
            ->doctrine
            ->getManager()
            ->getRepository(Rne::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            $em = $this->doctrine->getManager();

            for ($row = 2; $row <= $highestRow; ++$row) {

                $value = $worksheet->getCellByColumnAndRow(2, $row)->getValue();//On lit le rne
                $rne = $repositoryRne->findOneByRne($value);//On vérifie si  cet rne est déjà dans la base
                if (!$rne) { // si le rne n'existe pas, on le crée
                    $rne = new rne();
                } //sinon on écrase les précédentes données
                $rne->setRne($value);
                $value = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $rne->setNature($value);
                $value = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $rne->setSigle($value);
                $value = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $rne->setCommune($value);
                $value = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $rne->setAcademie($value);
                $value = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                $rne->setPays($value);
                $value = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                $rne->setDepartement($value);
                $value = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $rne->setDenominationPrincipale($value);
                $value = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                $rne->setAppellationOfficielle($value);
                $value = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                $rne->setNom($value);
                $value = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                $rne->setAdresse($value);
                $value = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                $rne->setBoitePostale($value);
                $value = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                $rne->setCodePostal($value);
                $value = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                $rne->setAcheminement($value);
                $value = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                $rne->setCoordonneeX($value);
                $value = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                $rne->setCoordonneeY($value);
                $em->persist($rne);
                $em->flush();

            }
            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('form' => $form->createView(), 'titre' => 'Enregistrer le RNE'));
        return new Response($content);

    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Route("/secretariatadmin/charge_eleves_inter", name="secretariatadmin_charge_eleves_inter")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function charge_eleves_inter(Request $request)
    {
        $session = $this->requestStack->getSession();
        $defaultData = ['message' => 'Charger le fichier des élèves '];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $repositoryElevesinter = $this
            ->doctrine
            ->getManager()
            ->getRepository(Elevesinter::class);
        $repositoryEquipesadmin = $this
            ->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $edition = $session->get('edition');
        $edition = $this->em->merge($edition);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            $em = $this->doctrine->getManager();

            for ($row = 2; $row <= $highestRow; ++$row) {

                $value = $worksheet->getCellByColumnAndRow(1, $row)->getValue();//On lit l'id de l'élève sur le site odpf

                $numsite = $value;//idsite est l'id du site odpf


                $qb = $repositoryElevesinter->createQueryBuilder('e')
                    ->where('e.numsite =:numsite')
                    ->setParameter('numsite', intval($numsite));//On vérifie si  cet élèves est déjà dans la base
                $query = $qb->getQuery();

                $eleves = $query->getResult();

                if (!$eleves) {// si l'éleve n'existe pas, on le crée
                    $eleve = new elevesinter();
                    $eleve->setNumsite(intval($numsite));
                } else {
                    $eleve = $eleves[0];
                } //sinon on écrase les précédentes données

                $nom = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $eleve->setNom($nom);
                $value = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $eleve->setPrenom($value);
                $value = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $eleve->setClasse($value);
                $value = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                $eleve->setCourriel($value);
                $value = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                $eleve->setGenre($value);
                $numero = $worksheet->getCellByColumnAndRow(20, $row)->getValue();

                $qb1 = $repositoryEquipesadmin->createQueryBuilder('e')
                    ->where('e.edition =:edition')
                    ->andWhere('e.numero =:numero')
                    ->setParameter('edition', $edition)
                    ->setParameter('numero', $numero);

                $equipes = $qb1->getQuery()->getResult();

                if ($equipes) {
                    $eleve->setEquipe($equipes[0]);


                    $em->persist($eleve);


                    $em->flush();

                }

            }

            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('form' => $form->createView(), 'titre' => 'Enregistrer les élèves'));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/charge_equipeinter", name="secretariatadmin_charge_equipeinter")
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function charge_equipeinter(Request $request)
    {

        $defaultData = ['message' => 'Charger le fichier '];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $repositoryEquipesadmin = $this
            ->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryEdition = $this
            ->doctrine
            ->getManager()
            ->getRepository(Edition::class);
        $repositoryRne = $this
            ->doctrine
            ->getManager()
            ->getRepository(Rne::class);
        $edition = $repositoryEdition->findOneBy([], ['id' => 'desc']);
        //$edition=$repositoryEdition->find(['id' => 1]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            $em = $this->doctrine->getManager();

            for ($row = 2; $row <= $highestRow; $row++) {

                $numero = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $qb = $repositoryEquipesadmin->createQueryBuilder('e')
                    ->where('e.numero =:numero')
                    ->setParameter('numero', $numero)
                    ->andWhere('e.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->setMaxResults(1);


                $equipe = $qb->getQuery()->getOneOrNullResult();


                if (!$equipe) {
                    $equipe = new Equipesadmin();

                }

                $equipe->setEdition($edition);
                $equipe->setNumero($numero);
                $value = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                if ($value != '~') {


                    $equipe->setLettre($value);
                }
                $value = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                $equipe->setNomLycee($value);
                $value = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                $equipe->setDenominationLycee($value);
                $rne = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                $equipe->setRne($rne);
                $value = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                $equipe->setLyceeLocalite($value);
                $value = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                $equipe->setLyceeAcademie($value);
                $value = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $equipe->setTitreProjet($value);
                $prenomProf1 = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                $equipe->setPrenomProf1($prenomProf1);
                $nomProf1 = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                $equipe->setNomProf1($nomProf1);
                $prenomProf2 = $worksheet->getCellByColumnAndRow(24, $row)->getValue();
                $equipe->setPrenomProf2($prenomProf2);
                $nomProf2 = $worksheet->getCellByColumnAndRow(25, $row)->getValue();
                $equipe->setNomProf2($nomProf2);
                $rneid = $repositoryRne->findOneBy(['rne' => $rne]);
                //dd($rneid);
                $equipe->setRneId($rneid);

                $repositoryUser = $this->doctrine
                    ->getManager()
                    ->getRepository(User::class);

                $qb1 = $repositoryUser->createQueryBuilder('u')->select('u')
                    ->where('u.nom=:nomprof1')
                    ->setParameter('nomprof1', $nomProf1)
                    ->andwhere('u.prenom=:prenomprof1')
                    ->setParameter('prenomprof1', $prenomProf1);
                $prof1 = $qb1->getQuery()->getResult();
                foreach ($prof1 as $prof) {
                    $equipe->setIdProf1($prof->getId());
                }
                $qb2 = $repositoryUser->createQueryBuilder('u')->select('u')
                    ->where('u.nom=:nomprof2')
                    ->setParameter('nomprof2', $nomProf2)
                    ->andwhere('u.prenom=:prenomprof2')
                    ->setParameter('prenomprof2', $prenomProf2);
                $prof2 = $qb2->getQuery()->getResult();


                foreach ($prof2 as $prof) {
                    $equipe->setIdProf2($prof->getId());
                }


                $equipe->setSelectionnee(0);
                $em->persist($equipe);


                $em->flush();

            }
            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('form' => $form->createView(), 'titre' => 'Enregistrer les équipes'));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/charge_user", name="secretariatadmin_charge_user")
     *
     */


    public function charge_user(Request $request)
    {
        $defaultData = ['message' => 'Charger le fichier '];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $repositoryUser = $this
            ->doctrine
            ->getManager()
            ->getRepository(User::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            $em = $this->doctrine->getManager();

            for ($row = 2; $row <= $highestRow; ++$row) {

                $value = $worksheet->getCellByColumnAndRow(2, $row)->getValue();//on récupère le username
                $username = $value;
                if ($username != null) {
                    $user = $repositoryUser->findOneByUsername($username);
                    if ($user == null) {
                        $user = new user();
                        $user->setCreatedAt(new DateTime('now'));
                        $user->setLastVisit(new DateTime('now'));
                    } //si l'user n'est pas existant on le crée sinon on écrase les anciennes valeurs pour une mise à jour
                    $user->setUsername($username);
                    $value = $worksheet->getCellByColumnAndRow(3, $row)->getValue();//on récupère le role

                    $user->setRoles([$value]);
                    $value = $worksheet->getCellByColumnAndRow(4, $row)->getValue();//password
                    $password = $this->passwordEncoder->hashPassword($user, $value);
                    $user->setPassword($password);
                    $value = $worksheet->getCellByColumnAndRow(5, $row)->getValue();//actif
                    $user->setIsactive($value);
                    $value = $worksheet->getCellByColumnAndRow(6, $row)->getValue();//email
                    $user->setEmail($value);


                    $value = $worksheet->getCellByColumnAndRow(8, $row)->getValue(); //rne
                    $user->setrne($value);
                    $value = $worksheet->getCellByColumnAndRow(9, $row)->getValue(); //adresse
                    $user->setAdresse($value);
                    $value = $worksheet->getCellByColumnAndRow(10, $row)->getValue(); //ville
                    $user->setVille($value);
                    $value = $worksheet->getCellByColumnAndRow(11, $row)->getValue();//code
                    $user->setCode($value);
                    $value = $worksheet->getCellByColumnAndRow(12, $row)->getValue(); //nom
                    $user->setNom($value);
                    $value = $worksheet->getCellByColumnAndRow(13, $row)->getValue();//prenom
                    $user->setPrenom($value);
                    $value = $worksheet->getCellByColumnAndRow(14, $row)->getValue();//phone
                    $user->setPhone($value);
                    $user->setUpdatedAt(new DateTime('now'));

                    /*$errors = $this->validator->validate($user);
                     if (count($errors) > 0) {
                                 $errorsString = (string) $errors;
                                 throw new \Exception($errorsString);
                             }*/
                    $em->persist($user);


                    $em->flush();
                }
            }

            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('form' => $form->createView(), 'titre' => 'Enregistrer les users'));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/charge_equipe1", name="secretariatadmin_charge_equipe1")
     *
     */
    /*	public function charge_equipe1(Request $request)
        {
                $defaultData = ['message' => 'Charger le fichier Équipe'];
                $form = $this->createFormBuilder($defaultData)
                                ->add('fichier',      FileType::class)
                                ->add('Envoyer',      SubmitType::class)
                                ->getForm();
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid())
                    {
                    $data=$form->getData();
                    $fichier=$data['fichier'];
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fichier);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();
                    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                    $em = $this->doctrine->getManager();
                    $lettres = range('A', 'Z');
                    $row=1;
                   foreach ($lettres as $lettre)
                       {
                       $equipe= new totalequipes();
                       $value = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                       $equipe->setNumeroEquipe($value) ;
                       $value = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                       $equipe->setLettreEquipe($value) ;
                       $value = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                       $equipe->setNomEquipe($value) ;
                       $value = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                       $equipe->setNomLycee($value) ;
                       $value = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                       $equipe->setDenominationLycee($value) ;
                       $value = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                       $equipe->setLyceeLocalite($value) ;
                       $value = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                       $equipe->setLyceeAcademie($value) ;
                       $value = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                       $equipe->setPrenomProf1($value) ;
                       $value = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                       $equipe->setNomProf1($value) ;
                       $value = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                       $equipe->setPrenomProf2($value) ;
                       $value = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                       $equipe->setNomProf2($value) ;
                       $em->persist($equipe);
                       $row +=1;
                        }
                        $em->flush();
                      return $this->redirectToRoute('secretariat_accueil');
                }
            $content = $this
                            ->renderView('secretariat\uploadexcel.html.twig', array('form'=>$form->createView(),));
        return new Response($content);
            }
     */

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/cree_equipes", name="secretariatadmin_cree_equipes")
     *
     */
    public function cree_equipes(Request $request)
    {
        $session = $this->requestStack->getSession();
        $form = $this->createFormBuilder()
            ->add('Creer', SubmitType::class)
            ->getForm();

        $repositoryEquipesadmin = $this
            ->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryEquipes = $this
            ->doctrine
            ->getManager()
            ->getRepository(Equipes::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $listEquipesinter = $repositoryEquipesadmin->createQueryBuilder('e')
                ->select('e')
                ->andwhere('e.edition =:edition')
                ->setParameter('edition', $session->get('edition'))
                ->andwhere('e.selectionnee = 1')
                ->orderBy('e.lettre', 'ASC')
                ->getQuery()
                ->getResult();
            $em = $this->doctrine->getManager();

            foreach ($listEquipesinter as $equipesel) {

                if (!$repositoryEquipes->findOneBy(['equipeinter' => $equipesel])) {//Vérification de l'existence de cette équipe
                    $equipe = new equipes();
                } else {
                    $equipe = $repositoryEquipes->findOneBy(['equipeinter' => $equipesel]);
                }

                $equipe->setEquipeinter($equipesel);
                $equipe->setOrdre(1);
                $equipe->setHeure('00H00');
                $equipe->setSalle('000');
                $equipe->setClassement(0);

                //$equipe->setTitreProjet($equipesel->getTitreProjet());

                $em->persist($equipe);
                $em->flush();

            }

            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\creer_equipes.html.twig', array('form' => $form->createView(),));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/charge_jures", name="secretariatadmin_charge_jures")
     *
     */
    public function charge_jures(Request $request)
    {

        $defaultData = ['message' => 'Charger le fichier Jures'];
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
            //$lettres = range('A','Z') ;
            $repositoryEquipes = $this->doctrine->getManager()
                ->getRepository(Equipes::class);
            $equipes = $repositoryEquipes->createQueryBuilder('e')
                ->leftJoin('e.equipeinter', 'eq')
                ->orderBy('eq.lettre', 'ASC')
                ->getQuery()->getResult();


            $repositoryUser = $this->doctrine->getManager()
                ->getRepository(User::class);


            for ($row = 2; $row <= $highestRow; ++$row) {
                $jure = new jures();
                $prenom = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $jure->setPrenomJure($prenom);
                $nom = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $jure->setNomJure($nom);
                $initiales = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $jure->setInitialesJure($initiales);

                $user = $repositoryUser->createQueryBuilder('u')
                    ->where('u.nom =:nom')
                    ->setParameter('nom', $nom)
                    ->andWhere('u.prenom =:prenom')
                    ->setParameter('prenom', $prenom)
                    ->getQuery()->getResult();

                if (count($user) > 1) {
                    foreach ($user as $jury) {//certains jurés sont parfois aussi organisateur des cia avec un autre compte.on ne sélectionne que le compte de role jury

                        if (in_array('ROLE_JURY', $jury->getRoles())) {
                            $jure->setIduser($jury);
                        }
                    }
                } else {
                    $jure->setIduser($user[0]);
                }


                $colonne = 4;


                foreach ($equipes as $equipe) {
                    $value = $worksheet->getCellByColumnAndRow($colonne, $row)->getValue();

                    $method = 'set' . $equipe->getEquipeinter()->getLettre();
                    $jure->$method($value);

                    $colonne += 1;
                }
                $em->persist($jure);
                $em->flush();
            }

            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('titre' => 'Remplissage de la table Jurés', 'form' => $form->createView(),));
        return new Response($content);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/charge_equipe_id_rne", name="secretariatadmin_charge_equipe_id_rne")
     *
     */
    public function charge_equipe_id_rne(Request $request)
    {
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryRne = $this->doctrine
            ->getManager()
            ->getRepository(Rne::class);
        $equipes = $repositoryEquipes->findAll();
        $em = $this->doctrine->getManager();
        $rnes = $repositoryRne->findAll();
        foreach ($equipes as $equipe) {
            foreach ($rnes as $rne) {
                if ($rne->getRne() == $equipe->getRne()) {
                    $equipe->setRneId($rne);
                }
            }
            $em->persist($equipe);
            $em->flush();

        }
        return $this->redirectToRoute('core_home');


    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/set_editon_equipe", name="secretariatadmin_set_editon_equipe")
     *
     */
    public function set_edition_equipe(Request $request)
    {
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryEleves = $this->doctrine
            ->getManager()
            ->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine
            ->getManager()
            ->getRepository(Edition::class);
        $qb = $repositoryEquipes->CreateQueryBuilder('e')
            ->where('e.edition is NULL')
            ->andWhere('e.numero <:nombre')
            ->setParameter('nombre', '100');

        $Equipes = $qb->getQuery()->getResult();


        $edition = $repositoryEdition->find(['id' => 1]);

        foreach ($Equipes as $equipe) {
            if (null == $equipe->getEdition()) {

                $em = $this->doctrine->getManager();
                $equipe->setEdition($edition);
                $em->persist($equipe);
                $em->flush();
            }

        }
        return $this->redirectToRoute('core_home');
    }

    /**
     * @Security("is_granted('ROLE_PROF')")
     *
     * @Route("/secretariatadmin/modif_equipe,{idequipe}", name="modif_equipe")
     *
     */
    public function modif_equipe(Request $request, $idequipe)
    {
        $em = $this->doctrine->getManager();
        $repositoryEquipesadmin = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);


        $repositoryElevesinter = $this->doctrine
            ->getManager()
            ->getRepository(Elevesinter::class);

        $equipe = $repositoryEquipesadmin->findOneById(['id' => $idequipe]);
        $listeEleves = $repositoryElevesinter->findByEquipe(['equipe' => $equipe]);
        $i = 0;
        $form[$i] = $this->createFormBuilder($equipe)
            ->add('titreprojet', TextType::class, [
                'mapped' => false,
                'data' => $equipe->getTitreprojet(),

            ])
            ->add('saveE', SubmitType::class, ['label' => 'Sauvegarder'])
            ->getForm();
        $form[$i]->handleRequest($request);
        $formview[$i] = $form[$i]->createView();
        if ($form[$i]->isSubmitted() && $form[$i]->isValid()) {
            if ($form[$i]->get('saveE')->isClicked()) {
                $em->persist($equipe);
                $em->flush();
            }
            return $this->redirectToRoute('modif_equipe', array('idequipe' => $idequipe));
        }
        $i++;
        foreach ($listeEleves as $eleve) {
            $form[$i] = $this->createFormBuilder()
                ->add('nom', TextType::class, [
                    'mapped' => false,
                    'data' => $eleve->getNom(),
                ])
                ->add('prenom', TextType::class, [
                    'mapped' => false,
                    'data' => $eleve->getPrenom(),
                ])
                ->add('courriel', EmailType::class, [
                    'mapped' => false,
                    'data' => $eleve->getCourriel(),
                ])
                ->add('id', HiddenType::class, [
                    'mapped' => false,
                    'data' => $eleve->getId(),
                ])
                ->add('save' . $i, SubmitType::class, ['label' => 'Sauvegarder'])
                ->getForm();
            $form[$i]->handleRequest($request);

            $formview[$i] = $form[$i]->createView();
            $i++;
        }
        $imax = $i;

        for ($i = 1; $i < $imax; $i++) {
            if ($form[$i]->isSubmitted() && $form[$i]->isValid()) {

                if ($form[$i]->get('save' . $i)->isClicked()) {


                    $elevemodif = $repositoryElevesinter->findOneById(['id' => $form[$i]->get('id')->getData()]);
                    $elevemodif->setNom($form[$i]->get('nom')->getData());
                    $elevemodif->setPrenom($form[$i]->get('prenom')->getData());
                    $elevemodif->setCourriel($form[$i]->get('courriel')->getData());
                    $em->persist($elevemodif);
                    $em->flush();
                }

                return $this->redirectToRoute('modif_equipe', array('idequipe' => $idequipe));
            }


        }


        return $this->render('adminfichiers/modif_equipe.html.twig', [
            'formtab' => $formview, 'equipe' => $equipe]);
    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/mise_a_jour_table_professeurs_equipesadmin", name="maj_profsequipes")
     *
     */
    public function mise_a_jour_table_user(Request $request)//fonction provisoire pour le remplissage des tables profs et equipesadmin mai 2021
    {
        $em = $this->doctrine->getManager();

        $repositoryRne = $this->doctrine
            ->getManager()
            ->getRepository(Rne::class);
        $repositoryUser = $this->doctrine
            ->getManager()
            ->getRepository(User::class);
        $qb = $repositoryUser->createQueryBuilder('p');
        $qb1 = $repositoryUser->createQueryBuilder('u')
            ->andWhere($qb->expr()->like('u.roles', ':roles'))
            ->setParameter('roles', '%i:0;s:9:"ROLE_PROF";i:2;s:9:"ROLE_USER";%')
            ->orWhere($qb->expr()->like('u.roles', ':role'))
            ->setParameter('role', '%a:2:{i:0;s:9:"ROLE_PROF";i:1;s:9:"ROLE_USER";}%')
            ->addOrderBy('u.nom', 'ASC');
        $listeProfs = $qb1->getQuery()->getResult();
        //dd($qb1);


        foreach ($listeProfs as $prof) {

            $prof->setRneId($repositoryRne->findOneBy(['rne' => $prof->getRne()]));


            $em->persist($prof);
            $em->flush();


        }
        return $this->redirectToRoute('core_home');


    }

    /**
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Route("/secretariatadmin/youtube_remise_des prix", name="secretariatadmin_youtube_remise_des_prix")
     *
     */
    public function youtube_remise_des_prix(Request $request)

    {
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $editions = $repositoryEdition->findAll();
        $i = 0;
        foreach ($editions as $edition_) {
            $ids[$i] = $edition_->getId();
            $i++;
        }
        $id = max($ids);
        $edition = $repositoryEdition->findOneBy(['id' => $id]);


        $form = $this->createFormBuilder()
            ->add('lien', TextType::class, [
                'required' => false,
                'data' => $edition->getLienYoutube()

            ])
            ->add('valider', SubmitType::class);
        $Form = $form->getForm();
        $Form->handleRequest($request);
        if ($Form->isSubmitted() && $Form->isValid()) {

            $edition->setLienYoutube($Form->get('lien')->getData());

            $this->em->persist($edition);
            $this->em->flush();

            return $this->redirectToRoute('core_home');

        }
        return $this->render('core/lien_video.html.twig', array('form' => $Form->createView()));

    }


}