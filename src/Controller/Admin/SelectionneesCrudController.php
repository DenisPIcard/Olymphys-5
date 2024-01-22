<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use App\Entity\Centrescia;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Phrases;
use App\Entity\Prix;
use App\Entity\User;
use App\Entity\Visites;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use phpDocumentor\Reflection\Types\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Time;

class SelectionneesCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Equipes::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'lettre', 'titreProjet', 'ordre', 'heure', 'salle', 'total', 'classement', 'rang', 'nbNotes', 'sallesecours', 'code'])
            ->setPaginatorPageSize(25);
    }

    public function configureActions(Actions $actions): Actions
    {
        $attribHeuresSalles = Action::new('attrib_heures_salles', 'Attribuer les salles et heures', 'fa fa_array',)
            // if the route needs parameters, you can define them:
            // 1) using an array
            ->linkToRoute('attrib_heures_salles')
            ->createAsGlobalAction();
        $tableauExcel = Action::new('equipes_tableau_excel', 'Extraire un tableau Excel', 'fa fa_array')
            ->linkToRoute('equipes_sel_tableau_excel')
            ->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_INDEX, $attribHeuresSalles)
            ->add(Crud::PAGE_INDEX, $tableauExcel)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission('attrib_heures_salles', 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN');


    }

    public function configureFields(string $pageName): iterable
    {

        if ($_REQUEST['crudAction'] == 'edit') {
            $listeVisites = $this->doctrine->getRepository(Visites::class)->createQueryBuilder('v')
                ->andWhere('v.attribue =:value')
                ->setParameter('value', '0')
                ->orderBy('v.intitule', 'ASC')->getQuery()->getResult();
            $visite = $this->doctrine->getRepository(Equipes::class)->findOneBy(['id' => $_REQUEST['entityId']])->getVisite();
            if ($visite != null) {
                $listeVisites != null ? $listeVisites[count($listeVisites)] = $visite : $listeVisites[0] = $visite;
            }
            $visiteform = AssociationField::new('visite')->setFormType(EntityType::class)->setFormTypeOptions(['required' => false,
                'mapped' => true,
                'class' => Visites::class,
                'choices' => $listeVisites,
                'choice_label' => 'getIntitule',
                'placeholder' => $visite == null ? 'choisir la visite' : $visite->getIntitule()
            ]);

            $listeCadeaux = $this->doctrine->getRepository(Cadeaux::class)->createQueryBuilder('c')
                ->andWhere('c.attribue =:value')
                ->setParameter('value', '0')
                ->orderBy('c.raccourci', 'ASC')->getQuery()->getResult();
            $cadeau = $this->doctrine->getRepository(Equipes::class)->findOneBy(['id' => $_REQUEST['entityId']])->getCadeau();
            if ($cadeau != null) {
                $listeCadeaux !== null ? $listeCadeaux[count($listeCadeaux)] = $cadeau : $listeCadeaux[0] = $cadeau;
            }
            $cadeauform = AssociationField::new('cadeau')->setFormType(EntityType::class)->setFormTypeOptions(['required' => false,
                'mapped' => true,
                'class' => Cadeaux::class,
                'choices' => $listeCadeaux,
                'choice_label' => 'getRaccourci',
                'placeholder' => $cadeau == null ? 'Choisir le cadeau' : $cadeau->getRaccourci()
            ]);
            $listePrix = $this->doctrine->getRepository(Prix::class)->createQueryBuilder('p')
                ->andWhere('p.attribue =:value')
                ->setParameter('value', '0')
                ->orderBy('p.niveau', 'DESC')->getQuery()->getResult();
            $prix = $this->doctrine->getRepository(Equipes::class)->findOneBy(['id' => $_REQUEST['entityId']])->getPrix();
            if ($prix != null) {
                $listePrix !== null ? $listePrix[count($listePrix)] = $prix : $listePrix[0] = $prix;
            }
            $prixform = AssociationField::new('prix')->setFormType(EntityType::class)->setFormTypeOptions(['required' => false,
                'mapped' => true,
                'class' => Prix::class,
                'choices' => $listePrix,
                'choice_label' => 'getPrix',
                'placeholder' => $prix == null ? 'Choisir le prix' : $prix->getPrix()
            ]);
            $phrasesform = AssociationField::new('phrases')->setFormType(EntityType::class);

        };
        $lettre = TextField::new('equipeinter.lettre', 'lettre');
        $titreProjet = TextField::new('equipeinter.titreProjet', 'projet');
        $ordre = IntegerField::new('ordre');
        $heure = TextField::new('heure');
        $salle = TextField::new('salle');
        $total = IntegerField::new('total');
        $classement = TextField::new('classement');
        $rang = IntegerField::new('rang');
        $nbNotes = IntegerField::new('nbNotes');

        $visite = AssociationField::new('visite');
        $cadeau = AssociationField::new('cadeau');

        $phrases = CollectionField::new('phrases');

        $prix = AssociationField::new('prix');
        $infoequipe = TextField::new('equipeinter.infoequipe');

        $notess = AssociationField::new('notess');
        $observateur = TextField::new('observateur');
        $infoequipeLyceeAcademie = TextareaField::new('equipeinter.lyceeAcademie', 'académie');
        $infoequipeLycee = TextareaField::new('equipeinter.nomLycee', 'lycée');
        $infoequipeTitreProjet = TextareaField::new('equipeinter.TitreProjet');
        $id = IntegerField::new('id', 'ID');
        if ($this->adminContextProvider->getContext()->getRequest()->query->get('palmares') != null) {
            $param = $this->adminContextProvider->getContext()->getRequest()->query->get('palmares');
            $this->requestStack->getSession()->set('param', $param);
        }
        if (!isset($_REQUEST['palmares'])) {
            $_REQUEST['palmares'] = $this->requestStack->getSession()->get('param');
        }
        if ($_REQUEST['palmares'] == 1) {
            if (Crud::PAGE_INDEX === $pageName) {
                return [$lettre, $titreProjet, $infoequipeLyceeAcademie, $infoequipeLycee, $classement, $rang, $prix, $phrases, $cadeau, $visite];
            } elseif (Crud::PAGE_DETAIL === $pageName) {
                return [$id, $lettre, $titreProjet, $ordre, $classement, $visite, $cadeau, $phrases, $prix];
            } elseif (Crud::PAGE_NEW === $pageName) {
                return [$lettre, $titreProjet, $classement, $rang, $nbNotes, $visite, $cadeau, $phrases, $prix];
            } elseif (Crud::PAGE_EDIT === $pageName) {
                return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $visiteform, $cadeauform, $phrasesform, $prixform];
            }
        } elseif ($_REQUEST['palmares'] == 0) {
            if (Crud::PAGE_INDEX === $pageName) {
                return [$lettre, $titreProjet, $infoequipeLyceeAcademie, $infoequipeLycee, $heure, $salle, $ordre];
            } elseif (Crud::PAGE_DETAIL === $pageName) {
                return [$id, $lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $infoequipe, $notess];
            } elseif (Crud::PAGE_NEW === $pageName) {
                return [$lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $infoequipe, $notess];
            } elseif (Crud::PAGE_EDIT === $pageName) {
                return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $heure, $salle, $ordre];
            }
        }
    }


    public function index(AdminContext $context)
    {
        if ($context->getRequest()->query->get('palmares') != null) {
            $param = $context->getRequest()->query->get('palmares');
            $this->requestStack->getSession()->set('param', $param);
        } else {
            $_REQUEST['palmares'] = $this->requestStack->getSession()->get('param');
        }
        return parent::index($context); // TODO: Change the autogenerated stub
    }

    #[Route("/Admin/SelectionneesCrud/attrib_heures_salles", name: "attrib_heures_salles")]
    public function attrib_heure_salle(Request $request)
    {
        $form = $this->createFormBuilder()
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


            for ($row = 2; $row <= $highestRow; ++$row) {

                $lettre = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $equipe = $repositoryEquipes->createQueryBuilder('e')
                    ->leftJoin('e.equipeinter', 'eq')
                    ->andWhere('eq.lettre =:lettre')
                    ->setParameter('lettre', $lettre)
                    ->getQuery()->getSingleResult();
                $ordre = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $equipe->setOrdre($ordre);
                $heure = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $equipe->setHeure($heure);
                $salle = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $equipe->setSalle($salle);

                $em->persist($equipe);
                $em->flush();
            }

            return $this->redirectToRoute('admin');
        }
        return $this->render('/secretariatjury/charge_donnees_excel_equipes.html.twig', array('form' => $form->createView()));
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // le champ attribué des prix, visites et cadeaux n'est pas modifié par le persist de l'équipe, il faut le modifier à la main
        // solution créer un champ équipe dans prix, visites et cadeaux
        $equipe = $this->doctrine->getRepository(Equipes::class)->findOneBy(['id' => $entityInstance->getId()]);
        $prixInit = $equipe->getPrix();
        $prix = $entityInstance->getPrix();
        if ($prix != $prixInit) {


            $this->doctrine->getManager()->persist($prix);
            $this->doctrine->getManager()->persist($prixInit);
            $this->doctrine->getManager()->flush();
        }
        $visiteInit = $equipe->getVisite();
        $visite = $entityInstance->getVisite();
        if ($visite != $visiteInit) {

            $visite != null ? $visite->setAttribue(true) : $visite->setAttribue(false);
            $this->doctrine->getManager()->persist($visite);
            $this->doctrine->getManager()->persist($visiteInit);
            $this->doctrine->getManager()->flush();
        }
        $visiteInit = $equipe->getvisite();
        $visite = $entityInstance->getvisite();
        if ($visite != $visiteInit) {

            $visite != null ? $visite->setAttribue(true) : $visite->setAttribue(false);
            $this->doctrine->getManager()->persist($visite);
            $this->doctrine->getManager()->persist($visiteInit);
            $this->doctrine->getManager()->flush();
        }
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    #[Route("/Admin/SelectionneesCrud/equipes_tableau_excel", name: "equipes_sel_tableau_excel")]
    public function equipestableauexcel()
    {
        $repositoryEquipes = $this->doctrine->getRepository(Equipes::class);
        $edition = $this->requestStack->getSession()->get('edition');
        $liste_equipes = $repositoryEquipes->findAll();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CN - " . $edition->getEd() . "e -Tableau destiné au comité")
            ->setSubject("Tableau destiné au comité")
            ->setDescription("Office 2007 XLSX liste des équipes")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }
        $ligne = 1;
        $sheet->setCellValue('A' . $ligne, 'Lettre')
            ->setCellValue('B' . $ligne, 'nom équipe')
            ->setCellValue('C' . $ligne, 'Nom du lycée')
            ->setCellValue('D' . $ligne, 'Commune')
            ->setCellValue('E' . $ligne, 'Académie')
            ->setCellValue('F' . $ligne, 'classement')
            ->setCellValue('G' . $ligne, 'prix')
            ->setCellValue('H' . $ligne, 'cadeau')
            ->setCellValue('I' . $ligne, 'visite')
            ->setCellValue('J' . $ligne, 'rang')
            ->setCellValue('K' . $ligne, 'heure')
            ->setCellValue('L' . $ligne, 'salle');;
        $ligne += 1;
        foreach ($liste_equipes as $equipe) {
            $sheet->setCellValue('A' . $ligne, $equipe->getEquipeinter()->getLettre())
                ->setCellValue('B' . $ligne, $equipe->getEquipeinter()->getTitreProjet())
                ->setCellValue('C' . $ligne, $equipe->getEquipeinter()->getUaiId()->getNom())
                ->setCellValue('D' . $ligne, $equipe->getEquipeinter()->getUaiId()->getCommune())
                ->setCellValue('E' . $ligne, $equipe->getEquipeinter()->getUaiId()->getAcademie())
                ->setCellValue('F' . $ligne, $equipe->getClassement())
                ->setCellValue('G' . $ligne, $equipe->getPrix())
                ->setCellValue('H' . $ligne, $equipe->getCadeau())
                ->setCellValue('I' . $ligne, $equipe->getVisite())
                ->setCellValue('J' . $ligne, $equipe->getRang())
                ->setCellValue('K' . $ligne, $equipe->getHeure())
                ->setCellValue('L' . $ligne, $equipe->getSalle());
            $ligne += 1;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="equipes.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        //$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        // $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');


    }

}
