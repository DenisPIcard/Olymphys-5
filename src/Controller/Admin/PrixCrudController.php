<?php

namespace App\Controller\Admin;

use AllowDynamicProperties;
use App\Entity\Equipes;
use App\Entity\Prix;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[AllowDynamicProperties] class PrixCrudController extends AbstractCrudController
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
        return Prix::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un prix')
            ->setSearchFields(['id', 'prix', 'niveau', 'voix', 'intervenant', 'remisPar'])
            ->setDefaultSort(['niveau' => 'ASC'])
            ->setPaginatorRangeSize(26);
    }

    public function configureFields(string $pageName): iterable
    {

        if (isset($_REQUEST['entityId'])){
            $id=$_REQUEST['entityId'];
            $prixEquipe=$this->doctrine->getRepository(Prix::class)->findOneBy(['id'=>$id]);
            $equipe= $prixEquipe->getEquipe();
        }

        $equipesSansPrix=$this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
                    ->where('e.prix=:value')
                    ->setParameter('value', 'null')
                    ->getQuery()->getResult();

        if (isset($equipe)){
            $equipesSansPrix[count($equipesSansPrix)]=$equipe;
        }
        $prix = TextField::new('prix');;
        $niveau = TextField::new('niveau');
        $attribue = BooleanField::new('attribue');
        $voix = TextField::new('voix');
        $intervenant = TextField::new('intervenant');
        $remisPar = TextField::new('remisPar');
        $id = IntegerField::new('id', 'ID');
        $equipe=AssociationField::new('equipe')->setFormType(EntityType::class)->setFormTypeOptions(
            ['class'=>Equipes::class,
              'choices' =>$equipesSansPrix,

            ]
        );

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $prix, $niveau, $attribue,$equipe, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $prix, $niveau, $attribue, $equipe, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$prix, $niveau, $attribue, $equipe, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$prix, $niveau, $attribue,$equipe, $voix, $intervenant, $remisPar];
        }
    }

    public function configureActions(Actions $actions): Actions
    {

        $uploadPrix = Action::new('excel_prix', 'Charger les prix', 'fa fa-upload')
            ->linkToRoute('secretariatjury_excel_prix')
            ->createAsGlobalAction();
        $tableauExcel=Action::new('prix_tableau_excel','Extraire un tableau Excel', 'fa fa_array')
            ->linkToRoute('prix_tableau_excel')
            ->createAsGlobalAction();

        return $actions->add(Crud::PAGE_INDEX, $uploadPrix)
                        ->add(Crud::PAGE_INDEX, $tableauExcel)
                        ->add(Crud::PAGE_EDIT,'index');
    }
    #[Route("/Admin/PrixCrud/prix_tableau_excel", name:"prix_tableau_excel")]
    public function prixtableauexcel()
    {
        $repositoryPrix = $this->doctrine->getRepository(Prix::class);
        $edition = $this->requeststack->getSession()->get('edition');
        $liste_prix = $repositoryPrix->findAll();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("CN - " . $edition->getEd() . "e -Tableau destiné au comité")
            ->setSubject("Tableau destiné au comité")
            ->setDescription("Office 2007 XLSX liste des prix")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }
        $ligne = 1;
        $sheet->setCellValue('A' . $ligne, 'Niveau')
            ->setCellValue('B' . $ligne, 'Prix')
            ->setCellValue('C' . $ligne, 'Equipe')
            ->setCellValue('D' . $ligne, 'Voix')
            ->setCellValue('E' . $ligne, 'intervanat')
            ->setCellValue('F' . $ligne, 'remis par');

        $ligne += 1;
        foreach ($liste_prix as $prix) {
            $sheet->setCellValue('A' . $ligne, $prix->getNiveau())
                ->setCellValue('B' . $ligne, $prix->getPrix())
                ->setCellValue('C' . $ligne, $prix->getEquipe())
                ->setCellValue('D' . $ligne, $prix->getVoix())
                ->setCellValue('E' . $ligne, $prix->getIntervenant())
                ->setCellValue('F' . $ligne, $prix->getRemisPar());

            $ligne += 1;
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="prix.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        //$writer= PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer =  \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        // $writer =IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_end_clean();
        $writer->save('php://output');


    }



}