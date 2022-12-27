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
        $lettre =TextField::new('equipeinter.lettre','lettre');
        $titreProjet = TextField::new('equipeinter.titreProjet','projet');
        $ordre = IntegerField::new('ordre');
        $heure = TextField::new('heure');
        $salle = TextField::new('salle');
        $total = IntegerField::new('total');
        $classement = TextField::new('classement');
        $rang = IntegerField::new('rang');
        $nbNotes = IntegerField::new('nbNotes');
        //$sallesecours = TextField::new('sallesecours');
        //$code = TextField::new('code', 'code');
        $visite = AssociationField::new('visite');
        $cadeau = AssociationField::new('cadeau');
        $phrases = AssociationField::new('phrases');
        $liaison = AssociationField::new('liaison');
        $prix = AssociationField::new('prix');
        $infoequipe = TextField::new('equipeinter.infoequipe');
        $eleves = AssociationField::new('eleves');
        $notess = AssociationField::new('notess');
        //$hote = AssociationField::new('hote');
        //$interlocuteur = TextField::new('interlocuteur');
        $observateur = TextField::new('observateur');
        $infoequipeLyceeAcademie = TextareaField::new('equipeinter.lyceeAcademie','académie');
        $infoequipeLycee = TextareaField::new('equipeinter.Lycee','lycée');
        $infoequipeTitreProjet = TextareaField::new('equipeinter.TitreProjet');
        $id = IntegerField::new('id', 'ID');
        //$hotePrenomNom = TextareaField::new('hote.PrenomNom', 'hote');
       // $interlocuteurPrenomNom = TextareaField::new('interlocuteur.PrenomNom', 'interlocuteur');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$lettre, $titreProjet,$infoequipeLyceeAcademie, $infoequipeLycee,  $heure, $salle,$ordre,$observateur];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $visite, $cadeau, $phrases, $liaison, $prix, $infoequipe, $eleves, $notess,  $observateur];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $visite, $cadeau, $phrases, $liaison, $prix, $infoequipe, $eleves, $notess,  $observateur];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $heure, $salle,$observateur];
        }
    }
}
