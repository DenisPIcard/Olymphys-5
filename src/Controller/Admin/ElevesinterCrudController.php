<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CustomEditionFilter;
use App\Controller\Admin\Filter\CustomEquipeFilter;
use App\Controller\Admin\Filter\CustomEquipeSelectionnesFilter;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use DateTime;
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
use Fpdf\Fpdf;

use Knp\Bundle\SnappyBundle\KnpSnappyBundle;

//use Knp\Snappy\Pdf;
//use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\UnicodeString;
use ZipArchive;

//use Dompdf\Dompdf;

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
        if (new DateTime('now') < $session->get('dateouverturesite')) {
            $editionEd = $editionEd - 1;
        }
        $edition = $session->get('edition');
        $editionEd = $edition->getEd();
        if (new DateTime('now') < $session->get('edition')->getDateouverturesite()) {
            $edition = $repositoryEdition->findOneBy(['ed' => $edition->getEd() - 1]);
            $editionEd = $edition->getEd();
        }
        $equipeTitre = '';
        $crud->setPageTitle('index', 'Liste des élèves de la ' . $editionEd . $exp . ' édition ');
        if (isset($_REQUEST['filters']['edition'])) {
            $editionId = $_REQUEST['filters']['edition'];
            $editionEd = $repositoryEdition->findOneBy(['id' => $editionId]);
            $crud->setPageTitle('index', 'Liste des élèves de la ' . $editionEd . $exp . ' édition ');
        }
        if (isset($_REQUEST['filters']['equipe'])) {
            $equipe = $repositoryEquipe->findOneBy(['id' => $_REQUEST['filters']['equipe']]);
            $equipeTitre = 'de l\'équipe ' . $equipe;

            $crud->setPageTitle('index', 'Liste des élèves ' . $equipeTitre);

        }
        if (isset($_REQUEST['filters']['selectionnes'])) {
            $selectionnes = $_REQUEST['filters']['selectionnes'];
            $selectionnes == true ? $qualite = 'sélectionnés' : $qualite = 'non-sélectionnés';
            $equipeTitre = $qualite;

            $crud->setPageTitle('index', 'Liste des élèves ' . $equipeTitre);

        }

        if ($_REQUEST['crudAction'] == 'edit') {
            $idEleve = $_REQUEST['entityId'];
            $eleve = $this->doctrine->getRepository(Elevesinter::class)->findOneBy(['id' => $idEleve]);
            $crud->setPageTitle('edit', 'Eleve ' . $eleve->getPrenom() . ' ' . $eleve->getNom());


        }

        return $crud
            //->setSearchFields(['nom', 'prenom', 'courriel', 'equipe.id', 'equipe.edition', 'equipe.numero', 'equipe.titreProjet', 'equipe.lettre'])
            ->overrideTemplate('layout', 'bundles/EasyAdminBundle/list_eleves.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomEquipeFilter::new('equipe'))
            ->add(CustomEditionFilter::new('edition'))
            ->add(customEquipeSelectionnesFilter::new('selectionnes'));


    }

    public function configureActions(Actions $actions): Actions
    {
        $session = $this->requestStack->getSession();
        $equipeId = 'na';
        $repositoryEquipe = $this->doctrine->getRepository(Equipesadmin::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);

        $edition = $session->get('edition');
        $editionId = $edition->getId();
        $editionN1 = $session->get('editionN1');
        $date = new \DateTime('now');

        if ($date < $session->get('edition')->getDateouverturesite() and $date > $editionN1->getConcoursCn()) {
            $edition = $repositoryEdition->findOneBy(['ed' => $edition->getEd() - 1]);
            $editionId = $repositoryEdition->findOneBy(['ed' => $edition->getEd()])->getId();

        }
        $equipeId = 'na';


        if (isset($_REQUEST['filters']['equipe'])) {
            $equipeId = $_REQUEST['filters']['equipe'];
            $editionId = $repositoryEquipe->findOneBy(['id' => $equipeId])->getEdition()->getId();

            $tableauexcelelevesequipe = Action::new('eleves_tableau_excel_equipe', 'Créer un tableau excel de ces élèves', 'fas fa_array',)
                ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId])
                ->createAsGlobalAction();
            $actions->add(Crud::PAGE_INDEX, $tableauexcelelevesequipe);
        }

        if (((!isset($_REQUEST['filters'])) or (isset($_REQUEST['filters']['edition']))) or (isset($_REQUEST['filters']['selectionnes'])) and (!isset($_REQUEST['filters']['equipe']))) {
            if (new DateTime('now') < $session->get('dateouverturesite')) {
                $editionId = $repositoryEdition->findOneBy(['ed' => $session->get('edition')->getEd() - 1])->getId();
            }
            if (isset($_REQUEST['filters']['edition'])) {
                $editionId = $_REQUEST['filters']['edition'];
                //$editionEd = $this->doctrine->getRepository(Edition::class)->findOneBy(['id' => $editionId]);

            }
            $attestationsEleves = Action::new('Attestions_eleves', 'Créer les attestations')->linkToRoute('attestations_eleves_pdf', ['ideditionequipe' => $editionId . '-' . $equipeId . '-ns'])
                ->createAsGlobalAction();
            $attestationsElevesNat = Action::new('Attestions_eleves_nat', 'Créer les attestations des élèves sélectionnés')->linkToRoute('attestations_eleves_nat_pdf', ['ideditionequipe' => $editionId . '-' . $equipeId . '-sel'])
                ->createAsGlobalAction();
            $tableauexcelnonsel = Action::new('eleves_tableau_excel', 'Créer un tableau excel des élèves non sélectionnés', 'fas fa_array',)
                ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId . '-ns'])
                ->createAsGlobalAction();
            $tableauexceleleves = Action::new('eleves_tableau_excel_tous', 'Créer un tableau excel des tous les élèves', 'fas fa_array',)
                ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId])
                ->createAsGlobalAction();
            $elevessel = Action::new('eleves_tableau_excel_sel', 'Créer un tableau excel des élèves sélectionnés', 'fas fa_array',)
                ->linkToRoute('eleves_tableau_excel', ['ideditionequipe' => $editionId . '-' . $equipeId . '-s'])
                ->createAsGlobalAction();
            $actions->add(Crud::PAGE_INDEX, $tableauexcelnonsel)
                ->add(Crud::PAGE_INDEX, $attestationsEleves)
                ->add(Crud::PAGE_INDEX, $attestationsElevesNat)
                ->add(Crud::PAGE_INDEX, $tableauexceleleves)
                ->add(Crud::PAGE_INDEX, $elevessel);


        }

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $edition = $this->requestStack->getSession()->get('edition');
        if (new DateTime('now') < $this->requestStack->getSession()->get('edition')->getDateouverturesite()) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => $edition->getEd() - 1]);

        }
        $listEquipes = $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
            ->andWhere('e.edition =:edition')
            ->setParameter('edition', $edition)
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();
        /* $nom = TextField::new('nom')->setSortable(true);
         $prenom = TextField::new('prenom')->setSortable(true);
         $genre = TextField::new('genre');
         $courriel = TextField::new('courriel');
         $equipe = AssociationField::new('equipe')->setFormTypeOptions(['choices' => $listEquipes])->setSortable(true);;
         $id = IntegerField::new('id', 'ID');
         $numsite = IntegerField::new('numsite');
         $classe = TextField::new('classe');
         $autorisationphotos = AssociationField::new('autorisationphotos');

         $equipeNumero = IntegerField::new('equipe.numero', ' Numéro équipe')->setSortable(true);
         $equipeTitreProjet = TextareaField::new('equipe.titreProjet', 'Projet')->setSortable(true);
         $equipeLyceeLocalite = TextareaField::new('equipe.lyceeLocalite', 'ville')->setSortable(true);
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
         }*/
        //return [$equipeEdition, $nom, $prenom, $genre, $courriel, $equipeNumero, $equipeTitreProjet, $equipeLyceeLocalite, $autorisationphotosFichier];
        return [
            yield TextField::new('equipe.edition', 'Edition'),
            yield TextField::new('nom')->setSortable(true),
            yield TextField::new('prenom')->setSortable(true),
            yield TextField::new('genre'),
            yield TextField::new('classe')->hideOnIndex()->hideOnForm(),
            yield AssociationField::new('equipe')->setFormTypeOptions(['choices' => $listEquipes])->setSortable(true)->hideOnIndex(),
            yield IntegerField::new('equipe.numero', ' Numéro équipe')->setSortable(true),
            yield TextareaField::new('equipe.titreProjet', 'Projet')->setSortable(true),
            yield TextareaField::new('equipe.lyceeLocalite', 'ville')->setSortable(true),
            yield AssociationField::new('autorisationphotos')->onlyOnDetail(),
            yield AssociationField::new('autorisationphotos', 'Autorisation photos')->onlyOnIndex()
        ];
    }

    /*public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $session = $this->requestStack->getSession();
        $context = $this->adminContextProvider->getContext();
        $edition=$session->get('edition');
        $repositoryEdition = $this->doctrine->getManager()->getRepository(Edition::class);
        $repositoryEquipe = $this->doctrine->getManager()->getRepository(Equipesadmin::class);
        if(date('now')<$session->get('dateouverturesite')){
            $edition=$repositoryEdition->findOneBy(['ed'=>$edition->getEd()-1]);
        }
        $qb = $this->doctrine->getRepository(Elevesinter::class)->createQueryBuilder('e')
                            ->leftJoin('e.equipe', 'eq');
        if (!isset($_REQUEST['filters'])) {
            $qb->andWhere('eq.edition =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('eq.inscrite =:value')
                ->setParameter('value','1');



        } else {

            if (isset($_REQUEST['filters']['equipe'])) {
                $idEquipe = $_REQUEST['filters']['equipe'];
                $equipe = $repositoryEquipe->findOneBy(['id' => $idEquipe]);

                $session->set('titrepage', ' Edition ' . $equipe);
                $qb ->andWhere('e.equipe =:equipe')
                    ->setParameter('equipe',$equipe);
                }
            if (isset($_REQUEST['filters']['edition'])) {
                $editionId = $_REQUEST['filters']['edition'];
                $editioned = $repositoryEdition->findOneBy(['id' => $editionId]);
                $qb->leftJoin('e.equipe', 'eq')
                    ->andWhere('eq.edition =:edition')
                    ->setParameter('edition', $editioned)
                    ->orderBy('eq.numero', 'ASC');;
            }
        }
        if (isset($_REQUEST['sort'])){
            $sort=$_REQUEST['sort'];
            if (key($sort)=='nom'){
                $qb->addOrderBy('e.nom', $sort['nom']);
            }
            if (key($sort)=='prenom'){
                $qb->addOrderBy('e.prenom', $sort['prenom']);
            }
            if (key($sort)=='autorisationphotos'){
                $qb->leftJoin('e.autorisationphotos','f')
                    ->addOrderBy('f.fichier', $sort['autorisationphotos']);
            }
            if (key($sort)=='equipe.numero'){
                $qb->addOrderBy('eq.numero', $sort['equipe.numero']);
            }
            if (key($sort)=='equipe.lyceeLocalite'){
                $qb->addOrderBy('eq.lyceeLocalite', $sort['equipe.lyceeLocalite']);
            }
        }
        else{
            $qb->orderBy('eq.numero', 'ASC');
        }
       return $qb;
    }*/
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $repositoryEdition = $this->doctrine->getManager()->getRepository(Edition::class);
        $repositoryEquipesadmin = $this->doctrine->getManager()->getRepository(Equipesadmin::class);
        $session = $this->requestStack->getSession();
        $edition = $session->get('edition');
        $response = $this->doctrine->getRepository(Elevesinter::class)->createQueryBuilder('e');
        if (new DateTime('now') < $session->get('edition')->getDateouverturesite()) {
            $edition = $repositoryEdition->findOneBy(['ed' => $edition->getEd() - 1]);
        }
        if (!isset($_REQUEST['filters'])) {
            $response->join('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $edition)
                ->addOrderBy('eq.numero', 'ASC');
        }
        if (isset($_REQUEST['filters'])) {
            if (isset($_REQUEST['filters']['equipe'])) {
                $equipeId = $_REQUEST['filters']['equipe'];

                $equipe = $repositoryEquipesadmin->findOneBy(['id' => $equipeId]);
                $response->andWhere('e.equipe =:equipe')
                    ->setParameter('equipe', $equipe);
            }

            if (isset($_REQUEST['filters']['edition'])) {
                $idEdition = $_REQUEST['filters']['edition'];
                $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
                $response->join('e.equipe', 'eq')
                    ->andWhere('eq.edition =:edition')
                    ->andWhere('eq.inscrite = TRUE')
                    ->setParameter('edition', $edition);
            }
            if (isset($_REQUEST['filters']['selectionnes'])) {

                $selectionne = $_REQUEST['filters']['selectionnes'];

                $response->join('e.equipe', 'eq')
                    ->andWhere('eq.edition =:edition')
                    ->andWhere('eq.selectionnee =:selectionnee')
                    ->andWhere('eq.inscrite = TRUE')
                    ->setParameter('selectionnee', $selectionne)
                    ->setParameter('edition', $edition)
                    ->addOrderBy('eq.numero', 'ASC');
            }

        }

        if (isset($_REQUEST['sort'])) {

            $response->resetDQLPart('orderBy');
            $sort = $_REQUEST['sort'];
            if (key($sort) == 'nom') {
                $response->addOrderBy('e.nom', $sort['nom']);
            }
            if (key($sort) == 'prenom') {
                $response->addOrderBy('e.prenom', $sort['prenom']);
            }
            if (key($sort) == 'genre') {
                $response->addOrderBy('e.genre', $sort['genre']);

            }
            if (key($sort) == 'equipe.numero') {
                $response->addOrderBy('eq.numero', $sort['equipe.numero']);
            }
            if (key($sort) == 'equipe.lyceeLocalite') {
                $response
                    ->addOrderBy('eq.lyceeLocalite', $sort['equipe.lyceeLocalite']);
            }
        }

        return $response;
        //return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters); // TODO: Change the autogenerated stub
    }

    #[Route("/Admin/ElevesinteradminCrud/eleves_tableau_excel,{ideditionequipe}", name: "eleves_tableau_excel")]
    public function elevestableauexcel($ideditionequipe)
    {
        $idedition = explode('-', $ideditionequipe)[0];
        $idequipe = explode('-', $ideditionequipe)[1];


        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);

        $queryBuilder = $repositoryEleves->createQueryBuilder('e');
        if ($idequipe == 'na') {

            $queryBuilder->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $edition)
                ->orderBy('eq.numero', 'ASC');
            if (isset(explode('-', $ideditionequipe)[2])) {
                explode('-', $ideditionequipe)[2] == 'ns' ? $queryBuilder->andWhere('eq.selectionnee = 0') : $queryBuilder->andWhere('eq.selectionnee = 1');

            }

        }
        if ($idequipe != 'na') {
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
            ->setCellValue('K' . $ligne, 'Académie')
            ->setCellValue('L' . $ligne, 'Centre');

        $ligne += 1;

        foreach ($liste_eleves as $eleve) {
            $uai = $eleve->getEquipe()->getUaiId();

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
                ->setCellValue('I' . $ligne, $uai->getNom())
                ->setCellValue('J' . $ligne, $uai->getCommune())
                ->setCellValue('K' . $ligne, $uai->getAcademie())
                ->setCellValue('L' . $ligne, $eleve->getEquipe()->getCentre()->getCentre());

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

    #[Route("/Admin/ElevesinteradminCrud/attestationsEleves,{ideditionequipe}", name: "attestations_eleves")]
    public function attestationsEleves($ideditionequipe)
    {
        $slugger = new AsciiSlugger();
        $idedition = explode('-', $ideditionequipe)[0];
        $idequipe = explode('-', $ideditionequipe)[1];
        $exp = new UnicodeString('e');
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);
        $queryBuilder = $repositoryEleves->createQueryBuilder('e');
        if ($idequipe == 'na') {

            $queryBuilder->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $edition)
                ->orderBy('eq.centre', 'ASC')
                ->addOrderBy('eq.numero', 'ASC');
            if (isset(explode('-', $ideditionequipe)[2])) {
                explode('-', $ideditionequipe)[2] == 'ns' ? $queryBuilder->andWhere('eq.selectionnee = 0') : $queryBuilder->andWhere('eq.selectionnee = 1');

            }

        }
        if ($idequipe != 'na') {
            $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
            $queryBuilder
                ->andWhere('e.equipe =:equipe')
                ->setParameter('equipe', $equipe);
        }
        $liste_eleves = $queryBuilder->getQuery()->getResult();
        $zipFile = new ZipArchive();
        $now = new DateTime('now');
        $fileNameZip = $edition->getEd() . '-Attestations_eleves_non_selectionnes-' . $now->format('d-m-Y\-His');
        if ($zipFile->open($fileNameZip, ZipArchive::CREATE) === TRUE) {
            foreach ($liste_eleves as $eleve) {
                $phpWord = new  PhpWord();
                $phpWord->setDefaultFontName('Verdana');
                $section = $phpWord->addSection();
                $src = 'odpf/odpf-images/site-logo-150x43.png';
                $section->addImage($src, array(
                    'width' => '150',
                    'positioning' => 'absolute',
                    'posHorizontalRel' => 'margin',
                    'posVerticalRel' => 'line',
                ), false, 'logo');
                $section->addTextBreak(3);
                $section->addText('Paris le ' . $this->date_in_french($this->requestStack->getSession()->get('edition')->getConcourscia()->format('Y-m-d')), ['size' => 14,], ['align' => 'right']);
                $section->addTextBreak(4, ['size' => 14]);
                $section->addText('Attestation de participation aux', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                $textrun = $section->addTextRun(['align' => 'center']);
                $textrun->addText('31', ['size' => 18, 'bold' => true,]);
                $textrun->addText('e', ['size' => 18, 'bold' => true, 'superScript' => true]);
                $textrun->addText(' Olympiades de Physique France', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                $section->addTextBreak(3, ['bold' => true, 'size' => 18]);
                $section->addText('Le Comité national des Olympiades de Physique France certifie que :', ['size' => 14,], ['align' => 'left']);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun2 = $section->addTextRun(['align' => 'center']);
                $textrun2->addText('l’élève ', ['size' => 14,]);
                $textrun2->addText($eleve->getPrenom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                $textrun2->addText(' ', ['size' => 14,]);
                $textrun2->addText($eleve->getNom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun3 = $section->addTextRun(['align' => 'center']);
                $textrun3->addText('du lycée ', ['size' => 14]);
                $textrun3->addText($eleve->getequipe()->getNomLycee(), ['size' => 14]);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun4 = $section->addTextRun(['align' => 'center']);
                $textrun4->addText('à ', ['size' => 14]);
                $textrun4->addText($eleve->getequipe()->getLyceeLocalite(), ['size' => 14, 'bold' => true]);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun5 = $section->addTextRun(['align' => 'center']);
                $textrun5->addText('Académie de ' . $eleve->getEquipe()->getLyceeAcademie(), ['size' => 14,]);

                $filesystem = new Filesystem();
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $section->addText('a participé le 6 décembre 2023 au concours interacadémique de ' . $eleve->getEquipe()->getCentre() . ' ' . $eleve->getEquipe()->getCentre()->getLieu(), ['size' => 14,]);
                $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                $section->addText('                     pour le Comité national des Olympiades de Physique France', ['size' => 12]);
                $src2 = 'odpf/odpf-images/signature_gd_format.png';
                $textrun6 = $section->addTextRun(['align' => 'right']);
                $section->addImage($src2, array(
                    'width' => 100,
                    'positioning' => 'absolute',
                    'alignement' => 'right',
                    //'posHorizontalRel' => 'right',
                    'wrapDistanceLeft' => 300,
                    'posVerticalRel' => 'line',
                ), false, 'signature');
                $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                $section->addText('Pascale Hervé      ', ['size' => 12], ['align' => 'right', '']);

                $fileName = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.doc';
                //$fileNamepdf = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.pdf';


                try {
                    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
                    //$pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpPdf, 'PDF');

                } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                    dd($e);
                }
                $objWriter->save($fileName);
                //La transfromation en pdf est mauvaise : ne garde pas la fonte, la taille de la marge et la formatge des images
                //require __DIR__ . '/vendor/autoload.php';

                // Make sure you have `dompdf/dompdf` in your composer dependencies.
                /* Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
                 // Any writable directory here. It will be ignored.
                 Settings::setPdfRendererPath('.');
                 Settings::setDefaultPaper('A4');
                 //Settings::setMeasurementUnit('point');
                 Settings::setDefaultFontName('Verdana');
                 Settings::setDefaultFontSize('14');
                 $domPdf = new dompdf();
                 //dd(fread(fopen($this->getParameter('app.path.tempdirectory') . '/' . $fileName, 'r'), filesize($this->getParameter('app.path.tempdirectory') . '/' . $fileName)));
                 $fichier = fopen($fileName, 'r');
                 $text = fread($fichier, filesize($fileName));
                 $domPdf->loadHtml($text);
                 fclose($fichier);

                 $domPdf->setPaper('A4');
                 $domPdf->render();
                 $output = $domPdf->output();
                 file_put_contents($fileNamepdf, $output);
                 //Settings::
                 $phpWordPdf = IOFactory::load($fileName, 'DOC');

                 $phpWordPdf->save($fileNamepdf, 'PDF');
                 */

                $zipFile->addFromString(basename($fileName), file_get_contents($fileName));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file

                //$pdfWriter->save($this->getParameter('app.path.tempdirectory') . '/' . $fileNamepdf);
            }
        }

        $zipFile->close();
        $response = new Response(file_get_contents($fileNameZip));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileNameZip);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);
        @unlink($fileNameZip);
        return $response;


    }

    #[Route("/Admin/ElevesinteradminCrud/attestationsElevesTwig,{ideditionequipe}", name: "attestations_eleves_pdf")]
    public function attestationsElevesPdf($ideditionequipe)//Un essais de conversion d'un twig avec  $knpSnappyPdf mais problème du this->render qui va chercher dans les templates, répertoire protégé;
    {
        $slugger = new AsciiSlugger();
        $idedition = explode('-', $ideditionequipe)[0];
        $idequipe = explode('-', $ideditionequipe)[1];
        $exp = new UnicodeString('e');
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);
        $queryBuilder = $repositoryEleves->createQueryBuilder('e');
        if ($idequipe == 'na') {

            $queryBuilder->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $edition)
                ->orderBy('eq.centre', 'ASC')
                ->addOrderBy('eq.numero', 'ASC');
            if (isset(explode('-', $ideditionequipe)[2])) {
                explode('-', $ideditionequipe)[2] == 'ns' ? $queryBuilder->andWhere('eq.selectionnee = 0') : $queryBuilder->andWhere('eq.selectionnee = 1');

            }

        }
        if ($idequipe != 'na') {
            $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
            $queryBuilder
                ->andWhere('e.equipe =:equipe')
                ->setParameter('equipe', $equipe);
        }
        $liste_eleves = $queryBuilder->getQuery()->getResult();
        $zipFile = new ZipArchive();
        $now = new DateTime('now');
        $fileNameZip = $edition->getEd() . '-Attestations_eleves_non_selectionnes-' . $now->format('d-m-Y\-His');
        if ($zipFile->open($fileNameZip, ZipArchive::CREATE) === TRUE) {
            if ($liste_eleves != null) {
                foreach ($liste_eleves as $eleve) {

                    $filename = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.doc';
                    $fileNamepdf = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.pdf';
                    $filenameTemplate = '/templates/attestations/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.html.twig';
                    //$filesystem = new Filesystem();
                    //$filesystem->copy($filename, $filenameTemplate);
                    //$twig = fopen($filename, 'w+');
                    //fwrite($twig, $text);
                    //fclose($twig);
                    $pdf = new Fpdf('P', 'mm', 'A4');
                    //$pdf->AddFont('Verdana');
                    $pdf->SetFont('helvetica', '', 14);
                    $pdf->SetMargins(20, 20);
                    $pdf->SetLeftMargin(20);
                    $pdf->SetRightMargin(20);
                    $pdf->AddPage();
                    $pdf->image('https://www.olymphys.fr/public/odpf/odpf-images/site-logo-398x106.png', 20, null, 60);
                    $str = 'Paris le ' . $this->date_in_french($this->requestStack->getSession()->get('edition')->getConcourscia()->format('Y-m-d'));
                    $str = iconv('UTF-8', 'windows-1252', $str);
                    $pdf->Cell(0, 30, $str . "\n", 0, 0, 'R');
                    $pdf->SetFont('helvetica', 'B', 18);
                    $str1 = 'Attestation de participation';
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 40;
                    $w = $pdf->GetStringWidth($str1);
                    $x = (210 - $w) / 2;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w, 20, $str1 . "\n", 0, 0, 'C');
                    $pdf->SetFont('helvetica', 'B', 18);
                    $w2 = $pdf->getStringWidth('Aux ' . $this->requestStack->getSession()->get('edition')->getEd() . 'e Olympiades de Physique France');
                    $x = (210 - $w2) / 2;
                    $str2 = 'Aux ' . $this->requestStack->getSession()->get('edition')->getEd();
                    $str21 = 'Olympiades de Physique France';
                    $w3 = $pdf->getStringWidth('Aux ' . $this->requestStack->getSession()->get('edition')->getEd());
                    $y = $pdf->getY() + 10;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w3, 20, $str2 . "\n", 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() - 2;
                    $pdf->SetXY($x, $y);

                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->Cell(5, 20, 'e', 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 2;
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('helvetica', 'B', 18);
                    $pdf->Cell(0, 20, $str21 . "\n", 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 30;
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('helvetica', '', 14);
                    $str3 = iconv('UTF-8', 'windows-1252', 'Le comité national des Olympiades de Physique France certifie que :');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 10;
                    $pdf->SetXY(0, $y);
                    $pdf->Cell(0, 10, $str3 . "\n", 0, 0, 'C');
                    $w4 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'l\'élève ' . $eleve->getprenom() . ' ' . $eleve->getNom()));
                    $str4 = iconv('UTF-8', 'windows-1252', 'l\'élève ');
                    $str5 = iconv('UTF-8', 'windows-1252', $eleve->getprenom() . ' ' . $eleve->getNom());
                    $x = (210 - $w4) / 2;
                    $w5 = $pdf->getStringWidth('l\'élève ');
                    $y = $pdf->getY() + 10;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w5 - 2, 10, $str4 . "\n", 0, 0, 'L');
                    $pdf->SetTextColor(84, 173, 209);
                    $x = $pdf->getX() - 4;
                    $pdf->setX($x);
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->cell(0, 10, $str5, '', 'L');
                    $pdf->SetFont('helvetica', '', 14);
                    $str6 = iconv('UTF-8', 'windows-1252', 'du lycée ' . $eleve->getEquipe()->getNomLycee());
                    $pdf->SetTextColor(0, 0, 0);

                    $w6 = $pdf->getStringWidth($str6);
                    $w7 = $pdf->getStringWidth('du lycée ');
                    $x = (210 - $w6) / 2;
                    $y = $pdf->getY();
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w7, 10, iconv('UTF-8', 'windows-1252', 'du lycée '), '', 'R');
                    $x = $pdf->getX() + $w7 - 3;
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('helvetica', '', 14);
                    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $eleve->getEquipe()->getNomLycee()), '', 'L');

                    $str9 = 'à ' . $eleve->getEquipe()->getLyceeLocalite();
                    $w9 = $pdf->getStringWidth($str9);
                    $x = (210 - $w9) / 2;
                    $y = $pdf->getY();
                    $pdf->SetXY($x, $y);
                    $w10 = $pdf->getStringWidth('à ');
                    $pdf->Cell($w10, 10, iconv('UTF-8', 'windows-1252', 'à '), '', 'R');
                    $x = $pdf->getX() + $w10;
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $eleve->getEquipe()->getLyceeLocalite()), '', 'L');

                    $pdf->SetFont('helvetica', '', 14);
                    $str11 = iconv('UTF-8', 'windows-1252', 'Académie de ' . $eleve->getEquipe()->getLyceeAcademie());
                    $w11 = $pdf->getStringWidth($str11);
                    $x = (210 - $w11) / 2;
                    $y = $pdf->getY();
                    $pdf->SetXY($x, $y);
                    $w12 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'Académie de '));
                    $pdf->Cell($w12, 10, iconv('UTF-8', 'windows-1252', 'Académie de '), '', 'R');
                    $x = $pdf->getX() + $w12;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $eleve->getEquipe()->getLyceeAcademie()), '', 'L');
                    $y = $pdf->getY();
                    $pdf->setXY(20, $y + 8);
                    $pdf->Write(8, iconv('UTF-8', 'windows-1252',
                        'a participé le ' .
                        $this->date_in_french($this->requestStack->getSession()->get('edition')->getConcoursCia()->format('Y-m-d')) . ' au concours interacadémique de ' . $eleve->getEquipe()->getCentre() . ' ' .
                        $eleve->getEquipe()->getCentre()->getLieu() . '.'
                    ));

                    $w13 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'pour le comité national des Olympiades de Physique France'));
                    $x = (210 - $w13) / 2;
                    $y = $pdf->getY();
                    $pdf->setXY($x, $y + 10);
                    $pdf->Cell($w13, 8, iconv('UTF-8', 'windows-1252', 'Pour le comité national des Olympiades de Physique France'), '', 'C');
                    $y = $pdf->getY();
                    $pdf->image('odpf/odpf-images/signature_gd_format.png', 130, $y, 40);
                    $y = $pdf->getY();
                    $pdf->setXY(130, $y + 20);
                    $pdf->Cell(0, 8, iconv('UTF-8', 'windows-1252', 'Pascale Hervé'), '', 'C');
                    $pdf->Output('F', $fileNamepdf);
                    $zipFile->addFromString(basename($fileNamepdf), file_get_contents($fileNamepdf));

                    //Création du fichier word
                    $phpWord = new  PhpWord();
                    $phpWord->setDefaultFontName('Verdana');
                    $section = $phpWord->addSection();
                    $src = 'odpf/odpf-images/site-logo-150x43.png';
                    $section->addImage($src, array(
                        'width' => '150',
                        'positioning' => 'absolute',
                        'posHorizontalRel' => 'margin',
                        'posVerticalRel' => 'line',
                    ), false, 'logo');
                    $section->addTextBreak(3);
                    $section->addText('Paris le ' . $this->date_in_french($this->requestStack->getSession()->get('edition')->getConcourscia()->format('Y-m-d')), ['size' => 14,], ['align' => 'right']);
                    $section->addTextBreak(4, ['size' => 14]);
                    $section->addText('Attestation de participation aux', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                    $textrun = $section->addTextRun(['align' => 'center']);
                    $textrun->addText('31', ['size' => 18, 'bold' => true,]);
                    $textrun->addText('e', ['size' => 18, 'bold' => true, 'superScript' => true]);
                    $textrun->addText(' Olympiades de Physique France', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                    $section->addTextBreak(3, ['bold' => true, 'size' => 18]);
                    $section->addText('Le Comité national des Olympiades de Physique France certifie que :', ['size' => 14,], ['align' => 'left']);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun2 = $section->addTextRun(['align' => 'center']);
                    $textrun2->addText('l’élève ', ['size' => 14,]);
                    $textrun2->addText($eleve->getPrenom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                    $textrun2->addText(' ', ['size' => 14,]);
                    $textrun2->addText($eleve->getNom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun3 = $section->addTextRun(['align' => 'center']);
                    $textrun3->addText('du lycée ', ['size' => 14]);
                    $textrun3->addText($eleve->getequipe()->getNomLycee(), ['size' => 14]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun4 = $section->addTextRun(['align' => 'center']);
                    $textrun4->addText('à ', ['size' => 14]);
                    $textrun4->addText($eleve->getequipe()->getLyceeLocalite(), ['size' => 14, 'bold' => true]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun5 = $section->addTextRun(['align' => 'center']);
                    $textrun5->addText('Académie de ' . $eleve->getEquipe()->getLyceeAcademie(), ['size' => 14,]);

                    $filesystem = new Filesystem();
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $section->addText('a participé le 6 décembre 2023 au concours interacadémique de ' . $eleve->getEquipe()->getCentre() . ' ' . $eleve->getEquipe()->getCentre()->getLieu(), ['size' => 14,]);
                    $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                    $section->addText('                     pour le Comité national des Olympiades de Physique France', ['size' => 12]);
                    $src2 = 'odpf/odpf-images/signature_gd_format.png';
                    $textrun6 = $section->addTextRun(['align' => 'right']);
                    $section->addImage($src2, array(
                        'width' => 100,
                        'positioning' => 'absolute',
                        'alignement' => 'right',
                        //'posHorizontalRel' => 'right',
                        'wrapDistanceLeft' => 300,
                        'posVerticalRel' => 'line',
                    ), false, 'signature');
                    $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                    $section->addText('Pascale Hervé      ', ['size' => 12], ['align' => 'right', '']);

                    $fileName = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.doc';
                    //$fileNamepdf = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.pdf';


                    try {
                        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
                        //$pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpPdf, 'PDF');

                    } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                        dd($e);
                    }
                    $objWriter->save($fileName);
                    $zipFile->addFromString(basename($fileName), file_get_contents($fileName));

                }

            }
        }
        $zipFile->close();
        $response = new Response(file_get_contents($fileNameZip));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileNameZip);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);
        @unlink($fileNameZip);
        return $response;


    }

    public function date_in_french($date)
    {
        $week_name = array("Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi");
        $month_name = array("", "janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août",
            "septembre", "octobre", "novembre", "décembre");

        $split = explode('-', $date);
        $year = $split[0];
        $month = round($split[1]);
        $day = round($split[2]);

        $week_day = date("w", mktime(12, 0, 0, $month, $day, $year));
        return $date_fr = $day . ' ' . $month_name[$month] . ' ' . $year;
    }

    #[Route("/Admin/ElevesinteradminCrud/attestationsElevesNat,{ideditionequipe}", name: "attestations_eleves_nat")]
    public function attestationsElevesNat($ideditionequipe)
    {
        $slugger = new AsciiSlugger();
        $idedition = explode('-', $ideditionequipe)[0];
        $idequipe = explode('-', $ideditionequipe)[1];
        $exp = new UnicodeString('e');
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);
        $queryBuilder = $repositoryEleves->createQueryBuilder('e');
        if ($idequipe == 'na') {

            $queryBuilder->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.inscrite = TRUE')
                ->setParameter('edition', $edition)
                ->orderBy('eq.centre', 'ASC')
                ->addOrderBy('eq.numero', 'ASC');
            if (isset(explode('-', $ideditionequipe)[2])) {
                explode('-', $ideditionequipe)[2] == 'ns' ? $queryBuilder->andWhere('eq.selectionnee = 0') : $queryBuilder->andWhere('eq.selectionnee = 1');

            }

        }
        if ($idequipe != 'na') {
            $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
            $queryBuilder
                ->andWhere('e.equipe =:equipe')
                ->setParameter('equipe', $equipe);
        }
        $liste_eleves = $queryBuilder->getQuery()->getResult();
        $zipFile = new ZipArchive();
        $now = new DateTime('now');
        $fileNameZip = $edition->getEd() . '-Attestations_eleves_non_selectionnes-' . $now->format('d-m-Y\-His');
        if ($zipFile->open($fileNameZip, ZipArchive::CREATE) === TRUE) {
            foreach ($liste_eleves as $eleve) {
                $phpWord = new  PhpWord();
                $phpWord->setDefaultFontName('Verdana');
                $section = $phpWord->addSection();
                $src = 'odpf/odpf-images/site-logo-150x43.png';
                $section->addImage($src, array(
                    'width' => '150',
                    'positioning' => 'absolute',
                    'posHorizontalRel' => 'margin',
                    'posVerticalRel' => 'line',
                ), false, 'logo');
                $section->addTextBreak(3);
                $section->addText('Paris le ' . $this->date_in_french($this->requestStack->getSession()->get('edition')->getConcourscia()->format('Y-m-d')), ['size' => 14,], ['align' => 'right']);
                $section->addTextBreak(4, ['size' => 14]);
                $section->addText('Attestation de participation aux', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                $textrun = $section->addTextRun(['align' => 'center']);
                $textrun->addText('31', ['size' => 18, 'bold' => true,]);
                $textrun->addText('e', ['size' => 18, 'bold' => true, 'superScript' => true]);
                $textrun->addText(' Olympiades de Physique France', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                $section->addTextBreak(3, ['bold' => true, 'size' => 18]);
                $section->addText('Le Comité national des Olympiades de Physique France certifie que :', ['size' => 14,], ['align' => 'left']);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun2 = $section->addTextRun(['align' => 'center']);
                $textrun2->addText('l’élève ', ['size' => 14,]);
                $textrun2->addText($eleve->getPrenom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                $textrun2->addText(' ', ['size' => 14,]);
                $textrun2->addText($eleve->getNom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun3 = $section->addTextRun(['align' => 'center']);
                $textrun3->addText('du lycée ', ['size' => 14]);
                $textrun3->addText($eleve->getequipe()->getNomLycee(), ['size' => 14]);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun4 = $section->addTextRun(['align' => 'center']);
                $textrun4->addText('à ', ['size' => 14]);
                $textrun4->addText($eleve->getequipe()->getLyceeLocalite(), ['size' => 14, 'bold' => true]);
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $textrun5 = $section->addTextRun(['align' => 'center']);
                $textrun5->addText('Académie de ' . $eleve->getEquipe()->getLyceeAcademie(), ['size' => 14,]);

                $filesystem = new Filesystem();
                $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                $section->addText('a participé le 6 décembre 2023 au concours interacadémique de ' . $eleve->getEquipe()->getCentre() . ' ' . $eleve->getEquipe()->getCentre()->getLieu(), ['size' => 14,]);
                $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                $section->addText('                     pour le Comité national des Olympiades de Physique France', ['size' => 12]);
                $src2 = 'odpf/odpf-images/signature_gd_format.png';
                $textrun6 = $section->addTextRun(['align' => 'right']);
                $section->addImage($src2, array(
                    'width' => 100,
                    'positioning' => 'absolute',
                    'alignement' => 'right',
                    //'posHorizontalRel' => 'right',
                    'wrapDistanceLeft' => 300,
                    'posVerticalRel' => 'line',
                ), false, 'signature');
                $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                $section->addText('Pascale Hervé      ', ['size' => 12], ['align' => 'right', '']);

                $fileName = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.doc';
                //$fileNamepdf = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.pdf';


                try {
                    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
                    //$pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpPdf, 'PDF');

                } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                    dd($e);
                }
                $objWriter->save($fileName);
                //La transfromation en pdf est mauvaise : ne garde pas la fonte, la taille de la marge et la formatge des images
                //require __DIR__ . '/vendor/autoload.php';

                // Make sure you have `dompdf/dompdf` in your composer dependencies.
                /* Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
                 // Any writable directory here. It will be ignored.
                 Settings::setPdfRendererPath('.');
                 Settings::setDefaultPaper('A4');
                 //Settings::setMeasurementUnit('point');
                 Settings::setDefaultFontName('Verdana');
                 Settings::setDefaultFontSize('14');
                 $domPdf = new dompdf();
                 //dd(fread(fopen($this->getParameter('app.path.tempdirectory') . '/' . $fileName, 'r'), filesize($this->getParameter('app.path.tempdirectory') . '/' . $fileName)));
                 $fichier = fopen($fileName, 'r');
                 $text = fread($fichier, filesize($fileName));
                 $domPdf->loadHtml($text);
                 fclose($fichier);

                 $domPdf->setPaper('A4');
                 $domPdf->render();
                 $output = $domPdf->output();
                 file_put_contents($fileNamepdf, $output);
                 //Settings::
                 $phpWordPdf = IOFactory::load($fileName, 'DOC');

                 $phpWordPdf->save($fileNamepdf, 'PDF');
                 */

                $zipFile->addFromString(basename($fileName), file_get_contents($fileName));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file

                //$pdfWriter->save($this->getParameter('app.path.tempdirectory') . '/' . $fileNamepdf);
            }
        }

        $zipFile->close();
        $response = new Response(file_get_contents($fileNameZip));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileNameZip);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);
        @unlink($fileNameZip);
        return $response;


    }

    #[Route("/Admin/ElevesinteradminCrud/attestationsElevesTwigPdf,{ideditionequipe}", name: "attestations_eleves_nat_pdf")]
    public function attestationsElevesNatPdf($ideditionequipe)//Un essais de conversion d'un twig avec  $knpSnappyPdf mais problème du this->render qui va chercher dans les templates, répertoire protégé;
    {
        $slugger = new AsciiSlugger();
        $idedition = explode('-', $ideditionequipe)[0];
        $idequipe = explode('-', $ideditionequipe)[1];
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $repositoryEquipespassees = $this->doctrine->getRepository(OdpfEquipesPassees::class);
        $repositoryEditionsspassees = $this->doctrine->getRepository(OdpfEditionsPassees::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idedition]);
        $editionpassee = $repositoryEditionsspassees->findOneBy(['edition' => $edition->getEd()]);
        $day = $edition->getConcoursCn()->format('d')-1;
        $queryBuilder = $repositoryEleves->createQueryBuilder('e');
        if ($idequipe == 'na') {

            $queryBuilder->leftJoin('e.equipe', 'eq')
                ->andWhere('eq.edition =:edition')
                ->andWhere('eq.selectionnee =:value')
                ->setParameter('edition', $edition)
                ->setParameter('value', true)
                ->addOrderBy('eq.lettre', 'ASC');
            if (isset(explode('-', $ideditionequipe)[2])) {
                explode('-', $ideditionequipe)[2] == 'ns' ? $queryBuilder->andWhere('eq.selectionnee = 0') : $queryBuilder->andWhere('eq.selectionnee = 1');

            }

        }
        if ($idequipe != 'na') {
            $equipe = $repositoryEquipes->findOneBy(['id' => $idequipe]);
            $queryBuilder
                ->andWhere('e.equipe =:equipe')
                ->setParameter('equipe', $equipe);
        }
        $liste_eleves = $queryBuilder->getQuery()->getResult();
        $zipFile = new ZipArchive();
        $now = new DateTime('now');
        $fileNameZip = $edition->getEd() . '-Attestations_eleves_selectionnes-' . $now->format('d-m-Y\-His');
        if ($zipFile->open($fileNameZip, ZipArchive::CREATE) === TRUE) {
            if ($liste_eleves != null) {
                foreach ($liste_eleves as $eleve) {
                    $equipepassee = $repositoryEquipespassees->findOneBy(['editionspassees' => $editionpassee, 'lettre' => $eleve->getEquipe()->getLettre()]);

                    $filename = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.doc';
                    $fileNamepdf = $this->getParameter('app.path.tempdirectory') . '/' . $edition->getEd() . '_ Eq ' . $slugger->slug($eleve->getEquipe()->getLettre() . '_attestation_élève_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.pdf';
                    $filenameTemplate = '/templates/attestations/' . $edition->getEd() . '_ attestation_equipe_' . $eleve->getEquipe()->getLettre() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom() . '.html.twig';
                    //$filesystem = new Filesystem();
                    //$filesystem->copy($filename, $filenameTemplate);
                    //$twig = fopen($filename, 'w+');
                    //fwrite($twig, $text);
                    //fclose($twig);
                    $pdf = new Fpdf('P', 'mm', 'A4');
                    //$pdf->AddFont('Verdana');
                    $pdf->SetFont('helvetica', '', 14);
                    $pdf->SetMargins(20, 20);
                    $pdf->SetLeftMargin(20);
                    $pdf->SetRightMargin(20);
                    $pdf->AddPage();
                    $pdf->image('https://www.olymphys.fr/public/odpf/odpf-images/site-logo-398x106.png', 20, null, 60);
                    $str = 'Paris le ' . $this->date_in_french($edition->getConcoursCn()->format('Y-m-d'));
                    $str = iconv('UTF-8', 'windows-1252', $str);
                    $pdf->Cell(0, 30, $str . "\n", 0, 0, 'R');
                    $pdf->SetFont('helvetica', 'B', 18);
                    $str1 = 'Attestation de participation';
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 40;
                    $w = $pdf->GetStringWidth($str1);
                    $x = (210 - $w) / 2;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w, 20, $str1 . "\n", 0, 0, 'C');
                    $pdf->SetFont('helvetica', 'B', 18);
                    $w2 = $pdf->getStringWidth('Aux ' . $edition->getEd() . 'e Olympiades de Physique France');
                    $x = (210 - $w2) / 2;
                    $str2 = 'Aux ' . $edition->getEd();
                    $str21 = 'Olympiades de Physique France';
                    $w3 = $pdf->getStringWidth('Aux ' . $edition->getEd());
                    $y = $pdf->getY() + 10;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w3, 20, $str2 . "\n", 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() - 2;
                    $pdf->SetXY($x, $y);

                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->Cell(5, 20, 'e', 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 2;
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('helvetica', 'B', 18);
                    $pdf->Cell(0, 20, $str21 . "\n", 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 30;
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('helvetica', '', 14);
                    $str3 = iconv('UTF-8', 'windows-1252', 'Le comité national des Olympiades de Physique France certifie que :');
                    $x = $pdf->GetX();
                    $y = $pdf->getY() + 10;
                    $pdf->SetXY(0, $y);
                    $pdf->Cell(0, 10, $str3 . "\n", 0, 0, 'C');
                    $w4 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'l\'élève ' . $eleve->getprenom() . ' ' . $eleve->getNom()));
                    $str4 = iconv('UTF-8', 'windows-1252', 'l\'élève ');
                    $str5 = iconv('UTF-8', 'windows-1252', $eleve->getprenom() . ' ' . $eleve->getNom());
                    $x = (210 - $w4) / 2;
                    $w5 = $pdf->getStringWidth('l\'élève ');
                    $y = $pdf->getY() + 10;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w5 - 2, 10, $str4 . "\n", 0, 0, 'L');
                    $pdf->SetTextColor(84, 173, 209);
                    $x = $pdf->getX() - 4;
                    $pdf->setX($x);
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->cell(0, 10, $str5, '', 'L');
                    $pdf->SetFont('helvetica', '', 14);
                    $str6 = iconv('UTF-8', 'windows-1252', 'du lycée ' . $eleve->getEquipe()->getNomLycee());
                    $pdf->SetTextColor(0, 0, 0);

                    $w6 = $pdf->getStringWidth($str6);
                    $w7 = $pdf->getStringWidth('du lycée ');
                    $x = (210 - $w6) / 2;
                    $y = $pdf->getY();
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($w7, 10, iconv('UTF-8', 'windows-1252', 'du lycée '), '', 'R');
                    $x = $pdf->getX() + $w7 - 3;
                    $pdf->SetXY($x, $y);
                    $pdf->SetFont('helvetica', '', 14);
                    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $eleve->getEquipe()->getNomLycee()), '', 'L');

                    $str9 = 'à ' . $eleve->getEquipe()->getLyceeLocalite();
                    $w9 = $pdf->getStringWidth($str9);
                    $x = (210 - $w9) / 2;
                    $y = $pdf->getY();
                    $pdf->SetXY($x, $y);
                    $w10 = $pdf->getStringWidth('à ');
                    $pdf->Cell($w10, 10, iconv('UTF-8', 'windows-1252', 'à '), '', 'R');
                    $x = $pdf->getX() + $w10;
                    $pdf->SetFont('helvetica', 'B', 14);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $eleve->getEquipe()->getLyceeLocalite()), '', 'L');

                    $pdf->SetFont('helvetica', '', 14);
                    $str11 = iconv('UTF-8', 'windows-1252', 'Académie de ' . $eleve->getEquipe()->getLyceeAcademie());
                    $w11 = $pdf->getStringWidth($str11);
                    $x = (210 - $w11) / 2;
                    $y = $pdf->getY();
                    $pdf->SetXY($x, $y);
                    $w12 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'Académie de '));
                    $pdf->Cell($w12, 10, iconv('UTF-8', 'windows-1252', 'Académie de '), '', 'R');
                    $x = $pdf->getX() + $w12;
                    $pdf->SetXY($x, $y);
                    $pdf->Cell(0, 10, iconv('UTF-8', 'windows-1252', $eleve->getEquipe()->getLyceeAcademie()), '', 'R');
                    $y = $pdf->getY();
                    $w14 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'a participé le ' . strval($day - 2) . ' et ' .
                        $this->date_in_french($edition->getConcoursCn()->format('Y-m-d')) . ' au'));
                    $w15 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'au 31e concours national des'));
                    $pdf->SetXY((210 - $w14) / 2, $y);
                    $pdf->Cell($w14, 8, iconv('UTF-8', 'windows-1252',
                        'a participé le ' . $day . ' et ' .
                        $this->date_in_french($edition->getConcoursCn()->format('Y-m-d')) . ' au'), '', 'R');
                    $y = $pdf->getY();
                    $pdf->SetXY((210 - $w15) / 2, $y);
                    $pdf->Cell(2, 8, iconv('UTF-8', 'windows-1252', '31'), '', 'R');
                    $x = $pdf->GetX();
                    $y = $y - 2;
                    $pdf->setXY($x + 5, $y);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->Cell(5, 8, 'e', 0, 0, 'L');
                    $x = $pdf->GetX();
                    $y = $y + 2;
                    $pdf->SetFont('helvetica', '', 14);
                    $pdf->setXY($x, $y);
                    $pdf->Cell($w15, 8, iconv('UTF-8', 'windows-1252', ' concours national des'), '', 'L');
                    $y = $pdf->GetY();
                    $w16 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'Olympiades de Physique France à'));
                    $pdf->setXY((210 - $w16) / 2, $y);
                    $pdf->Cell($w16, 8, iconv('UTF-8', 'windows-1252', 'Olympiades de Physique France à '), '', 'L');
                    $w17 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'l\'' . $edition->getLieu() . '.'));
                    $y = $pdf->getY();
                    $pdf->setXY((210 - $w17) / 2, $y);
                    $pdf->Cell($w16, 8, iconv('UTF-8', 'windows-1252', 'l\'' . $editionpassee->getLieu() . '.'), '', 'R');
                    $pdf->setXY(20, $y +12);
                    $pdf->Write(8, iconv('UTF-8', 'windows-1252', 'Son équipe a obtenu un ' .
                        $this->prixlit($equipepassee->getPalmares()) . ' prix.'));
                    $w13 = $pdf->getStringWidth(iconv('UTF-8', 'windows-1252', 'pour le comité national des Olympiades de Physique France'));
                    $x = (210 - $w13) / 2;
                    $y = $pdf->getY();
                    $pdf->setXY($x, $y + 12);
                    $pdf->Cell($w13, 8, iconv('UTF-8', 'windows-1252', 'Pour le comité national des Olympiades de Physique France'), '', 'C');
                    $y = $pdf->getY();
                    $pdf->image('odpf/odpf-images/signature_gd_format.png', 130, $y, 40);
                    $y = $pdf->getY();
                    $pdf->setXY(130, $y + 20);
                    $pdf->Cell(0, 8, iconv('UTF-8', 'windows-1252', 'Pascale Hervé'), '', 'C');
                    $pdf->Output('F', $fileNamepdf);
                    $zipFile->addFromString(basename($fileNamepdf), file_get_contents($fileNamepdf));

                    //Création du fichier word
                    $phpWord = new  PhpWord();
                    $phpWord->setDefaultFontName('Verdana');
                    $section = $phpWord->addSection();
                    $src = 'odpf/odpf-images/site-logo-150x43.png';
                    $section->addImage($src, array(
                        'width' => '150',
                        'positioning' => 'absolute',
                        'posHorizontalRel' => 'margin',
                        'posVerticalRel' => 'line',
                    ), false, 'logo');
                    $section->addTextBreak(3);
                    $section->addText('Paris le ' . $this->date_in_french($edition->getConcoursCn()->format('Y-m-d')), ['size' => 14,], ['align' => 'right']);
                    $section->addTextBreak(4, ['size' => 14]);
                    $section->addText('Attestation de participation aux', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                    $textrun = $section->addTextRun(['align' => 'center']);
                    $textrun->addText('31', ['size' => 18, 'bold' => true,]);
                    $textrun->addText('e', ['size' => 18, 'bold' => true, 'superScript' => true]);
                    $textrun->addText(' Olympiades de Physique France', ['size' => 18, 'bold' => true,], ['align' => 'center']);
                    $section->addTextBreak(3, ['bold' => true, 'size' => 18]);
                    $section->addText('Le Comité national des Olympiades de Physique France certifie que :', ['size' => 14,], ['align' => 'left']);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun2 = $section->addTextRun(['align' => 'center']);
                    $textrun2->addText('l’élève ', ['size' => 14,]);
                    $textrun2->addText($eleve->getPrenom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                    $textrun2->addText(' ', ['size' => 14,]);
                    $textrun2->addText($eleve->getNom(), ['size' => 14, 'color' => '54add1', 'bold' => true]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun3 = $section->addTextRun(['align' => 'center']);
                    $textrun3->addText('du lycée ', ['size' => 14]);
                    $textrun3->addText($eleve->getequipe()->getNomLycee(), ['size' => 14]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun4 = $section->addTextRun(['align' => 'center']);
                    $textrun4->addText('à ', ['size' => 14]);
                    $textrun4->addText($eleve->getequipe()->getLyceeLocalite(), ['size' => 14, 'bold' => true]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun5 = $section->addTextRun(['align' => 'center']);
                    $textrun5->addText('Académie de ' . $eleve->getEquipe()->getLyceeAcademie(), ['size' => 14,]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $textrun6=$section->addTextRun(['align'=>'center']);
                    $textrun6->addText('a participé le '.$day.' et '.$this->date_in_french($edition->getConcoursCn()->format('Y-m-d')).' au'  , ['size' => 14,]);
                    $textrun7=$section->addTextRun(['align'=>'center']);
                    $textrun7->addText('31', ['size' => 14,]);
                    $textrun7->addText('e',['size'=>14,'superScript' => true]);
                    $textrun7->addText(' concours national des', ['size'=>14]);
                    $textrun8=$section->addTextRun(['align'=>'center']);
                    $textrun8->addText('Olympiades de  Physique France à', ['size' => 14,]);
                    $textrun9=$section->addTextRun(['align'=>'center']);
                    $textrun9->addText('l\'' . $editionpassee->getLieu() . '.', ['size' => 14,]);
                    $section->addTextBreak(1, ['bold' => true, 'size' => 14]);
                    $section->addText('Son équipe a obtenu un ' . $this->prixLit($equipepassee->getPalmares()) . ' prix.', ['size' => 14,]);
                    $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                    $section->addText('                     pour le Comité national des Olympiades de Physique France', ['size' => 12]);
                    $src2 = 'odpf/odpf-images/signature_gd_format.png';
                    $textrun6 = $section->addTextRun(['align' => 'right']);
                    $section->addImage($src2, array(
                        'width' => 100,
                        'positioning' => 'absolute',
                        'alignement' => 'right',
                        //'posHorizontalRel' => 'right',
                        'wrapDistanceLeft' => 300,
                        'posVerticalRel' => 'line',
                    ), false, 'signature');
                    $section->addTextBreak(2, ['bold' => true, 'size' => 14]);
                    $section->addText('Pascale Hervé      ', ['size' => 12], ['align' => 'right', '']);
                    $filesystem = new Filesystem();
                    $fileName = $this->getParameter('app.path.tempdirectory') . '/' . $edition->getEd() . '_ Eq ' . $slugger->slug($eleve->getEquipe()->getLettre() . '_attestation_élève_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.doc';
                    //$fileNamepdf = $this->getParameter('app.path.tempdirectory') . '/' . $eleve->getEquipe()->getEdition()->getEd() . '_' . $slugger->slug($eleve->getEquipe()->getCentre()->getCentre() . '_attestation_equipe_' . $eleve->getEquipe()->getNumero() . '_' . $eleve->getPrenom() . '_' . $eleve->getNom()) . '.pdf';


                    try {
                        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
                        //$pdfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpPdf, 'PDF');

                    } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                        dd($e);
                    }
                    $objWriter->save($fileName);
                    $zipFile->addFromString(basename($fileName), file_get_contents($fileName));

                }

            }
        }
        $zipFile->close();
        $response = new Response(file_get_contents($fileNameZip));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileNameZip);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', $disposition);
        @unlink($fileNameZip);
        return $response;


    }

    public function prixLit($palmares): string
    {
        $palmaresLit = '';
        switch ($palmares) {

            case '1er' :
                $palmaresLit = 'premier';
                break;
            case '2ème' :
                $palmaresLit = 'deuxième';
                break;
            case '3ème' :
                $palmaresLit = 'troisième';
                break;
        }
        return $palmaresLit;
    }


}