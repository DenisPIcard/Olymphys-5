<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CustomEditionFilter;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Professeurs;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\UnicodeString;

class ProfesseursCrudController extends AbstractCrudController
{

    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Professeurs::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $session = $this->requestStack->getSession();
        $exp = new UnicodeString('<sup>e</sup>');
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $edition = $session->get('edition');
        $editionEd = $edition->getEd();
        if (new Datetime('now') < $session->get('edition')->getDateouverturesite()) {
            $edition = $repositoryEdition->findOneBy(['ed' => $edition->getEd() - 1]);
            $editionEd = $edition->getEd();
        }
        $crud->setPageTitle('index', 'Liste des professeurs de la ' . $editionEd . $exp . ' édition ');
        if (isset($_REQUEST['filters']['edition'])) {
            $editionId = $_REQUEST['filters']['edition'];
            $editionEd = $repositoryEdition->findOneBy(['id' => $editionId]);
            $crud->setPageTitle('index', 'Liste des professeurs de la ' . $editionEd . $exp . ' édition ');
        }
        return $crud
            ->setPageTitle(Crud::PAGE_DETAIL, 'Professeur')
            ->setSearchFields(['id', 'lettre', 'numero', 'titreProjet', 'nomLycee', 'denominationLycee', 'lyceeLocalite', 'lyceeAcademie', 'prenomProf1', 'nomProf1', 'prenomProf2', 'nomProf2', 'uai', 'contribfinance', 'origineprojet', 'recompense', 'partenaire', 'description'])
            ->setPaginatorPageSize(50);

        //->overrideTemplates(['layout' => 'bundles/EasyAdminBundle/list_profs.html.twig',]);


    }

    public function configureActions(Actions $actions): Actions
    {
        $session = $this->requestStack->getSession();
        $edition = $session->get('edition');
        $editionId = $edition->getId();
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        if (new Datetime('now') < $session->get('edition')->getDateouverturesite()) {
            $edition = $repositoryEdition->findOneBy(['ed' => $edition->getEd() - 1]);
            $editionId = $edition->getId();
        }
        if (isset($_REQUEST['filters']['edition'])) {

            $editionId = $_REQUEST['filters']['edition'];
        }


        $tableauexcel = Action::new('profs_tableau_excel', 'Créer un tableau excel des professeurs', 'fas fa-columns')
            // if the route needs parameters, you can define them:
            // 1) using an array
            ->linkToRoute('profs_tableau_excel', ['idEdition' => $editionId])
            ->createAsGlobalAction();
        //->displayAsButton()->setCssClass('btn btn-primary');
        $tableauexcelsel = Action::new('profs_tableau_excel_sel', 'Créer un tableau excel des professeurs sélectionnés', 'fas fa-columns')
            // if the route needs parameters, you can define them:
            // 1) using an array
            ->linkToRoute('profs_tableau_excel_sel', ['idEdition' => $editionId])
            ->createAsGlobalAction();
        $tableauexcelmailing = Action::new('profs_tableau_excel_mailing', 'Créer un tableau excel des professeurs pour mailings', 'fas fa-columns')
            // if the route needs parameters, you can define them:
            // 1) using an array
            ->linkToRoute('profs_tableau_excel_mailing', ['idEdition' => $editionId])
            ->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $tableauexcel)
            ->add(Crud::PAGE_INDEX, $tableauexcelsel)
            ->add(Crud::PAGE_INDEX, $tableauexcelmailing)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);

    }

    public function configureFields(string $pageName): iterable
    {
        $nom = IntegerField::new('user.nom', 'nom');
        $prenom = TextField::new('user.prenom', 'Prénom');
        $nomLycee = TextField::new('user.uaiId.nom', 'Lycée');
        $lyceeLocalite = TextField::new('user.uaiId.commune', 'Ville');
        $lyceeAcademie = TextField::new('user.uaiId.academie', 'Académie');
        $uai = TextField::new('user.uai', 'Code UAI');
        $equipes = IntegerField::new('equipesstring', 'Equipes');
        $telephone = TextField::new('user.phone', 'Téléphone');
        $mail = EmailField::new('user.email', 'Mail');
        $adresse = TextField::new('user.adresse', 'Adresse');
        $ville = TextField::new('user.ville', 'Ville');
        $code = TextField::new('user.code', 'CP');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$prenom, $nom, $nomLycee, $lyceeLocalite, $lyceeAcademie, $uai, $equipes];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$prenom, $nom, $nomLycee, $lyceeLocalite, $lyceeAcademie, $uai, $equipes, $mail, $telephone, $adresse, $code, $ville];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$prenom, $nom, $nomLycee, $lyceeLocalite, $lyceeAcademie, $uai, $equipes];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$prenom, $nom, $nomLycee, $lyceeLocalite, $lyceeAcademie, $uai, $equipes];
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomEditionFilter::new('edition'));

    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $session = $this->requestStack->getSession();
        $context = $this->adminContextProvider->getContext();
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);

        if (!isset($_REQUEST['filters'])) {
            $edition = $repositoryEdition->findOneBy(['id' => $session->get('edition')->getId()]);//afin de charger l'objet $edition

            if (new Datetime('now') < $session->get('edition')->getDateouverturesite()) {
                $edition = $repositoryEdition->findOneBy(['ed' => $edition->getEd() - 1]);
            }
        } else {
            if (isset($_REQUEST['filters']['edition'])) {

                $idEdition = $_REQUEST['filters']['edition'];
                $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
                $session->set('titreedition', $edition);
            }


        }
        $qb = $this->doctrine->getRepository(Professeurs::class)->createQueryBuilder('p')
            ->leftJoin('p.equipes', 'eq')
            ->where('eq.edition =:edition')
            ->setParameter('edition', $edition)
            ->leftJoin('p.user', 'u')
            ->orderBy('u.nom', 'ASC');;
        $this->set_equipeString($edition, $qb);
        return $qb;
    }

    public function set_equipeString($edition, $qb)
    {//Equipesstring est un champ à contenu variable destiné à l'affichage des équipes d'un prof pour une session dans l'admin
        $em = $this->doctrine->getManager();
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $listProfs = $qb->getQuery()->getResult();

        if ($listProfs != null) {
            foreach ($listProfs as $prof) {
                $equipestring = '';
                $equipes = $repositoryEquipes->createQueryBuilder('e')
                    ->where('e.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->andWhere('e.idProf1 =:user OR e.idProf2 =:user')
                    ->setParameter('user', $prof->getUser())
                    ->getQuery()->getResult();

                if ($equipes != null) {
                    foreach ($equipes as $equipe) {
                        if ($equipe->getIdProf1() == $prof->getUser()) {
                            $encad = '(prof1)';
                        }
                        if ($equipe->getIdProf2() == $prof->getUser()) {
                            $encad = '(prof2)';
                        }


                        $slugger = new AsciiSlugger();
                        $nom_equipe = $slugger->slug($equipe->getTitreProjet());
                        if (strlen($equipe->getTitreProjet() > 40)) {

                            $nom_equipe = substr($nom_equipe, 0, 40);
                        }
                        $equipestring = $equipestring . $nom_equipe . $encad;
                        if (next($equipes) != null) {
                            $equipestring = $equipestring . ' || ';
                        }
                    }

                    $prof->setEquipesstring($equipestring);
                    $em->persist($prof);
                    $em->flush();
                }
            }

        }


    }

    #[Route("/Professeurs/editer_tableau_excel,{idEdition}", name: "profs_tableau_excel")]
    public function editer_tableau_excel($idEdition)
    {


        $em = $this->doctrine->getManager();
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
        $repositoryProfs = $this->doctrine->getManager()->getRepository(Professeurs::class);

        $queryBuilder = $repositoryProfs->createQueryBuilder('p')
            ->leftJoin('p.equipes', 'eqs')
            ->andWhere('eqs.edition =:edition')
            ->setParameter('edition', $edition)
            ->leftJoin('p.user', 'u')
            ->orderBY('u.nom', 'ASC');
        $listProfs = $queryBuilder->getQuery()->getResult();

        if ($listProfs != null) {
            foreach ($listProfs as $prof) {
                $equipestring = '';

                $equipes = $repositoryEquipes->createQueryBuilder('e')
                    ->where('e.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->andWhere('e.idProf1 =:user OR e.idProf2 =:user')
                    ->setParameter('user', $prof->getUser())
                    ->getQuery()->getResult();

                if ($equipes != null) {
                    foreach ($equipes as $equipe) {
                        if ($equipe->getIdProf1() == $prof->getUser()) {
                            $encad = '(prof1)';
                        }
                        if ($equipe->getIdProf2() == $prof->getUser()) {
                            $encad = '(prof2)';
                        }
                        $slugger = new AsciiSlugger();
                        $nom_equipe = $slugger->slug($equipe->getTitreProjet());
                        if (strlen($equipe->getTitreProjet() > 40)) {

                            $nom_equipe = substr($nom_equipe, 0, 40);
                        }
                        $equipestring = $equipestring . $nom_equipe . $encad;
                        if (next($equipes) != null) {
                            $equipestring = $equipestring . "\n";
                        }
                    }

                    $prof->setEquipesstring($equipestring);
                    $em->persist($prof);
                    $em->flush();
                }
            }
        }


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("OdPF" . $edition->getEd() . "ème édition - professeurs encadrants")
            ->setSubject("PROFESSEURS")
            ->setDescription("Office 2007 XLSX Document pour comité")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);

        }
        $sheet->setCellValue('A1', 'Professeurs de la ' . $edition->getEd() . 'e' . ' édition');

        $ligne = 2;


        $sheet->setCellValue('A' . $ligne, 'Nom')
            ->setCellValue('B' . $ligne, 'Prénom')
            ->setCellValue('C' . $ligne, 'Adresse')
            ->setCellValue('D' . $ligne, 'Ville')
            ->setCellValue('E' . $ligne, 'Code Postal')
            ->setCellValue('F' . $ligne, 'Courriel')
            ->setCellValue('G' . $ligne, 'téléphone')
            ->setCellValue('H' . $ligne, 'Code UAI')
            ->setCellValue('I' . $ligne, 'Lycée')
            ->setCellValue('J' . $ligne, 'Commune lycée')
            ->setCellValue('K' . $ligne, 'Académie')
            ->setCellValue('L' . $ligne, 'Equipes');;

        $ligne += 1;

        foreach ($listProfs as $prof) {


            $sheet->setCellValue('A' . $ligne, $prof->getUser()->getNom())
                ->setCellValue('B' . $ligne, $prof->getUser()->getPrenom())
                ->setCellValue('C' . $ligne, $prof->getUser()->getAdresse())
                ->setCellValue('D' . $ligne, $prof->getUser()->getVille())
                ->setCellValue('E' . $ligne, $prof->getUser()->getCode())
                ->setCellValue('F' . $ligne, $prof->getUser()->getEmail())
                ->getCell('G' . $ligne)->setValueExplicit($prof->getUser()->getPhone(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            if ($prof->getUser()->getUaiId() !== null) {
                $sheet->setCellValue('H' . $ligne, $prof->getUser()->getUaiId()->getUai())
                    ->setCellValue('I' . $ligne, $prof->getUser()->getUaiId()->getNom())
                    ->setCellValue('J' . $ligne, $prof->getUser()->getUaiId()->getCommune())
                    ->setCellValue('K' . $ligne, $prof->getUser()->getUaiId()->getAcademie());
            }

            //$equipesstring = explode('-', $prof->getEquipesstring());

            $sheet->getCell('L' . $ligne)->setValueExplicit($prof->getEquipesstring());//'abc \n cde'
            $sheet->getStyle('A' . $ligne . ':L' . $ligne)->getAlignment()->setWrapText(true);

            $sheet->getRowDimension($ligne)->setRowHeight(2 * count($equipes), 'cm');
            $ligne += 1;
        }


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="professeurs.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        ob_end_clean();
        $writer->save('php://output');


    }

    #[Route("/Professeurs/editer_tableau_excel_sel,{idEdition}", name: "profs_tableau_excel_sel")]
    public function editer_tableau_excel_sel($idEdition)
    {


        $em = $this->doctrine->getManager();
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
        $repositoryProfs = $this->doctrine->getManager()->getRepository(Professeurs::class);

        $queryBuilder = $repositoryProfs->createQueryBuilder('p')
            ->groupBy('p.user')
            ->leftJoin('p.equipes', 'eqs')
            ->andWhere('eqs.edition =:edition')
            ->andWhere('eqs.selectionnee = true')
            ->setParameter('edition', $edition)
            ->leftJoin('p.user', 'u')
            ->orderBY('u.nom', 'ASC');
        $listProfs = $queryBuilder->getQuery()->getResult();

        if ($listProfs != null) {
            foreach ($listProfs as $prof) {
                $equipestring = '';

                $equipes = $repositoryEquipes->createQueryBuilder('e')
                    ->where('e.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->andWhere('e.idProf1 =:user OR e.idProf2 =:user')
                    ->setParameter('user', $prof->getUser())
                    ->getQuery()->getResult();

                if ($equipes != null) {
                    foreach ($equipes as $equipe) {
                        if ($equipe->getIdProf1() == $prof->getUser()) {
                            $encad = '(prof1)';
                        }
                        if ($equipe->getIdProf2() == $prof->getUser()) {
                            $encad = '(prof2)';
                        }
                        $equipestring = $equipestring . $equipe->getTitreProjet() . $encad;
                        if (next($equipes) != null) {
                            $equipestring = $equipestring . "\n";
                        }
                    }
                    $equipestring = count($equipes) . '-' . $equipestring;
                    $prof->setEquipesstring($equipestring);
                    $em->persist($prof);
                    $em->flush();
                }
            }
        }


        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("OdPF" . $edition->getEd() . "ème édition - professeurs sélectionnés")
            ->setSubject("PROFESSEURS")
            ->setDescription("Office 2007 XLSX Document pour comité")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);

        }
        $sheet->setCellValue('A1', 'Professeurs de la ' . $edition->getEd() . 'e' . ' édition');

        $ligne = 2;


        $sheet->setCellValue('A' . $ligne, 'Nom')
            ->setCellValue('B' . $ligne, 'Prénom')
            ->setCellValue('C' . $ligne, 'Adresse')
            ->setCellValue('D' . $ligne, 'Ville')
            ->setCellValue('E' . $ligne, 'Code Postal')
            ->setCellValue('F' . $ligne, 'Courriel')
            ->setCellValue('G' . $ligne, 'téléphone')
            ->setCellValue('H' . $ligne, 'Code UAI')
            ->setCellValue('I' . $ligne, 'Lycée')
            ->setCellValue('J' . $ligne, 'Commune lycée')
            ->setCellValue('K' . $ligne, 'Académie')
            ->setCellValue('L' . $ligne, 'Equipes');;

        $ligne += 1;

        foreach ($listProfs as $prof) {


            $sheet->setCellValue('A' . $ligne, $prof->getUser()->getNom())
                ->setCellValue('B' . $ligne, $prof->getUser()->getPrenom())
                ->setCellValue('C' . $ligne, $prof->getUser()->getAdresse())
                ->setCellValue('D' . $ligne, $prof->getUser()->getVille())
                ->setCellValue('E' . $ligne, $prof->getUser()->getCode())
                ->setCellValue('F' . $ligne, $prof->getUser()->getEmail())
                ->getCell('G' . $ligne)->setValueExplicit($prof->getUser()->getPhone(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $ligne, $prof->getUser()->getUaiId()->getUai())
                ->setCellValue('I' . $ligne, $prof->getUser()->getUaiId()->getNom())
                ->setCellValue('J' . $ligne, $prof->getUser()->getUaiId()->getCommune());
            $sheet->setCellValue('K' . $ligne, $prof->getUser()->getUaiId()->getAcademie());

            $equipesstring = explode('-', $prof->getEquipesstring());
            $sheet->getRowDimension($ligne)->setRowHeight(12.5 * intval($equipesstring[0]));
            $sheet->getCell('L' . $ligne)->setValueExplicit($equipesstring[1], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);//'abc \n cde'
            $sheet->getStyle('A' . $ligne . ':L' . $ligne)->getAlignment()->setWrapText(true);
            $ligne += 1;
        }


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="professeurs.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        ob_end_clean();
        $writer->save('php://output');


    }

    #[Route("/Professeurs/editer_tableau_excel_mailing,{idEdition}", name: "profs_tableau_excel_mailing")]
    public function editer_tableau_excel_mailing($idEdition)
    {


        $em = $this->doctrine->getManager();
        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $repositoryEquipes = $this->doctrine->getRepository(Equipesadmin::class);
        $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
        $repositoryProfs = $this->doctrine->getManager()->getRepository(Professeurs::class);

        $queryBuilder = $repositoryProfs->createQueryBuilder('p')
            ->leftJoin('p.equipes', 'eqs')
            ->andWhere('eqs.edition =:edition')
            ->setParameter('edition', $edition)
            ->leftJoin('p.user', 'u')
            ->orderBY('u.nom', 'ASC');
        $listProfs = $queryBuilder->getQuery()->getResult();
        $listProfs = $queryBuilder->getQuery()->getResult();
        $listeEquipeProfs = [];
        if ($listProfs != null) {
            foreach ($listProfs as $prof) {
                $n = 0;
                $equipes = $repositoryEquipes->createQueryBuilder('e')
                    ->where('e.edition =:edition')
                    ->setParameter('edition', $edition)
                    ->andWhere('e.idProf1 =:user OR e.idProf2 =:user')
                    ->setParameter('user', $prof->getUser())
                    ->getQuery()->getResult();

                if ($equipes != null) {
                    foreach ($equipes as $equipe) {
                        $listeEquipeProfs[$prof->getId()][$n] = $equipe;
                        $n++;
                    }
                }
            }

        }
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("Olymphys")
            ->setLastModifiedBy("Olymphys")
            ->setTitle("OdPF" . $edition->getEd() . "ème édition - liste des professeurs et de leurs équipes")
            ->setSubject("PROFESSEURS")
            ->setDescription("Office 2007 XLSX Document pour comité")
            ->setKeywords("Office 2007 XLSX")
            ->setCategory("Test result file");

        $sheet = $spreadsheet->getActiveSheet();
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $letter) {
            $sheet->getColumnDimension($letter)->setAutoSize(true);

        }
        $sheet->setCellValue('A1', 'Professeurs de la ' . $edition->getEd() . 'e' . ' édition');

        $ligne = 2;


        $sheet->setCellValue('A' . $ligne, 'Nom')
            ->setCellValue('B' . $ligne, 'Prénom')
            ->setCellValue('C' . $ligne, 'Courriel')
            ->setCellValue('D' . $ligne, 'Centre Cia')
            ->setCellValue('E' . $ligne, 'N° Equipe')
            ->setCellValue('F' . $ligne, 'Nom Equipe');


        $ligne += 1;

        foreach ($listProfs as $prof) {
            foreach ($listeEquipeProfs[$prof->getId()] as $equipe) {
                $sheet->setCellValue('A' . $ligne, $prof->getUser()->getNom())
                    ->setCellValue('B' . $ligne, $prof->getUser()->getPrenom())
                    ->setCellValue('C' . $ligne, $prof->getUser()->getEmail())
                    ->setCellValue('D' . $ligne, $equipe->getCentre()->getCentre())
                    ->setCellValue('E' . $ligne, $equipe->getNumero())
                    ->setCellValue('F' . $ligne, $equipe->getTitreProjet());

                $ligne += 1;
            }
        }


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="professeurs_mailing.xls"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
        ob_end_clean();
        $writer->save('php://output');


    }

}
