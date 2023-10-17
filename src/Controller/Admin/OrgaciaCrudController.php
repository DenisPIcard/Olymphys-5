<?php

namespace App\Controller\Admin;



use App\Entity\Orgacia;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


class OrgaciaCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Orgacia::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $centre =AssociationField::new('centre');
        $user = AssociationField::new('user');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$user, $centre];
        }
        elseif (Crud::PAGE_NEW === $pageName) {
            return [$user, $centre];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$user, $centre];
        }
    }
}