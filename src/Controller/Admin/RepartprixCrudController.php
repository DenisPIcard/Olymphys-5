<?php

namespace App\Controller\Admin;

use App\Entity\Repartprix;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RepartprixCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Repartprix::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'modifier la répartition')
            ->setSearchFields(['id', 'niveau', 'montant', 'nbreprix']);
    }

    public function configureFields(string $pageName): iterable
    {
        $niveau = TextField::new('niveau');
        $nbreprix = IntegerField::new('nbreprix');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$niveau, $nbreprix];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $niveau, $nbreprix];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$niveau, $nbreprix];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$niveau, $nbreprix];
        }
    }
}