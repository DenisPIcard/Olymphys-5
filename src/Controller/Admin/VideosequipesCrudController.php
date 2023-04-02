<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CustomEquipespasseesFilter;
use App\Entity\Edition;
use App\Entity\Fichiersequipes;
use App\Entity\Videosequipes;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class VideosequipesCrudController extends AbstractCrudController
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
        return Videosequipes::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Videosequipes')
            ->setEntityLabelInPlural('Videosequipes')
            ->setPageTitle(Crud::PAGE_INDEX, '<p style="color:yellow"><h2>Les vidéos des équipes</h2></p>')
            ->setPageTitle(Crud::PAGE_EDIT, 'Donner un nom à la vidéo')
            ->setSearchFields(['id', 'lien', 'nom'])
            ->setPaginatorPageSize(50);

    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomEquipespasseesFilter::new('equipe'));
    }

    public function configureFields(string $pageName): iterable
    {
        $panel1 = FormField::addPanel('<p style="color:red" > Choisir le fichier à déposer </p> ');
        $equipe = AssociationField::new('equipe');
        $lien = UrlField::new('lien')->setTemplatePath('bundles/EasyAdminBundle/list_videos.html.twig');
        $nom = TextareaField::new('nom')->setLabel('Nom du lien vidéo');
        $panel2 = FormField::addPanel('<p style="color:red" > Choisir l\'équipe </p> ');
        $id = IntegerField::new('id', 'ID');
        $updatedAt = DateTimeField::new('updatedAt');
        $edition = AssociationField::new('edition');
        $equipe = AssociationField::new('equipe')->setQueryBuilder(function ($queryBuilder) {
            return $queryBuilder->select()->addOrderBy('entity.edition', 'DESC')->addOrderBy('entity.numero', 'ASC');
        });
        $equipeLettre = TextareaField::new('equipe.lettre');
        $equipeCentreCentre = TextareaField::new('equipe.centre.centre');
        $equipeTitreprojet = TextareaField::new('equipe.titreprojet');
        $updatedat = DateTimeField::new('updatedat', 'Déposé le ');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$equipe, $equipeTitreprojet, $nom, $lien, $updatedat];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $lien, $nom, $updatedAt, $edition, $equipe];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $equipe, $lien, $nom];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$panel2, $equipe, $lien, $nom];
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $session = $this->requestStack->getSession();
        $context = $this->adminContextProvider->getContext();
        $repositoryEdition = $this->doctrine->getManager()->getRepository(Edition::class);
        if ($context->getRequest()->query->get('filters') == null) {

            $edition = $session->get('edition');
            if(new Datetime('now')<$session->get('edition')->getDateouverturesite()){
                $edition=$repositoryEdition->findOneBy(['ed'=>$edition->getEd()-1]);
            }

        } else {
            if (isset($context->getRequest()->query->get('filters')['edition'])) {
                $idEdition = $context->getRequest()->query->get('filters')['edition']['value'];
                $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
                $session->set('titreedition', $edition);
            }
        }


        $qb = $this->doctrine->getRepository(Videosequipes::class)->createQueryBuilder('v')
            ->leftJoin('v.equipe', 'eq')
            ->andWhere('eq.edition =:edition')
            ->setParameter('edition', $edition)
            ->addOrderBy('eq.numero', 'ASC')
            ->addOrderBy('eq.lettre', 'ASC');

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ...
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Déposer un nouveau lien vidéo');
            });
    }

}