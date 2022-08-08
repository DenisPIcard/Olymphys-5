<?php

namespace App\Controller\Admin;

use App\Entity\Classement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClassementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Classement::class;
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
        $montant = NumberField::new('montant');
        $nbreprix = IntegerField::new('nbreprix');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$niveau, $montant, $nbreprix];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $niveau, $montant, $nbreprix];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$niveau, $montant, $nbreprix];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$niveau, $montant, $nbreprix];
        }
    }
}
