<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CustomEditionFilter;
use App\Controller\Admin\Filter\CustomEquipeFilter;
use App\Controller\Admin\Filter\CustomRolesFilter;
use App\Entity\User;
use App\Form\Type\Admin\CustomRolesFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PhpOffice\PhpSpreadsheet\Shared\PasswordHasher;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserCrudController extends AbstractCrudController
{
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordEncoder;

    public function __construct(AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /* public function configureFilters(Filters $filters): Filters
     {
         return $filters
             ->add(CustomRolesFilter::new('roles'));

     }*/

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'username', 'roles', 'email', 'token', 'uai', 'nom', 'prenom', 'adresse', 'ville', 'code', 'phone', 'civilite']);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IntegerField::new('id', 'ID');
        $email = TextField::new('email');
        $username = TextField::new('username');
        $nomPrenom = TextareaField::new('nomPrenom');
        $roles = ArrayField::new('roles');
        $rolesedit = ChoiceField::new('roles')->setChoices(['ROLES_ADMIN' => 'ROLES_ADMIN',
            'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
            'ROLE_ADMIN' => 'ROLE_ADMIN',
            'ROLE_PROF' => 'ROLE_PROF',
            'ROLE_JURY' => 'ROLE_JURY',
            'ROLE_JURYCIA' => 'ROLE_JURYCIA',
            'ROLE_ORGACIA' => 'ROLE_ORGACIA',
            'ROLE_COMITE' => 'ROLE_COMITE'])
            ->setFormTypeOption('multiple', true);
        $password = Field::new('password')->setFormType(PasswordType::class)->onlyOnForms();
        if ($pageName == 'edit') {
            $iD = $_REQUEST['entityId'];
            $user = $this->doctrine->getRepository(User::class)->findOneBy(['id' => $iD]);
            $password->setFormTypeOptions(['required' => false, 'mapped' => true, 'empty_data' => $user->getPassword()]);
        }

        $nom = TextField::new('nom');
        $prenom = TextField::new('prenom');
        $uai = TextField::new('uai');
        $uaiId = AssociationField::new('uaiId', 'UAI')->setFormTypeOptions(['required' => false]);
        $centreCia = AssociationField::new('centrecia');
        $isActive = Field::new('isActive');
        $adresse = TextField::new('adresse');
        $ville = TextField::new('ville');
        $code = TextField::new('code');
        $phone = TextField::new('phone');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $lastVisit = DateTimeField::new('lastVisit');
        //$civilite = TextField::new('civilite');
        $autorisationphotos = AssociationField::new('autorisationphotos');
        $token = TextField::new('token');
        $passwordRequestedAt = DateTimeField::new('passwordRequestedAt');
        //$centreciaCentre = TextField::new('centrecia.centre', 'Centre CIA');
        /*
        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $email, $username, $nomPrenom, $roles, $isActive,];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $username, $roles, $password, $email, $isActive, $token, $passwordRequestedAt, $uaiId, $nom, $prenom, $adresse, $centreCia, $ville, $code, $phone, $createdAt, $updatedAt, $lastVisit, $civilite, $autorisationphotos];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$username, $email, $rolesedit, $password, $isActive, $nom, $prenom, $uaiId, $centreCia, $adresse, $ville, $code, $phone];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$username, $email, $password, $rolesedit, $isActive, $nom, $prenom, $uaiId, $centreCia, $adresse, $ville, $code, $phone];
        }*/
        return [
            IntegerField::new('id')->setFormTypeOption('disabled', true),
            TextField::new('prenom'),
            TextField::new('nom'),
            TextField::new('email'),
            TextField::new('username'),
            ArrayField::new('roles')->onlyOnIndex(),
            ChoiceField::new('roles')->setChoices(['ROLES_ADMIN' => 'ROLES_ADMIN',
                'ROLE_SUPER_ADMIN' => 'ROLE_SUPER_ADMIN',
                'ROLE_ADMIN' => 'ROLE_ADMIN',
                'ROLE_PROF' => 'ROLE_PROF',
                'ROLE_JURY' => 'ROLE_JURY',
                'ROLE_JURYCIA' => 'ROLE_JURYCIA',
                'ROLE_ORGACIA' => 'ROLE_ORGACIA',
                'ROLE_COMITE' => 'ROLE_COMITE'])
                ->setFormTypeOption('multiple', true)->onlyOnForms(),
            TextField::new('uai')->onlyOnIndex(),
            TextField::new('plainPassword', 'Mot de passe')->onlyOnForms(),
            AssociationField::new('uaiId', 'UAI')->setFormTypeOptions(['required' => false])->onlyOnForms(),
            AssociationField::new('centrecia'),
            $isActive = Field::new('isActive'),
            $adresse = TextField::new('adresse'),
            $ville = TextField::new('ville'),
            $code = TextField::new('code'),
            $phone = TextField::new('phone'),
            $createdAt = DateTimeField::new('createdAt'),
            $updatedAt = DateTimeField::new('updatedAt'),
            $lastVisit = DateTimeField::new('lastVisit'),
            $civilite = TextField::new('civilite'),
            //$autorisationphotos = AssociationField::new('autorisationphotos'),
            $token = TextField::new('token'),
            $passwordRequestedAt = DateTimeField::new('passwordRequestedAt'),

        ];


    }

    public function configureActions(Actions $actions): Actions
    {
        $addUsers = Action::new('addUsers', 'Ajouter des users', 'fa fa_users',)
            // if the route needs parameters, you can define them:
            // 1) using an array
            ->linkToRoute('secretariatadmin_charge_user')
            ->createAsGlobalAction();


        $actions = $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX, 'Retour à la liste')
            ->add(Crud::PAGE_NEW, Action::INDEX, 'Retour à la liste')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $addUsers);
        return $actions;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->getUaiId() !== null) {
            $uai = $entityInstance->getUaiId()->getUai();

            $entityInstance->setUai($uai);
        }

        if ($entityInstance->getPlainPassword() != null) {
            $hashpassword = $this->passwordEncoder->hashPassword($entityInstance, $entityInstance->getPlainPassword());
            $entityInstance->setPassword($hashpassword);

        }

        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->getUaiId() !== null) {
            $uai = $entityInstance->getUaiId()->getUai();

            $entityInstance->setUai($uai);
        }
        $hashpassword = $this->passwordEncoder->hashPassword($entityInstance, $entityInstance->getPlainPassword());
        $entityInstance->setPassword($hashpassword);

        parent::persistEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }


}
