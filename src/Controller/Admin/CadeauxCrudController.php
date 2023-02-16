<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use App\Entity\Equipes;
use App\Entity\Prix;
use App\Entity\Visites;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\QueryBuilder;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class CadeauxCrudController extends AbstractCrudController
{
    protected EntityManagerInterface $doctrine;
    protected RequestStack $requeststack;

    public function __Construct(EntityManagerInterface $doctrine,RequestStack $requestStack)
    {
        $this->doctrine=$doctrine;
        $this->requeststack=$requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Cadeaux::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cadeaux')
            ->setEntityLabelInPlural('Cadeaux')
            ->setSearchFields(['id', 'contenu', 'fournisseur', 'montant', 'raccourci']);
    }
    public function configureActions(Actions $actions): Actions
    {
        $tableauExcel=Action::new('cadeaux_tableau_excel','Extraire un tableau Excel', 'fa fa_array')
            ->linkToRoute('cadeaux_tableau_excel')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $tableauExcel)
            ->add(Crud::PAGE_EDIT,'index');
    }
    public function configureFields(string $pageName): iterable
    {

        if (isset($_REQUEST['entityId'])){
            $id=$_REQUEST['entityId'];
            $cadeauEquipe=$this->doctrine->getRepository(Cadeaux::class)->findOneBy(['id'=>$id]);
            $equipe= $cadeauEquipe->getEquipe();
        }

        $equipesSansCadeau=$this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
            ->where('e.cadeau=:value')
            ->setParameter('value', 'null')
            ->getQuery()->getResult();

        if (isset($equipe)){
            $equipesSansCadeau[count($equipesSansCadeau)]=$equipe;//pour afficher la valeur de l'équipe dans le formulaire
        }


        $contenu = TextField::new('contenu');
        $fournisseur = TextField::new('fournisseur');
        $montant = NumberField::new('montant');
        $raccourci = TextField::new('raccourci');
        $id = IntegerField::new('id', 'ID');
        $equipe=AssociationField::new('equipe')->setFormType(EntityType::class)
                            ->setFormTypeOptions(
                                [
                                    'class'=>Equipes::class,
                                    'choices'=>$equipesSansCadeau,
                                ]
                            );
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $contenu, $fournisseur, $montant,  $equipe, $raccourci];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $contenu, $fournisseur, $montant,  $equipe, $raccourci];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$contenu, $fournisseur, $montant,  $equipe, $raccourci];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$contenu, $fournisseur, $montant,$equipe, $raccourci];
        }
    }
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $qb = $this->doctrine->getRepository(Cadeaux::class)->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.equipe', 'eq')
            ->join('eq.equipeinter','ei');

        if (isset($_REQUEST['sort'])){
            $sort=$_REQUEST['sort'];
            if (key($sort)=='equipe'){
                $qb->addOrderBy('ei.lettre', $sort['equipe']);
            }
            if (key($sort)=='montant'){
                $qb->addOrderBy('c.montant', $sort['montant']);
            }

        }
        else{
            $qb->addOrderBy('ei.lettre', 'ASC');
        }


        ;
        return $qb;
    }
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $equipe=$entityInstance->getEquipe();
        $equipe->setCadeau($entityInstance);
        $this->doctrine->persist($equipe);
        $this->doctrine->flush();
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    #[Route("/Admin/CadeauxCrud/cadeaux_tableau_excel", name:"cadeaux_tableau_excel")]
    public function cadeauxstableauexcel()
    {
        $repositoryCadeaux = $this->doctrine->getRepository(Cadeaux::class);
        $edition = $this->requeststack->getSession()->get('edition');
        $liste_cadeaux = $repositoryCadeaux->createQueryBuilder('c')
            ->join('c.equipe','eq')
            ->where('eq.cadeau is not null')
            ->getQuery()->getResult();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CN - " . $edition->getEd() . "e -Tableau destiné au comité")
            ->setSubject("Tableau destiné au comité")
            ->setDescription("Office 2007 XLSX liste des cadeaux")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        $ligne = 1;
        $sheet->setCellValue('A' . $ligne, 'contenu')
            ->setCellValue('B' . $ligne, 'fournisseur')
            ->setCellValue('C' . $ligne, 'montant')
            ->setCellValue('D' . $ligne, 'equipe')
            ->setCellValue('E' . $ligne, 'raccourci');


        $ligne += 1;
        foreach ($liste_cadeaux as $cadeau) {
            $sheet->setCellValue('A' . $ligne, $cadeau->getContenu())
                ->setCellValue('B' . $ligne, $cadeau->getFournisseur())
                ->setCellValue('C' . $ligne, $cadeau->getMontant().' €')
                ->setCellValue('D' . $ligne, $cadeau->getEquipe())
                ->setCellValue('E' . $ligne, $cadeau->getRaccourci())
            ;

            $ligne += 1;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="cadeaux.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        //$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        // $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');


    }
}
