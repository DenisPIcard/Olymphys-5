<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class CadeauxCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cadeaux::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cadeaux')
            ->setEntityLabelInPlural('Cadeaux')
            ->setSearchFields(['id', 'contenu', 'fournisseur', 'montant', 'raccourci']);
    }

    public function configureFields(string $pageName): iterable
    {
        $contenu = TextField::new('contenu');
        $fournisseur = TextField::new('fournisseur');
        $montant = NumberField::new('montant');
        $attribue = BooleanField::new('attribue');
        $raccourci = TextField::new('raccourci');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $contenu, $fournisseur, $montant, $attribue, $raccourci];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $contenu, $fournisseur, $montant, $attribue, $raccourci];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$contenu, $fournisseur, $montant, $attribue, $raccourci];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$contenu, $fournisseur, $montant, $attribue, $raccourci];
        }
    }
}
