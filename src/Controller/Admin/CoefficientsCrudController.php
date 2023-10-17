<?php

namespace App\Controller\Admin;


use App\Entity\Coefficients;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class CoefficientsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Coefficients::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'modifier les coefficients');

    }

    public function configureFields(string $pageName): iterable
    {
        return [
            'demarche',
            'origin',
            'oral',
            'repquestions',
            'exper',
            'wgroupe',
            'ecrit'
        ];
    }
}