<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use App\Entity\Visites;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $contenu = TextField::new('contenu');
        $fournisseur = TextField::new('fournisseur');
        $montant = NumberField::new('montant');
        $attribue = BooleanField::new('attribue');
        $raccourci = TextField::new('raccourci');
        $id = IntegerField::new('id', 'ID');
        $equipe=AssociationField::new('equipe');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $contenu, $fournisseur, $montant, $attribue, $equipe, $raccourci];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $contenu, $fournisseur, $montant, $attribue, $equipe, $raccourci];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$contenu, $fournisseur, $montant, $attribue, $equipe, $raccourci];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$contenu, $fournisseur, $montant, $attribue,$equipe, $raccourci];
        }
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
