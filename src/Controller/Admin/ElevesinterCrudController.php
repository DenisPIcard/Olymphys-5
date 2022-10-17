<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CustomEditionFilter;
use App\Controller\Admin\Filter\CustomEquipeFilter;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\UnicodeString;

class ElevesinterCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine, AdminContextProvider $adminContextProvider)
    {
        $this->requestStack = $requestStack;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Elevesinter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $session = $this->requestStack->getSession();
        $exp = new UnicodeString('<sup>e</sup>');
        $repositoryEdition = $this->doctrine->getManager()->getRepository(Edition::class);
        $repositoryEquipe = $this->doctrine->getManager()->getRepository(Equipesadmin::class);
        $editionEd = $session->get('edition')->getEd();
        $equipeTitre = '';
        $crud->setPageTitle('index', 'Liste des élèves de la ' . $editionEd . $exp . ' édition ');
        if (isset($_REQUEST['filters']['edition'])) {
            $editionId = $_REQUEST['filters']['edition'];
            $editionEd = $repositoryEdition->findOneBy(['id' => $editionId]);
            $crud->setPageTitle('index', 'Liste des élèves de la ' . $editionEd . $exp . ' édition ');
        }
        if (isset($_REQUEST['filters']['equipe'])) {
            $equipe = $repositoryEquipe->findOneBy(['id' => $_REQUEST['filters']['equipe']['value']]);
            $equipeTitre = 'de l\'équipe ' . $equipe;

            $crud->setPageTitle('index', 'Liste des élèves ' . $equipeTitre);

        }

        if ($_REQUEST['crudAction'] == 'edit') {
            $idEleve = $_REQUEST['entityId'];
            $eleve = $this->doctrine->getRepository(Elevesinter::class)->findOneBy(['id' => $idEleve]);
            $crud->setPageTitle('edit', 'Eleve ' . $eleve->getPrenom() . ' ' . $eleve->getNom());


        }

        return $crud
            ->setSearchFields(['nom', 'prenom', 'courriel', 'equipe.id', 'equipe.edition', 'equipe.numero', 'equipe.titreProjet', 'equipe.lettre'])
            ->overrideTemplate('layout', 'bundles/EasyAdminBundle/list_eleves.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomEquipeFilter::new('equipe'))
            ->add(CustomEditionFilter:: new('edition'));


    }

    public function configureActions(Actions $actions): Actions
    {
        $session = $this->requestStack->getSession();
        $equipeId = 'na';
        $repositoryEquipe = $this->doctrine->getManager()->getRepository(Equipesadmin::class);
        $editionId = $session->get('edition')->getId();
        $equipeId = 'na';

        if (isset($_REQUEST['filters']['edition'])) {
            $editionId = $_REQUEST['filters']['edition'];
            $equipeId = 'na';
        }
        if (isset($_REQUEST['filters']['equipe'])) {
            $equipeId = $_REQUEST['filters']['equipe']['value'];
            $editionId = $repositoryEquipe->findOneBy(['id' => $equipeId])->getEdition()->getId();
        }

        $tableauexcel = Action::new('eleves_tableau_excel', 'Créer un tableau excel des élèves', 'fas fa_array',)
            ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId])
            ->createAsGlobalAction();
//->displayAsButton();
//->setHtmlAttributes(['data-ideditionequipe' =>  $editionId.'-'.$equipeId, 'target' => '_blank'])
//->setCssClass('btn btn-alert action-eleves_tableau_excel');
        $tableauexcelnonsel = Action::new('eleves_tableau_excel', 'Créer un tableau excel des élèves non sélectionnés', 'fas fa_array',)
            ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId . '-ns'])
            ->createAsGlobalAction();
        $elevessel = Action::new('eleves_tableau_excel_sel', 'Créer un tableau excel des élèves sélectionnés', 'fas fa_array',)
            ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId . '-s'])
            ->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $tableauexcelnonsel)
            ->add(Crud::PAGE_INDEX, $elevessel)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);

    }

    public function configureFields(string $pageName): iterable
    {

        $listEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $this->requestStack->getSession()->get('edition'))
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();
        $nom = TextField::new('nom');
        $prenom = TextField::new('prenom');
        $genre = TextField::new('genre');
        $courriel = TextField::new('courriel');
        $equipe = AssociationField::new('equipe')->setFormTypeOptions(['choices' => $listEquipes]);;
        $id = IntegerField::new('id', 'ID');
        $numsite = IntegerField::new('numsite');
        $classe = TextField::new('classe');
        $autorisationphotos = AssociationField::new('autorisationphotos');

        $equipeNumero = IntegerField::new('equipe.numero', ' Numéro équipe');
        $equipeTitreProjet = TextareaField::new('equipe.titreProjet', 'Projet');
        $equipeLyceeLocalite = TextareaField::new('equipe.lyceeLocalite', 'ville');
        $equipeEdition = TextareaField::new('equipe.edition', 'Edition');
        $autorisationphotosFichier = AssociationField::new('autorisationphotos', 'Autorisation photos');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$equipeEdition, $nom, $prenom, $genre, $courriel, $equipeNumero, $equipeTitreProjet, $equipeLyceeLocalite, $autorisationphotosFichier];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$equipeEdition, $nom, $prenom, $genre, $classe, $courriel, $equipe, $autorisationphotos];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$nom, $prenom, $genre, $courriel, $equipe];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$nom, $prenom, $genre, $classe, $courriel, $equipe];
        }
        return [$equipeEdition, $nom, $prenom, $genre, $courriel, $equipeNumero, $equipeTitreProjet, $equipeLyceeLocalite, $autorisationphotosFichier];

    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $session = $this->requestStack->getSession();
        $context = $this->adminContextProvider->getContext();

        $repositoryEdition = $this->doctrine->getManager()->getRepository(Edition::class);
        $repositoryEquipe = $this->doctrine->getManager()->getRepository(Equipesadmin::class);
        if ($context->getRequest()->query->get('filters') == null) {

            $qb = $this->doctrine->getRepository(Elevesinter::class)->createQueryBuilder('e')
                ->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $session->get('edition'))
                ->orderBy('eq.numero', 'ASC');

        } else {
            if (isset($context->getRequest()->query->get('filters')['equipe'])) {
                $idEquipe = $context->getRequest()->query->get('filters')['equipe']['value'];
                $equipe = $repositoryEquipe->findOneBy(['id' => $idEquipe]);
                $session->set('titrepage', ' Edition ' . $equipe);
            }

            $qb = $this->doctrine->getRepository(Elevesinter::class)->createQueryBuilder('e');
        }
        if (isset($context->getRequest()->query->get('filters')['edition'])) {
            $idEdition = $context->getRequest()->query->get('filters')['edition'];
            $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
            if (!isset($context->getRequest()->query->get('filters')['equipe'])) {
                $session->set('titrepage', $edition . '<sup>e</sup>' . ' édition');
            }


            $qb = $this->doctrine->getRepository(Elevesinter::class)->createQueryBuilder('e')
                ->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->setParameter('edition', $edition)
                ->orderBy('eq.numero', 'ASC');

        }

        return $qb;
    }

    /**
     * @Route("/Admin/ElevesinteradminCrud/eleves_tableau_excel,{ideditionequipe}", name="eleves_tableau_excel")
     */
    public function elevestableauexcel($ideditionequipe)
    {
        $idedition = explode('-', $ideditionequipe)[0];
        $idequipe = explode('-', $ideditionequipe)[1];


        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);
        $queryBuilder = $repositoryEleves->createQueryBuilder('e');
        if ($idequipe == 0) {

            $queryBuilder->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $edition)
                ->orderBy('eq.numero', 'ASC');
            if (isset(explode('-', $ideditionequipe)[2])) {
                explode('-', $ideditionequipe)[2] == 'ns' ? $queryBuilder->andWhere('eq.selectionnee = FALSE') : $queryBuilder->andWhere('eq.selectionnee = TRUE');

            }

        }
        if ($idequipe != 0) {
            $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
            $queryBuilder
                ->andWhere('e.equipe =:equipe')
                ->setParameter('equipe', $equipe);
        }
        $liste_eleves = $queryBuilder->getQuery()->getResult();

        $nombreFilles = count($queryBuilder->andWhere('e.genre =:genre')
            ->setParameter('genre', 'F')
            ->getQuery()->getResult());
        $nombreGarcons = count($queryBuilder->andWhere('e.genre =:genre')
            ->setParameter('genre', 'M')
            ->getQuery()->getResult());
//dd($edition);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CN  " . $edition->getEd() . "e édition -Tableau destiné au comité")
            ->setSubject("Tableau destiné au comité")
            ->setDescription("Office 2007 XLSX liste des éleves")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();


        $ligne = 1;
        $sheet
            ->setCellValue('A' . $ligne, 'Nb filles :' . $nombreFilles)
            ->setCellValue('D' . $ligne, 'Nb garçons :' . $nombreGarcons);
        $ligne += 1;
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        $sheet
            ->setCellValue('A' . $ligne, 'Edition')
            ->setCellValue('B' . $ligne, 'Numero equipe')
            ->setCellValue('C' . $ligne, 'Lettre equipe')
            ->setCellValue('D' . $ligne, 'Prenom')
            ->setCellValue('E' . $ligne, 'Nom')
            ->setCellValue('F' . $ligne, 'Genre')
            ->setCellValue('G' . $ligne, 'Courriel')
            ->setCellValue('H' . $ligne, 'Equipe')
            ->setCellValue('I' . $ligne, 'Nom du lycée')
            ->setCellValue('J' . $ligne, 'Commune')
            ->setCellValue('K' . $ligne, 'Académie');

        $ligne += 1;

        foreach ($liste_eleves as $eleve) {
            $rne = $eleve->getEquipe()->getRneId();

            $sheet->setCellValue('A' . $ligne, $eleve->getEquipe()->getEdition())
                ->setCellValue('B' . $ligne, $eleve->getEquipe()->getNumero());
            if ($eleve->getEquipe()->getLettre() != null) {
                $sheet->setCellValue('C' . $ligne, $eleve->getEquipe()->getLettre());
            }
            $sheet->setCellValue('D' . $ligne, $eleve->getPrenom())
                ->setCellValue('E' . $ligne, $eleve->getNom())
                ->setCellValue('F' . $ligne, $eleve->getGenre())
                ->setCellValue('G' . $ligne, $eleve->getCourriel())
                ->setCellValue('H' . $ligne, $eleve->getEquipe())
                ->setCellValue('I' . $ligne, $rne->getNom())
                ->setCellValue('J' . $ligne, $rne->getCommune())
                ->setCellValue('K' . $ligne, $rne->getAcademie());

            $ligne += 1;
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="eleves.xls"');
        header('Cache-Control: max-age=0');

        $writer = new Xls($spreadsheet);
//$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
//$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
// $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');

    }
}