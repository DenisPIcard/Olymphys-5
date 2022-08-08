<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfCategorie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OdpfCategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OdpfCategorie::class;
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
