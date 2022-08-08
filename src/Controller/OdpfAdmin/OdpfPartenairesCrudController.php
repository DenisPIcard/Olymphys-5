<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfPartenaires;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OdpfPartenairesCrudController extends AbstractCrudController
{


    public static function getEntityFqcn(): string
    {
        return OdpfPartenaires::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {

        $titre = TextField::new('titre');
        $choix = TextField::new('choix');
        $mecenes = AdminCKEditorField::new('mecenes');
        $donateurs = AdminCKEditorField::new('donateurs');
        $visites = AdminCKEditorField::new('visites');
        $cadeaux = AdminCKEditorField::new('cadeaux');
        $cia = AdminCKEditorField::new('cia');
        $updatedAt = DateTimeField::new('updatedAt');
        $updatedat = DateTimeField::new('updatedat', 'Mis à jour  le ');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$titre, $choix, $mecenes, $donateurs, $visites, $cadeaux, $cia, $updatedat];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$titre, $choix, $mecenes, $donateurs, $visites, $cadeaux, $cia, $updatedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$titre, $choix, $mecenes, $donateurs, $visites, $cadeaux, $cia];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$titre, $choix, $mecenes, $donateurs, $visites, $cadeaux, $cia];
        }


    }

}

