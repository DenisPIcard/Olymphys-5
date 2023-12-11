<?php

namespace App\Controller\Admin;

use App\Entity\Attributions;
use App\Entity\Equipes;
use App\Entity\Jures;
use App\Entity\User;
use App\Form\CustomAttributionsType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Symfony\Component\HttpFoundation\RequestStack;

class JuresCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;

    }


    public static function getEntityFqcn(): string
    {
        return Jures::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $equipes = $this->doctrine->getRepository(Equipes::class)->findAll();
        $lettres = [];
        foreach ($equipes as $equipe) {
            $lettres[$equipe->getEquipeinter()->getLettre()] = $equipe->getEquipeinter()->getLettre();
        }
        $this->requestStack->getSession()->set('lettres', $lettres);//variable globale utilisée par l'index_jures.html.twig pour les entêtes des colonnes
        return $crud
            ->setPaginatorPageSize(30)
            ->overrideTemplates(['crud/index' => 'bundles/EasyAdminBundle/index_jures.html.twig']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $gestionjures = Action::new('gestionjures')->createAsGlobalAction()->linkToRoute('secretariatjury_gestionjures');
        return $actions->add(Crud::PAGE_INDEX, $gestionjures);
    }

    public function configureFields(string $pageName): iterable
    {
        $role = 'ROLE_JURY';
        $listeJures = $this->doctrine->getRepository(Jures::class)->findAll();
        $userJures = [];
        $i = 0;
        foreach ($listeJures as $jure) {
            $userJures[$i] = $jure->getIduser()->getId();
            $i += 1;
        }

        $qb = $this->doctrine->getRepository(User::class)->createQueryBuilder('j');
        $listeUser = $qb->select('j')//liste id des user qui ne sont pas déjà jurés(pour la création d'un nouveau juré)
        ->where('j.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%')
            ->andWhere('j.id NOT IN ( :user)')
            ->setParameter('user', $userJures)
            ->getQuery()->getResult();
        if ($pageName == 'edit') {
            $idJure = $_REQUEST['entityId'];
            $jure = $this->doctrine->getRepository(Jures::class)->find($idJure);


        }
        $equipesNat = $this->doctrine->getRepository(Equipes::class)->findAll();//toutes les équipes du CN

        $nomJure = TextField::new('nomJure');
        $prenomJure = TextField::new('prenomJure');
        $id = IntegerField::new('id', 'ID');
        $initialesJure = TextField::new('initialesJure');
        $label = '';
        foreach ($equipesNat as $equipe) {//Création des entête des équipes du tableau index
            $lettre = $equipe->getEquipeinter()->getlettre();
            $label = $label . '<b>' . str_replace(' ', '&nbsp;', str_pad($lettre, 9, ' ', STR_PAD_RIGHT)) . '</b>';
        }
        $attributions = CollectionField::new('attributions')->setLabel($label)->showEntryLabel()->formatValue(function ($value, $entity) {
            $repoJures = $this->doctrine->getRepository(Jures::class);
            $attribs = $repoJures->getAttributionAdmin($entity);
            $equipesNat = $this->doctrine->getRepository(Equipes::class)->findAll();
            $attribution = '';
            foreach ($equipesNat as $equipe) {
                $lettre = $equipe->getEquipeinter()->getlettre();
                /* if (!isset($attribs[$lettre])) {
                     $attribs[$lettre] = str_replace(' ', '&nbsp;', str_pad('_', 1, ' ', STR_PAD_RIGHT));
                 }*/
                if ($attribs == []) {
                    $attribution = $attribution . str_replace(' ', '&nbsp;', str_pad('_', 10, ' ', STR_PAD_RIGHT));
                } else {
                    $attribution = $attribution . str_replace(' ', '&nbsp;', str_pad($attribs[$lettre], 10, ' ', STR_PAD_RIGHT));
                }

            }


            return $attribution;
        })->onlyOnIndex();
        $lesAttributions = CollectionField::new('attributions');
        //$lesAttributions = CollectionField::new('attributions');
        if (($pageName == 'edit') or ($pageName == 'new')) {
            $lesAttributions = CollectionField::new('attributions')->setEntryType(CustomAttributionsType::class)
                ->setFormTypeOption('entry_options', ['page' => $pageName])
                ->setEntryIsComplex(true)
                ->renderExpanded(true);
        }
        $iduser = AssociationField::new('iduser', 'user')->setFormTypeOption('choices', $listeUser);
        $notesj = AssociationField::new('notesj');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$nomJure, $initialesJure, $lesAttributions];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $prenomJure, $nomJure, $initialesJure, $attributions, $iduser, $notesj];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$iduser, $nomJure, $prenomJure, $initialesJure];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$nomJure, $prenomJure, $lesAttributions];
        }

    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        $attributions = $entityInstance->getAttributions();
        if ($attributions !== null) {
            foreach ($attributions as $attribution) {
                $this->doctrine->getManager()->persist($attribution);

            }


        }


        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub

    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        // les équipes non attribuées explicitement dans le formulaire doivent être attribué à null dans l'affectation au juré

        $listeEquipes = $this->doctrine->getRepository(Equipes::class)->findAll();

        foreach ($listeEquipes as $equipe) {
            $attribution = new Attributions();
            $attribution->setEquipe($equipe);
            $attribution->setJure($entityInstance);
            $attribution->setEstLecteur(null);
            $entityInstance->addAttribution($attribution);
            $this->doctrine->getManager()->persist($attribution);

            //$this->doctrine->getManager()->flush();

        }
        parent::persistEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

}
