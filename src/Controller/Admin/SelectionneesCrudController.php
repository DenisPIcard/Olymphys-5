<?php

namespace App\Controller\Admin;

use App\Entity\Equipes;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SelectionneesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Equipes::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'lettre', 'titreProjet', 'ordre', 'heure', 'salle', 'total', 'classement', 'rang', 'nbNotes', 'sallesecours', 'code'])
            ->setPaginatorPageSize(25);
    }

    public function configureFields(string $pageName): iterable
    {
        $lettre = TextField::new('lettre');
        $titreProjet = TextField::new('titreProjet');
        $ordre = IntegerField::new('ordre');
        $heure = TextField::new('heure');
        $salle = TextField::new('salle');
        $total = IntegerField::new('total');
        $classement = TextField::new('classement');
        $rang = IntegerField::new('rang');
        $nbNotes = IntegerField::new('nbNotes');
        $sallesecours = TextField::new('sallesecours');
        $code = TextField::new('code', 'code');
        $visite = AssociationField::new('visite');
        $cadeau = AssociationField::new('cadeau');
        $phrases = AssociationField::new('phrases');
        $liaison = AssociationField::new('liaison');
        $prix = AssociationField::new('prix');
        $infoequipe = AssociationField::new('infoequipe');
        $eleves = AssociationField::new('eleves');
        $notess = AssociationField::new('notess');
        //$hote = AssociationField::new('hote');
        $interlocuteur = AssociationField::new('interlocuteur');
        $observateur = AssociationField::new('observateur');
        $infoequipeLyceeAcademie = TextareaField::new('infoequipe.lyceeAcademie');
        $infoequipeLycee = TextareaField::new('infoequipe.Lycee');
        $infoequipeTitreProjet = TextareaField::new('infoequipe.TitreProjet');
        $id = IntegerField::new('id', 'ID');
        $hotePrenomNom = TextareaField::new('hote.PrenomNom', 'hote');
        $interlocuteurPrenomNom = TextareaField::new('interlocuteur.PrenomNom', 'interlocuteur');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $heure, $salle, $code, $sallesecours, $hotePrenomNom, $interlocuteurPrenomNom];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $sallesecours, $code, $visite, $cadeau, $phrases, $liaison, $prix, $infoequipe, $eleves, $notess, $interlocuteur, $observateur];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $sallesecours, $code, $visite, $cadeau, $phrases, $liaison, $prix, $infoequipe, $eleves, $notess, $interlocuteur, $observateur];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $heure, $salle, $sallesecours, $code,  $interlocuteur];
        }
    }
}
