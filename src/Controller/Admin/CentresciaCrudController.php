<?php

namespace App\Controller\Admin;

use App\Entity\Centrescia;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CentresciaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Centrescia::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'centre']);
    }

    public function configureFields(string $pageName): iterable
    {
        $centre = TextField::new('centre');
        $id = IntegerField::new('id', 'ID');
        $nbselectionnees = IntegerField::new('nbselectionnees');
        $orga1 = AssociationField::new('orga1');
        $orga2 = AssociationField::new('orga2');
        $jurycia = AssociationField::new('jurycia');
        $actif = BooleanField::new('actif', 'Actif');
        $verouClassement = BooleanField::new('verouClassement', 'verouClassement');
        if (Crud::PAGE_INDEX === $pageName) {
            return [$centre, $actif, $nbselectionnees, $verouClassement];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $actif, $centre, $nbselectionnees, $verouClassement];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$centre, $actif, $nbselectionnees, $verouClassement];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$centre, $actif, $nbselectionnees, $verouClassement];
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX, 'Retour à la liste')
            ->add(Crud::PAGE_NEW, Action::INDEX, 'Retour à la liste')
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }


}