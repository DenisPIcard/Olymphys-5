<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfEquipesPassees;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
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
            ->add(EntityFilter::new('editionspassees', 'edition'))
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
        $qb = $this->doctrine->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e');
        $qb->leftJoin('e.editionspassees', 'ed')
            ->addOrderBy('ed.edition', 'DESC');
        return $qb;
    }
}
