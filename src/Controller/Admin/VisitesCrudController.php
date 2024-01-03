<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
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

    protected EntityManagerInterface $doctrine;
    protected RequestStack $requeststack;

    public function __Construct(EntityManagerInterface $doctrine, RequestStack $requestStack)
    {
        $this->doctrine = $doctrine;
        $this->requeststack = $requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Visites::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une visite')
            ->setSearchFields(['intitule']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $tableauExcel = Action::new('visites_tableau_excel', 'Extraire un tableau Excel', 'fa fa_array')
            ->linkToRoute('visites_tableau_excel')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $tableauExcel)
            ->add(Crud::PAGE_EDIT, 'index');
    }

    public function configureFields(string $pageName): iterable
    {
        $equipesSansVisite = $this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
            ->where('e.visite is NULL')
            ->getQuery()->getResult();
        $listeEquipes = $this->doctrine->getRepository(Equipes::class)->findAll();

        if (isset($_REQUEST['entityId'])) {//apparaît lors de l'edit
            $id = $_REQUEST['entityId'];
            $visiteEquipe = $this->doctrine->getRepository(Visites::class)->find($id);
            $equipe = $visiteEquipe->getEquipe();

            if (isset($equipe)) {
                $equipesSansVisite[count($equipesSansVisite)] = $equipe;//pour afficher la valeur de l'équipe dans le formulaire, elle est ajouté à la liste des équipes à la fin
            }
        }
        $intitule = TextField::new('intitule');
        $id = IntegerField::new('id', 'ID');
        $equipe = AssociationField::new('equipe')
            ->setFormType(EntityType::class)
            ->setFormTypeOptions([
                'class' => Equipes::class,
                'choices' => $equipesSansVisite

            ]);

        if (Crud::PAGE_INDEX === $pageName) {
            return [$intitule, $equipe];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $intitule, $equipe];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$intitule, $equipe];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$intitule, $equipe];
        }

    }


    #[Route("/Admin/VisitesCrud/visites_tableau_excel", name: "visites_tableau_excel")]
    public function visitestableauexcel()
    {
        $listEquipes = $this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
            ->join('e.equipeinter', 'eq')
            ->addOrderBy('eq.lettre', 'ASC')
            ->getQuery()->getResult();

        $edition = $this->requeststack->getSession()->get('edition');
        if (date('now') < $this->requeststack->getSession()->get('dateouverturesite')) {
            $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => $edition->getEd() - 1]);
        }
        $liste_visites = [];
        $i = 0;
        foreach ($listEquipes as $equipe) {

            $liste_visites[$i] = $equipe->getVisite();
            $i = $i + 1;
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
                ->setCellValue('B' . $ligne, $visite->getEquipe());

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
