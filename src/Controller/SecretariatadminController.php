<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Jures;
use App\Entity\Uai;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineExtensions\Query\Mysql\Time;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/charge_uai", name: "secretariatadmin_charge_uai")]
    public function charge_uai(Request $request): RedirectResponse|Response
    {
        $defaultData = ['message' => 'Charger le fichier des élèves '];
        $form = $this->createFormBuilder($defaultData)
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();

        $repositoryUai = $this
            ->doctrine
            ->getManager()
            ->getRepository(Uai::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $worksheet->getHighestRow();

            $em = $this->doctrine->getManager();

            for ($row = 3; $row <= $highestRow; ++$row) {

                $value = $worksheet->getCell('A' . $row)->getValue();//On lit le uai
                $uai = $repositoryUai->findOneByUai($value);//On vérifie si  cet uai est déjà dans la base
                if (!$uai) { // si le uai n'existe pas, on le crée
                    $uai = new Uai();
                    //sinon on garde les précédentes données
                    //dd($value);
                    $uai->setUai($value);
                    $value = $worksheet->getCell('T' . $row)->getValue();
                    $uai->setNature($value);
                    //$value = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                    //$uai->setSigle($value);
                    $value = $worksheet->getCell('K' . $row)->getValue();
                    $uai->setCommune($value);
                    $value = $worksheet->getCell('AC' . $row)->getValue();
                    $uai->setAcademie($value);
                    //$value = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                    $uai->setPays('France');
                    $value = $worksheet->getCell('W' . $row)->getValue();
                    $uai->setDepartement($value);
                    $value = $worksheet->getCell('C' . $row)->getValue();
                    $uai->setDenominationPrincipale($value);
                    $value = $worksheet->getCell('B' . $row)->getValue();
                    $uai->setAppellationOfficielle($value);
                    $value = $worksheet->getCell('D' . $row)->getValue();
                    $uai->setNom(ucwords(strtolower($value)));
                    $value = $worksheet->getCell('F' . $row)->getValue();
                    $uai->setAdresse($value);
                    $value = $worksheet->getCell('H' . $row)->getValue();
                    $uai->setBoitePostale($value);
                    $value = $worksheet->getCell('Z' . $row)->getValue();
                    $uai->setCodePostal($value);
                    $value = $worksheet->getCell('J' . $row)->getValue();
                    $uai->setAcheminement($value);
                    $value = $worksheet->getCell('L' . $row)->getValue();
                    $uai->setCoordonneeX($value);
                    $value = $worksheet->getCell('M' . $row)->getValue();
                    $uai->setCoordonneeY($value);
                    $this->em->persist($uai);
                    $this->em->flush();
                }
            }
            return $this->redirectToRoute('core_home');
        }
        $content = $this
            ->renderView('secretariatadmin\charge_donnees_excel.html.twig', array('form' => $form->createView(), 'titre' => 'Enregistrer le uai'));
        return new Response($content);

    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/charge_user", name: "secretariatadmin_charge_user")]
    public function charge_user(Request $request): RedirectResponse|Response
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


                    $value = $worksheet->getCellByColumnAndRow(8, $row)->getValue(); //uai
                    $user->setuai($value);
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


    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/cree_equipes", name: "secretariatadmin_cree_equipes")]
    public function cree_equipes(Request $request): RedirectResponse|Response
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
                $equipe->setCouleur(0);
                $date = new DateTime('now');
                $heure = '00:00';
                $equipe->setHeure($heure);
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


    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/charge_equipe_id_uai", name: "secretariatadmin_charge_equipe_id_uai")]
    public function charge_equipe_id_uai(Request $request): RedirectResponse
    {
        $repositoryEquipes = $this->doctrine
            ->getManager()
            ->getRepository(Equipesadmin::class);
        $repositoryUai = $this->doctrine
            ->getManager()
            ->getRepository(Uai::class);
        $equipes = $repositoryEquipes->findAll();
        $em = $this->doctrine->getManager();
        $uais = $repositoryUai->findAll();
        foreach ($equipes as $equipe) {
            foreach ($uais as $uai) {
                if ($uai->getUai() == $equipe->getUai()) {
                    $equipe->setUaiId($uai);
                }
            }
            $em->persist($equipe);
            $em->flush();

        }
        return $this->redirectToRoute('core_home');


    }

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/set_editon_equipe", name: "secretariatadmin_set_editon_equipe")]
    public function set_edition_equipe(Request $request): RedirectResponse
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

    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/modif_equipe,{idequipe}", name: "modif_equipe")]
    public function modif_equipe(Request $request, $idequipe): RedirectResponse|Response
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


    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route("/secretariatadmin/youtube_remise_des prix", name: "secretariatadmin_youtube_remise_des_prix")]
    public function youtube_remise_des_prix(Request $request): RedirectResponse|Response

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