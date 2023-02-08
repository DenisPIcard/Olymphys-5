<?php

namespace App\Controller\Admin;

use App\Entity\Visites;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
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
use Symfony\Component\HttpFoundation\RequestStack;

class VisitesCrudController extends AbstractCrudController
{

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public static function getEntityFqcn(): string
    {
        return Visites::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une visite')
            ->setSearchFields(['id', 'intitule']);
    }

    public function configureFields(string $pageName): iterable
    {
        $intitule = TextField::new('intitule');
        $attribue = BooleanField::new('attribue');
        $id = IntegerField::new('id', 'ID');
        $equipe= AssociationField::new('equipe');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$intitule, $attribue, $equipe];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $intitule, $attribue,$equipe];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$intitule, $attribue,$equipe];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$intitule, $attribue,$equipe];
        }

    }
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $qb = $this->doctrine->getRepository(Visites::class)->createQueryBuilder('v')
            ->leftJoin('v.equipe', 'eq')
            ->join('eq.equipeinter', 'ei')
            ->addOrderBy('ei.lettre', 'ASC')
            ;
        return $qb;
        }
}
