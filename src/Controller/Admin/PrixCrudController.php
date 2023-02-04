<?php

namespace App\Controller\Admin;

use App\Entity\Prix;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PrixCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Prix::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un prix')
            ->setSearchFields(['id', 'prix', 'niveau', 'voix', 'intervenant', 'remisPar'])
            ->setDefaultSort(['niveau' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $prix = TextField::new('prix');
        $niveau = TextField::new('niveau');
        $attribue = BooleanField::new('attribue');
        $voix = TextField::new('voix');
        $intervenant = TextField::new('intervenant');
        $remisPar = TextField::new('remisPar');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $prix, $niveau, $attribue, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $prix, $niveau, $attribue, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$prix, $niveau, $attribue, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$prix, $niveau, $attribue, $voix, $intervenant, $remisPar];
        }
    }

    public function configureActions(Actions $actions): Actions
    {

        $uploadPrix = Action::new('excel_prix', 'Charger les prix', 'fa fa-upload')
            ->linkToRoute('secretariatjury_excel_prix')
            ->createAsGlobalAction();


        return $actions->add(Crud::PAGE_INDEX, $uploadPrix);
    }
}