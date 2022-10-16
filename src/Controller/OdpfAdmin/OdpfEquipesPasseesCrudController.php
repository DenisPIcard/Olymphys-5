<?php

namespace App\Controller\OdpfAdmin;

use App\Controller\Admin\Filter\CustomEditionspasseesFilter;
use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Rne;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

class OdpfEquipesPasseesCrudController extends AbstractCrudController
{
    private EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {

        $this->doctrine = $doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return OdpfEquipesPassees::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomEditionspasseesFilter::new('editionspassees', 'edition'))
            ->add(BooleanFilter::new('selectionnee'));


    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('editionspassees.edition', 'Edition'),
            TextField::new('numero'),
            TextField::new('lettre'),
            TextField::new('titreProjet'),
            TextField::new('lycee'),
            TextField::new('ville'),
            TextField::new('academie'),
            TextField::new('profs'),
            TextField::new('eleves'),
            BooleanField::new('selectionnee')

        ];
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $repositoryEdition = $this->doctrine->getRepository(OdpfEditionsPassees::class);
        $qb = $this->doctrine->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e');
        $qb->leftJoin('e.editionspassees', 'ed')
            ->addOrderBy('ed.edition', 'DESC')
            ->addOrderBy('e.numero', 'ASC');;
        if (isset($_REQUEST['filters']['editionspassees'])) {

            $idEdition = $_REQUEST['filters']['editionspassees'];

            $qb->andWhere('e.editionspassees =:edition')
                ->setParameter('edition', $repositoryEdition->findOneBy(['id' => $idEdition]));

        }


        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        $ajoute_equipe = Action::new('ajouter_equipe_passee', 'Ajouter des équipes passée', 'fa fa-file-download')
            ->linkToRoute('charger_equipes_passees')->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $ajoute_equipe)
            ->setPermission($ajoute_equipe, 'ROLE_SUPER_ADMIN');;
        return parent::configureActions($actions); // TODO: Change the autogenerated stub
    }

    /**
     * @Route("/OdpfAdmin/Crud/charger-equipes_passees",name="charger_equipes_passees")
     */
    public function charger_equipes_passees(Request $request)
    {// A partir du tableau excel fourni par odpf fonction provisoire pour la transfert odpf vers olymphys
        $form = $this->createFormBuilder()
            ->add(
                'file', FileType::class
            )
            ->add('edition', ChoiceType::class, [
                'choices' => range(1, 30),
                'label' => 'Choisir le numéro de l\'édition'

            ])
            ->add('submit', SubmitType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $fichier = $form->get('file')->getData();
            $numeroEd = $form->get('edition')->getData() - 1;
            $editionPasseeRepository = $this->doctrine->getRepository(OdpfEditionsPassees::class);
            $equipesPasseeRepository = $this->doctrine->getRepository(OdpfEquipesPassees::class);
            $edition = $editionPasseeRepository->findOneBy(['edition' => $numeroEd]);
            $equipes = $equipesPasseeRepository->findBy(['editionspassees' => $edition]);

            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();


            for ($row = 2; $row <= $highestRow; ++$row) {

                $titreProjet = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $rne = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                $rneObj = $this->doctrine->getRepository(Rne::class)->findOneBy(['rne' => $rne]);
                $numeroEquipe = intval($worksheet->getCellByColumnAndRow(4, $row)->getValue());
                $lettreEquipe = $worksheet->getCellByColumnAndRow(5, $row)->getValue();


                if ($rneObj !== null) {
                    $nomLycee = $rneObj->getNom();
                    $localiteLycee = $rneObj->getCommune();
                    $academieLycee = $rneObj->getAcademie();

                }
                $prenomProf1 = u(u($worksheet->getCellByColumnAndRow(22, $row)->getValue())->lower())->camel()->title()->toString();
                $nomProf1 = strtoupper($worksheet->getCellByColumnAndRow(23, $row)->getValue());
                $profs = $prenomProf1 . ' ' . $nomProf1;
                $prenomProf2 = u(u($worksheet->getCellByColumnAndRow(24, $row)->getValue())->lower())->camel()->title()->toString();
                $nomProf2 = strtoupper($worksheet->getCellByColumnAndRow(25, $row)->getValue());
                if ($prenomProf2 !== '') {
                    $profs = $profs . ', ' . $prenomProf2 . ' ' . $nomProf2;
                }
                $equipe = $equipesPasseeRepository->createQueryBuilder('e')
                    ->where('e.editionspassees =:edition')
                    ->andWhere('e.numero =:numero or e.lettre =:lettre')
                    ->setParameters(['edition' => $edition, 'numero' => $numeroEquipe, 'lettre' => $lettreEquipe])
                    ->getQuery()->getOneOrNullResult();
                if ($equipe === null) {
                    $equipe = new OdpfEquipesPassees();
                    $equipe->setEditionspassees($edition);

                }
                $equipe->setNumero($numeroEquipe);
                if ($lettreEquipe == '~') {
                    $equipe->setLettre(null);
                } else {
                    $equipe->setLettre(($lettreEquipe));
                    $equipe->setSelectionnee(true);
                }
                $equipe->setTitreProjet($titreProjet);
                $equipe->setLycee($nomLycee);
                $equipe->setVille($localiteLycee);
                $equipe->setAcademie(($academieLycee));
                $equipe->setProfs($profs);
                $this->doctrine->persist($equipe);
                $this->doctrine->flush();
            }
            $this->modifTexteEdition($edition);
            return $this->redirectToRoute('odpfadmin');
        }
        return $this->renderForm('recup_odpf/recup-profs-eleves.html.twig', array('form' => $form));


    }

    public function modifTexteEdition($edition)
    {
        $article = $this->doctrine->getRepository(OdpfArticle::class)->findOneBy(['choix' => 'edition' . $edition->getEdition()]);
        $texte = $article->getTexte();
        $intro = explode('<ul>', $texte)[0];
        $texteEquipes = explode('<ul>', $texte)[1];

        $equipes = $this->doctrine->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e')
            ->andWhere('e.editionspassees =:edition')
            ->setParameter('edition', $edition)
            ->addOrderBy('e.selectionnee', 'DESC')
            ->addOrderBy('e.lettre', 'ASC')
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();
        $nouveauTexte = '';
        foreach ($equipes as $equipe) {
            $equipe->getLettre() ? $codeEquipe = $equipe->getLettre() : $codeEquipe = $equipe->getNumero();

            $nouveauTexte = $nouveauTexte . '<li><a href="/../public/index.php/odpf/editionspassees/equipe,' . $equipe->getId() . '"> ' . $codeEquipe . '- ' . $equipe->getTitreProjet() . '</a>, Lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</a></li>';
        }
        $nouveauTexte = '<ul>' . $nouveauTexte . '</ul>';
        $texte = $intro . $nouveauTexte;
        $article->setTexte($texte);
        $this->doctrine->persist($article);
        $this->doctrine->flush();

    }
}