<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfVideosequipes;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OdpfVideosEquipesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OdpfVideosequipes::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [

            AssociationField::new('equipe'),
            TextField::new('lien'),
        ];
    }

}
