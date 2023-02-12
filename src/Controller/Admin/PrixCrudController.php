<?php

namespace App\Controller\Admin;

use App\Entity\Equipes;
use App\Entity\Prix;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PrixCrudController extends AbstractCrudController
{
    protected EntityManagerInterface $doctrine;

    public function __Construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine=$doctrine;
    }

    public static function getEntityFqcn(): string
    {
        return Prix::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un prix')
            ->setSearchFields(['id', 'prix', 'niveau', 'voix', 'intervenant', 'remisPar'])
            ->setDefaultSort(['niveau' => 'ASC'])
            ->setPaginatorRangeSize(26);
    }

    public function configureFields(string $pageName): iterable
    {

        if (isset($_REQUEST['entityId'])){
            $id=$_REQUEST['entityId'];
            $prixEquipe=$this->doctrine->getRepository(Prix::class)->findOneBy(['id'=>$id]);
            $equipe= $prixEquipe->getEquipe();
        }

        $equipesSansPrix=$this->doctrine->getRepository(Equipes::class)->createQueryBuilder('e')
                    ->where('e.prix=:value')
                    ->setParameter('value', 'null')
                    ->getQuery()->getResult();

        if (isset($equipe)){
            $equipesSansPrix[count($equipesSansPrix)]=$equipe;
        }
        $prix = TextField::new('prix');;
        $niveau = TextField::new('niveau');
        $attribue = BooleanField::new('attribue');
        $voix = TextField::new('voix');
        $intervenant = TextField::new('intervenant');
        $remisPar = TextField::new('remisPar');
        $id = IntegerField::new('id', 'ID');
        $equipe=AssociationField::new('equipe')->setFormType(EntityType::class)->setFormTypeOptions(
            ['class'=>Equipes::class,
              'choices' =>$equipesSansPrix,

            ]
        );

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $prix, $niveau, $attribue,$equipe, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $prix, $niveau, $attribue, $equipe, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$prix, $niveau, $attribue, $equipe, $voix, $intervenant, $remisPar];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$prix, $niveau, $attribue,$equipe, $voix, $intervenant, $remisPar];
        }
    }

    public function configureActions(Actions $actions): Actions
    {

        $uploadPrix = Action::new('excel_prix', 'Charger les prix', 'fa fa-upload')
            ->linkToRoute('secretariatjury_excel_prix')
            ->createAsGlobalAction();


        return $actions->add(Crud::PAGE_INDEX, $uploadPrix)
                        ->add(Crud::PAGE_EDIT,'index');
    }
}