<?php

namespace App\Controller\Admin;

use App\Entity\Attributions;
use App\Entity\Equipes;
use App\Entity\Jures;
use App\Entity\User;
use App\Form\CustomAttributionsType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
        return $crud
            //->setSearchFields(['id', 'prenomJure', 'nomJure', 'initialesJure', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w'])
            ->setPaginatorPageSize(30);
    }

    /* public function configureFields(string $pageName): iterable
     {
         $nomJure = TextField::new('nomJure');
         $prenomJure = TextField::new('prenomJure');

         $x = IntegerField::new('x');
         $y = IntegerField::new('y');
         $id = IntegerField::new('id', 'ID');
         $initialesJure = TextField::new('initialesJure');
         $a = IntegerField::new('a');
         $b = IntegerField::new('b');
         $c = IntegerField::new('c');
         $d = IntegerField::new('d');
         $e = IntegerField::new('e');
         $f = IntegerField::new('f');
         $g = IntegerField::new('g');
         $h = IntegerField::new('h');
         $i = IntegerField::new('i');
         $j = IntegerField::new('j');
         $k = IntegerField::new('k');
         $l = IntegerField::new('l');
         $m = IntegerField::new('m');
         $n = IntegerField::new('n');
         $o = IntegerField::new('o');
         $p = IntegerField::new('p');
         $q = IntegerField::new('q');
         $r = IntegerField::new('r');
         $s = IntegerField::new('s');
         $t = IntegerField::new('t');
         $u = IntegerField::new('u');
         $v = IntegerField::new('v');
         $w = IntegerField::new('w');
         $iduser = AssociationField::new('iduser');
         $notesj = AssociationField::new('notesj');
         $nom = Field::new('nom');

         if (Crud::PAGE_INDEX === $pageName) {
             return [$nom, $initialesJure, $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p, $q, $r, $s, $t, $u, $v, $w, $x, $y];
         } elseif (Crud::PAGE_DETAIL === $pageName) {
             return [$id, $prenomJure, $nomJure, $initialesJure, $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p, $q, $r, $s, $t, $u, $v, $w, $iduser, $notesj];
         } elseif (Crud::PAGE_NEW === $pageName) {
             return [$nomJure, $prenomJure, $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p, $q, $r, $s, $t, $u, $v, $w, $x, $y];
         } elseif (Crud::PAGE_EDIT === $pageName) {
             return [$nomJure, $prenomJure, $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p, $q, $r, $s, $t, $u, $v, $w, $x, $y];
         }
     }*/
    public function configureFields(string $pageName): iterable
    {
        $role = 'ROLE_JURY';
        $listeUser = $this->doctrine->getRepository(User::class)->createQueryBuilder('j')
            ->select('j')
            ->where('j.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%')
            ->getQuery()->getResult();
        if ($pageName == 'edit') {
            $idJure = $_REQUEST['entityId'];
            $jure = $this->doctrine->getRepository(Jures::class)->find($idJure);


        }
        $equipesNat = $this->doctrine->getRepository(Equipes::class)->findAll();

        $nomJure = TextField::new('nomJure');
        $prenomJure = TextField::new('prenomJure');
        $id = IntegerField::new('id', 'ID');
        $initialesJure = TextField::new('initialesJure');
        $label = '';
        foreach ($equipesNat as $equipe) {
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
            return [$nomJure, $initialesJure, $attributions];
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
