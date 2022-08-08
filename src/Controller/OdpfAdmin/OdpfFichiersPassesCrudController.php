<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Odpf\OdpfFichierspasses;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Form\Type\VichFileType;

class OdpfFichiersPassesCrudController extends AbstractCrudController
{
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;
    private RequestStack $requestStack;

    public function __construct(AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine, RequestStack $requestack)
    {
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;
        $this->requestStack = $requestack;
    }

    public static function getEntityFqcn(): string
    {
        return OdpfFichierspasses::class;
    }

    public function set_type_fichier($valueIndex, $valueSubIndex): int
    {
        if ($valueIndex == 6) {
            switch ($valueSubIndex) {
                case 2 :
                    $typeFichier = 0; //mémoires ou annexes 1
                    break;
                case 3:
                    $typeFichier = 2;  //résumés
                    break;

                case 4 :
                    $typeFichier = 3; //Présentations
                    break;

                case 5:
                    $typeFichier = 6; //Autorisations photos
                    break;
            }
        }

        return $typeFichier;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $context = $this->adminContextProvider->getContext();
        $typefichier = $context->getRequest()->query->get('typefichier');
        $qb = $this->doctrine->getRepository(OdpfFichierspasses::class)->createQueryBuilder('f');

        if (($typefichier == 0) or ($typefichier == 1)) {
            $qb->andWhere('f.typefichier <=:type')
                ->setParameter('type', 1);
        } else {
            $qb->andWhere('f.typefichier =:type')
                ->setParameter('type', $typefichier);

        }
        $qb->leftJoin('f.equipepassee', 'eq')
            ->leftJoin('f.editionspassees', 'ed')
            //->addOrderBy('eq.numero','ASC')
            ->addOrderBy('ed.edition', 'DESC');
        return $qb;
    }


    public function configureFields(string $pageName): iterable

    {
        $repositoryEdition = $this->doctrine->getRepository(OdpfEquipesPassees::class);
        $numtypefichier = $this->set_type_fichier($_REQUEST['menuIndex'], $_REQUEST['submenuIndex']);

        if ($pageName == Crud::PAGE_NEW) {

            $panel1 = FormField::addPanel('<p style= "color :red" > Déposer un nouveau ' . $this->getParameter('type_fichier_lit')[$this->set_type_fichier($_REQUEST['menuIndex'], $_REQUEST['submenuIndex'])] . '  </p> ');
            $numtypefichier = $this->set_type_fichier($_REQUEST['menuIndex'], $_REQUEST['submenuIndex']);

        }
        if ($pageName == Crud::PAGE_EDIT) {

            $panel1 = FormField::addPanel('<p style= "color:red" > Editer le fichier ' . $this->getParameter('type_fichier_lit')[$this->set_type_fichier($_REQUEST['menuIndex'], $_REQUEST['submenuIndex'])] . '  </p> ');
            $numtypefichier = $this->set_type_fichier($_REQUEST['menuIndex'], $_REQUEST['submenuIndex']);//La valeur du paramètre du dashController est perdue lors de l'affichage de l'index

        }

        $equipe = AssociationField::new('equipepassee')->setFormTypeOptions(['data_class' => null])
            ->setQueryBuilder(function ($queryBuilder) {

                return $queryBuilder->select()->addOrderBy('entity.editionspassees', 'DESC')
                    ->addOrderBy('entity.lettre', 'ASC')
                    ->addOrderBy('entity.numero', 'ASC');
            }
            );
        $fichierFile = Field::new('fichierFile', 'fichier')
            ->setFormType(VichFileType::class)
            ->setLabel('Fichier')
            ->onlyOnForms()
            ->setFormTypeOption('allow_delete', false);//sinon la case à cocher delete s'affiche
        //$numtypefichier=$this->set_type_fichier($_REQUEST['menuIndex'],$_REQUEST['submenuIndex']);
        switch ($numtypefichier) {
            case 0 :
                $article = 'le';
                break;
            case 1 :
                $article = 'l\'';
                break;
            case 2 :
                $article = 'le';
                break;
            case 3 :
                $article = 'le';
                break;
            case 4 :
                $article = 'la';
                break;
            case 5 :
                $article = 'le';
                break;
            case 6 :
                $article = 'l\'';
                break;
            case 7 :
                $article = 'le';
                break;
        }

        $panel2 = FormField::addPanel('<p style=" color:red" > Modifier ' . $article . ' ' . $this->getParameter('type_fichier_lit')[$this->set_type_fichier($_REQUEST['menuIndex'], $_REQUEST['submenuIndex'])] . '</p> ');
        $id = IntegerField::new('id', 'ID');
        $fichier = TextField::new('nomfichier')->setTemplatePath('bundles\\EasyAdminBundle\\liste_fichiers.html.twig');


        $typefichier = IntegerField::new('typefichier');
        if ($pageName == Crud::PAGE_INDEX) {
            $context = $this->adminContextProvider->getContext();
            $context->getRequest()->query->set('typefichier', $_REQUEST['typefichier']);
        }
        $annexe = ChoiceField::new('typefichier', 'Mémoire ou annexe')
            ->setChoices(['Memoire' => 0, 'Annexe' => 1])
            ->setFormTypeOptions(['required' => true])
            ->setColumns('col-sm-4 col-lg-3 col-xxl-2');
        $updatedAt = DateTimeField::new('updatedAt');
        $edition = AssociationField::new('editionpassee');

        $editionEd = IntegerField::new('editionspassees.edition');
        $equipeNumero = IntegerField::new('equipepassee.numero');
        $equipeLettre = TextareaField::new('equipepassee.lettre');
        $equipeTitreprojet = TextareaField::new('equipepassee.titreprojet');
        $updatedat = DateTimeField::new('updatedat', 'Déposé le ');

        if (Crud::PAGE_INDEX === $pageName) {

            return [$editionEd, $equipeNumero, $equipeLettre, $equipeTitreprojet, $fichier, $updatedat];

        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $fichier, $typefichier, $updatedAt, $edition, $equipe];
        }
        if (Crud::PAGE_NEW === $pageName) {


            if ($numtypefichier == 0) {
                return [$panel1, $equipe, $fichierFile, $annexe];
            }
            if (($numtypefichier == 2) or ($numtypefichier == 3) or ($numtypefichier == 4) or ($numtypefichier == 5)) {
                return [$panel1, $equipe, $fichierFile];
            }
            if ($numtypefichier == 6) {

                return [$panel1, $fichierFile];
            }
        }
        if (Crud::PAGE_EDIT === $pageName) {


            if ($numtypefichier == 0) {
                return [$panel1, $equipe, $fichierFile, $annexe];
            }
            if (($numtypefichier == 2) or ($numtypefichier == 3) or ($numtypefichier == 4) or ($numtypefichier == 5)) {
                return [$panel1, $equipe, $fichierFile];
            }
            if ($numtypefichier == 6) {

                return [$panel1, $equipe, $fichierFile];
            }
        }

    }
}
