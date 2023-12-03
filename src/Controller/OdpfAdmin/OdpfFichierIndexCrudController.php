<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfFichierIndex;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OdpfFichierIndexCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OdpfFichierIndex::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $chargeIndex = Action::new('chargeindex')->createAsGlobalAction()->linkToRoute('charge_mots_clefs');


        return $actions->add(Crud::PAGE_INDEX, $chargeIndex);
    }
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
