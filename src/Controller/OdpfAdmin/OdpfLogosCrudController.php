<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfLogos;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;


class OdpfLogosCrudController extends AbstractCrudController
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }


    public static function getEntityFqcn(): string
    {
        return OdpfLogos::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('OdpfLogos')
            ->setEntityLabelInPlural('OdpfLogos')
            ->setPageTitle(Crud::PAGE_INDEX, '<h2>Les logos pour le site</h2>')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edite le logo')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau logo')
            ->setSearchFields(['id', 'type', 'logo', 'lien', 'alt', 'nom', 'en_service'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX);
    }

    public function configureFields(string $pageName): iterable
    {
        $type = ChoiceField::new('type')->setChoices(['jpg' => 'jpg', 'png' => 'png']);
        $nom = TextField::new('nom');
        $lien = TextField::new('lien')->setTemplatePath('bundles\EasyAdminBundle\odpf\odpf-logos-lien.html.twig');
        $imageFile = Field::new('imageFile', 'image')
            ->setFormType(VichFileType::class)
            ->setLabel('Image')
            ->onlyOnForms()
            ->setFormTypeOption('allow_delete', false);//sinon la case à cocher delete s'affiche//VichFilesField::new('fichierFile')->setBasePath($this->params->get('app.path.odpf_documents.localhost'));
        $id = IntegerField::new('id', 'ID');
        $alt = TextField::new('alt');
        $choix = ChoiceField::new('choix')->setChoices(['mecenes' => 'mecenes', 'donateurs' => 'donateurs']);
        $part = ChoiceField::new('part')->setChoices(['mecenes' => 'mecenes', 'donateurs' => 'donateurs', 'materiel' => 'materiel', 'visites' => 'visites', 'editeurs' => 'editeur']);
        $image = TextField::new('image')
            ->setTemplatePath('bundles\EasyAdminBundle\odpf\odpf-logos.html.twig')
            ->setFormTypeOption('disabled', 'disabled');
        $createdAt = DateTimeField::new('createdAt', 'Créé  le');
        $updatedAt = DateTimeField::new('updatedAt', 'Mis à jour le');
        $en_service = BooleanField::new('en_service');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$type, $nom, $lien, $image, $alt, $choix, $part, $createdAt, $updatedAt, $en_service];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $type, $image, $nom, $alt, $choix, $part, $createdAt, $updatedAt, $en_service, $lien];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$type, $nom, $lien, $choix, $part, $alt, $imageFile, $en_service];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$type, $nom, $lien, $imageFile, $alt, $choix, $part, $en_service];
        }


    }
}

