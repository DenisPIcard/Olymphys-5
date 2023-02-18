<?php

namespace App\Controller\Admin;

use App\Entity\Edition;
use App\Entity\Equipes;
use App\Entity\Prix;
use App\Entity\Visites;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class VisitesCrudController extends AbstractCrudController
{
    public function __Construct( protected EntityManagerInterface $doctrine,protected RequestStack $requestStack)
    {
    }
    public static function getEntityFqcn(): string
    {
        return Visites::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une visite')
            ->setSearchFields(['id', 'intitule']);
    }
    public function configureActions(Actions $actions): Actions
    {
        $tableauExcel=Action::new('visites_tableau_excel','Extraire un tableau Excel', 'fa fa_array')
        ->linkToRoute('visites_tableau_excel')
        ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $tableauExcel)
            ->add(Crud::PAGE_EDIT,'index');
    }

    public function configureFields(string $pageName): iterable
    {
        $listeEquipes=$this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
                        ->where('e.visite is null')
                        ->getQuery()->getResult();
        if(isset($_REQUEST['entityId'])){
            $equipeEdit=$this->doctrine->getRepository(Visites::class)->findOneBy(['id'=>$_REQUEST['entityId']])->getEquipe();
            $listeEquipes[count($listeEquipes)]=$equipeEdit;
        }

        $intitule = TextField::new('intitule');
        $id = IntegerField::new('id', 'ID');
        $equipe= AssociationField::new('equipe')
            ->setFormType(EntityType::class)
            ->setFormTypeOptions([
                'class'=>Equipes::class,
                'choices'=>$listeEquipes,

            ]);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$intitule,  $equipe];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $intitule, $equipe];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$intitule,$equipe];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$intitule, $equipe];
        }

    }
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $qb = $this->doctrine->getRepository(Visites::class)->createQueryBuilder('v')
            ->select('v')
            ->leftJoin('v.equipe', 'eq')
            ->join('eq.equipeinter', 'ei')
            //->addOrderBy('ei.lettre', 'ASC')
            ;
        if (isset($_REQUEST['sort'])){
            $sort=$_REQUEST['sort'];
            if (key($sort)=='equipe'){
                $qb->addOrderBy('ei.lettre', $sort['equipe']);
            }
            if (key($sort)=='intitule'){
                $qb->addOrderBy('v.intitule', $sort['intitule']);
                $qb->addOrderBy('ei.lettre', 'ASC');
            }
        }
        else {
            $qb->addOrderBy('ei.lettre', 'ASC');
        }





        return $qb;
        }

    #[Route("/Admin/VisitesCrud/visites_tableau_excel", name:"visites_tableau_excel")]
    public function visitestableauexcel()
    {
        $listEquipes =  $this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
                                        ->join('e.equipeinter','eq')
                                        ->addOrderBy('eq.lettre', 'ASC')
                                        ->getQuery()->getResult();

        $edition = $this->requestStack->getSession()->get('edition');
        if(date('now')<$this->requestStack->getSession()->get('dateouverturesite')){
            $edition=$this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$edition->getEd()-1]);
            }
        $liste_visites = [];
        $i=0;
        foreach($listEquipes as $equipe){

            $liste_visites[$i]=$equipe->getVisite();
            $i=$i+1;
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CN - " . $edition->getEd() . "e -Tableau destiné au comité")
            ->setSubject("Tableau destiné au comité")
            ->setDescription("Office 2007 XLSX liste des visites")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }
        $ligne = 1;
        $sheet->setCellValue('A' . $ligne, 'intitulé')
              ->setCellValue('B' . $ligne, 'Equipe');


        $ligne += 1;
        foreach ($liste_visites as $visite) {
            $sheet->setCellValue('A' . $ligne, $visite->getIntitule())
                ->setCellValue('B' . $ligne, $visite->getEquipe())
               ;

            $ligne += 1;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="visites.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        //$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        // $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');


    }

}
