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
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            ->setPaginatorPageSize(100);
    }

    public function configureFields(string $pageName): iterable
    {
        $listCarousels = $this->doctrine->getRepository(OdpfCarousels::class)->findAll();
        $idField = IdField::new('id')->hideOnForm();

        // Add a tab
        $tab1 = FormField::addTab('Article ');

        // You can use a Form Panel inside a Form Tab
        $panel1 = FormField::addPanel('Données');
        $panel2 = FormField::addPanel('Autre');
        $titre = TextField::new('titre')->setSortable(true);
        $categorie = AssociationField::new('categorie')->setSortable(true);
        $choix = TextField::new('choix')->setSortable(true);
        $texte = AdminCKEditorField::new('texte');
        $alt_image = TextField::new('alt_image');
        $descr_image = AdminCKEditorField::new('descr_image');
        $titre_objectifs = TextField::new('titre_objectifs');
        $texte_objectifs = AdminCKEditorField::new('texte_objectifs');
        $carousel = AssociationField::new('carousel')->setFormTypeOptions(['choices' => $listCarousels]);
        $createdAt = DateTimeField::new('createdAt', 'Créé  le ')->setSortable(true);
        $updatedAt = DateTimeField::new('updatedAt')->setSortable(true);
        //$updatedat = DateTimeField::new('updatedat', 'Mis à jour  le ')->setSortable(true);
        $publie = BooleanField::new('publie', 'publié');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$titre, $choix, $categorie, $texte, $titre_objectifs, $texte_objectifs, $carousel, $publie, $createdAt, $updatedAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$titre, $choix, $categorie, $texte, $titre_objectifs, $texte_objectifs, $carousel, $createdAt, $updatedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$titre, $choix, $categorie, $texte, $publie, $titre_objectifs, $texte_objectifs, $carousel];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$tab1, $titre, $publie, $panel1, $choix, $categorie, $texte, $panel2, $titre_objectifs, $texte_objectifs, $carousel];
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
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        //->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
        return $actions;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters) //le tri selon les éditions ne fonctionne pas bien
        ->leftJoin('entity.categorie', 'eq');
        //->resetDQLPart('orderBy');
        if (isset($_REQUEST['sort'])) {
            $sort = $_REQUEST['sort'];
            if (key($sort) == 'titre') {
                $response->addOrderBy('entity.titre', $sort['titre']);
            }
            if (key($sort) == 'choix') {
                $response->addOrderBy('entity.choix', $sort['choix']);
            }
            if (key($sort) == 'texte') {
                $response->addOrderBy('entity.texte', $sort['texte']);
            }
            if (key($sort) == 'categorie') {
                $response->addOrderBy('entity.categorie', $sort['categorie']);

                if (key($sort) == 'createdAt') {
                    $response->addOrderBy('entity.createdAt', $sort['createdAt']);
                }
                if (key($sort) == 'updatedAt') {
                    $response->addOrderBy('entity.updatedAt', $sort['updatedAt']);
                }
            }

        } else {

            $response->OrderBy('entity.updatedAt', 'DESC');

        }

        return $response;
    }
}
