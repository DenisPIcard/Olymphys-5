<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfArticle;

use App\Entity\Odpf\OdpfCarousels;
use App\Entity\Odpf\OdpfCategorie;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

class OdpfArticleCrudController extends AbstractCrudController
{

    private ManagerRegistry $doctrine;
    private AdminContextProvider $adminContextProvider;

    public function __construct(ManagerRegistry $doctrine, AdminContextProvider $adminContextProvider)
    {

        $this->doctrine = $doctrine;
        $this->adminContextProvider = $adminContextProvider;
    }

    public static function getEntityFqcn(): string
    {
        return OdpfArticle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $listCarousels = $this->doctrine->getRepository(OdpfCarousels::class)->findAll();
        $idField = IdField::new('id')->hideOnForm();

        // Add a tab
        $tab1 = FormField::addTab('Article ');

        // You can use a Form Panel inside a Form Tab
        $panel1 = FormField::addPanel('Donnéees');
        $panel2 = FormField::addPanel('Autre');
        $titre = TextField::new('titre');
        $choix = TextField::new('choix');
        $texte = AdminCKEditorField::new('texte');
        $categorie = AssociationField::new('categorie');
        $alt_image = TextField::new('alt_image');
        $descr_image = AdminCKEditorField::new('descr_image');
        $titre_objectifs = TextField::new('titre_objectifs');
        $texte_objectifs = AdminCKEditorField::new('texte_objectifs');
        $carousel = AssociationField::new('carousel')->setFormTypeOptions(['choices' => $listCarousels]);
        $createdAt = DateTimeField::new('createdAt', 'Créé  le ');
        $updatedAt = DateTimeField::new('updatedAt');
        $updatedat = DateTimeField::new('updatedat', 'Mis à jour  le ');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$titre, $choix, $texte, $categorie, $alt_image, $descr_image, $titre_objectifs, $texte_objectifs, $carousel, $createdAt, $updatedat];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$titre, $choix, $texte, $categorie, $alt_image, $descr_image, $titre_objectifs, $texte_objectifs, $carousel, $createdAt, $updatedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$titre, $choix, $texte, $categorie, $alt_image, $descr_image, $titre_objectifs, $texte_objectifs, $carousel];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$tab1, $titre, $panel1, $choix, $texte, $categorie, $panel2, $alt_image, $descr_image, $titre_objectifs, $texte_objectifs, $carousel];
        }


    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('categorie'));

    }

    public function configureActions(Actions $actions): Actions
    {
        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        return $actions;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $context = $this->adminContextProvider->getContext();
        $qb = $this->doctrine->getRepository(OdpfArticle::class)->createQueryBuilder('a')
            ->addOrderBy('a.createdAt', 'DESC');
        if (isset($_REQUEST['filters'])) {
            $categorie = $this->doctrine->getRepository(OdpfCategorie::class)->findOneBy(['id' => $_REQUEST['filters']['categorie']['value']]);
            $qb->andWhere('a.categorie =:categorie')
                ->setParameter('categorie', $categorie);
        }
        return $qb;
    }
}
