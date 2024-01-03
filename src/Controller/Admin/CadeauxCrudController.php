<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use App\Entity\Edition;
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

    public function __Construct(protected EntityManagerInterface $doctrine, protected RequestStack $requestStack)
    {

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
        $tableauExcel = Action::new('cadeaux_tableau_excel', 'Extraire un tableau Excel', 'fa fa_array')
            ->linkToRoute('cadeaux_tableau_excel')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $tableauExcel)
            ->add(Crud::PAGE_EDIT, 'index');
    }

    public function configureFields(string $pageName): iterable
    {
        $equipesSansCadeau = $this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
            ->where('e.cadeau is NULL')
            ->getQuery()->getResult();
        if (isset($_REQUEST['entityId'])) {
            $id = $_REQUEST['entityId'];
            $cadeauEquipe = $this->doctrine->getRepository(Cadeaux::class)->findOneBy(['id' => $id]);
            $equipe = $cadeauEquipe->getEquipe();
            if (isset($equipe)) {
                $equipesSansCadeau[count($equipesSansCadeau)] = $equipe;//pour afficher la valeur de l'équipe dans le formulaire, elle est ajoutée à la fin de la liste
            }
        }

        /*  if (Crud::PAGE_INDEX === $pageName) {
              return [$id, $contenu, $fournisseur, $montant, $equipe, $raccourci];
          } elseif (Crud::PAGE_DETAIL === $pageName) {
              return [$id, $contenu, $fournisseur, $montant, $equipe, $raccourci];
          } elseif (Crud::PAGE_NEW === $pageName) {
              return [$contenu, $fournisseur, $montant, $equipe, $raccourci];
          } elseif (Crud::PAGE_EDIT === $pageName) {
              return [$contenu, $fournisseur, $montant, $equipe, $raccourci];
          }*/
        return [
            $id = IntegerField::new('id', 'ID')->onlyOnIndex(),
            $contenu = TextField::new('contenu'),
            $fournisseur = TextField::new('fournisseur'),
            $montant = NumberField::new('montant'),
            $raccourci = TextField::new('raccourci'),
            $equipe = AssociationField::new('equipe')->setFormType(EntityType::class)
                ->setFormTypeOptions(
                    [
                        'class' => Equipes::class,
                        'choices' => $equipesSansCadeau,
                    ]
                ),


        ];

    }


    #[Route("/Admin/CadeauxCrud/cadeaux_tableau_excel", name: "cadeaux_tableau_excel")]
    public function cadeauxstableauexcel()
    {

        $listEquipes = $this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
            ->join('e.equipeinter', 'eq')
            ->addOrderBy('eq.lettre', 'ASC')
            ->getQuery()->getResult();

        $edition = $this->requestStack->getSession()->get('edition');
        if (date('now') < $this->requestStack->getSession()->get('dateouverturesite')) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => $edition->getEd() - 1]);
        }
        $liste_cadeaux = [];
        $i = 0;
        foreach ($listEquipes as $equipe) {

            $liste_cadeaux[$i] = $equipe->getCadeau();
            $i = $i + 1;
        }

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
                ->setCellValue('C' . $ligne, $cadeau->getMontant() . ' €')
                ->setCellValue('D' . $ligne, $cadeau->getEquipe())
                ->setCellValue('E' . $ligne, $cadeau->getRaccourci());

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
