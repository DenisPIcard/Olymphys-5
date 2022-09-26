<?php

namespace App\Controller\OdpfAdmin;

use App\Controller\Admin\Filter\CustomCentreFilter;
use App\Controller\Admin\Filter\CustomEditionFilter;
use App\Controller\Admin\Filter\CustomEditionspasseesFilter;
use App\Entity\Centrescia;
use App\Entity\Edition;
use App\Entity\Fichiersequipes;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Photos;
use App\Service\ImagesCreateThumbs;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Form;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

//use Symfony\Component\HttpFoundation\File\File;


class OdpfPhotosCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;
    private AdminUrlGenerator $adminUrlGenerator;


    public function __construct(RequestStack $requestStack, AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->requestStack = $requestStack;;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;

    }

    public static function getEntityFqcn(): string
    {
        return Photos::class;
    }


    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomEditionspasseesFilter::new('editionspassees'))
            ->add(EntityFilter::new('equipepassee'))
            ->add(BooleanFilter::new('national'));
    }

    public function configureFields(string $pageName): iterable
    {


        $id = IntegerField::new('id', 'ID');
        $equipe = AssociationField::new('equipepassee');
        $editionpassee = AssociationField::new('editionspassees', 'edition');
        $photo = TextField::new('photo')
            ->setTemplatePath('bundles\EasyAdminBundle\photos.html.twig')
            ->setLabel('Nom de la photo')
            ->setFormTypeOptions(['disabled' => 'disabled']);
        $coment = TextField::new('coment', 'commentaire');
        $national = Field::new('national');
        $updatedAt = DateTimeField::new('updatedAt', 'Déposé le ');
        $equipeTitreprojet = TextareaField::new('equipepassee.titreprojet', 'Projet');
        $equipeLettre = TextField::new('equipepassee.lettre', 'Lettre');
        $equipenumero = IntegerField::new('equipepassee.numero', 'N°');
        $imageFile = Field::new('photoFile')
            ->setFormType(FileType::class)
            ->setLabel('Photo')
            ->onlyOnForms();

        if (Crud::PAGE_INDEX === $pageName) {

            return [$id, $editionpassee, $equipenumero, $equipeLettre, $equipeTitreprojet, $photo, $coment, $updatedAt];


        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $photo, $coment, $national, $updatedAt, $equipe, $editionpassee];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$equipe, $imageFile, $coment, $national];
        } elseif (Crud::PAGE_EDIT === $pageName) {

            return [$photo, $imageFile, $equipe, $national, $coment];
        }
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $name = $entityInstance->getPhoto();
        if (file_exists('odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name)) {
            unlink('odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name);
        }
        parent::deleteEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setEditionspassees($entityInstance->getEquipepassee()->getEditionspassees());
        $name = $entityInstance->getPhoto();
        if (file_exists('odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name)) {
            unlink('odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name);
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setEditionspassees($entityInstance->getEquipepassee()->getEditionspassees());
        $entityInstance->getEquipe() === null ? $entityInstance->setEdition(null) : $entityInstance->setEdition($entityInstance->getEquipe()->getEdition());
        //$this->doctrine->getManager()->persist($entityInstance);
        //$this->doctrine->getManager()->flush();
        parent::persistEntity($entityManager, $entityInstance);
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $url = $this->adminUrlGenerator
            ->setAction(Action::DETAIL)
            ->generateUrl();

        return $this->redirect($url);
    }

    /**
     * @Route("/Admin/PhotosCrud/charge-photos",name="charge-photos")
     */
    public function charger_photos(Request $request, AdminContext $context)
    {//fontion appelée à disparaître lorsque le basculement odpf vers Olymphys sera achevé
        $qb = $this->doctrine->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e')
            ->leftJoin('e.editionspassees', 'ed')
            ->addOrderBy('ed.edition', 'DESC')
            ->addOrderBy('e.numero', 'ASC');

        $form = $this->createFormBuilder()
            /* ->add('edition',ChoiceType::class,[
                 'choices'=> range(1, 30),
                 'label' => 'Choisir le numéro de l\'édition'
             ])*/

            ->add('equipepassee', EntityType::class, [
                'class' => OdpfEquipesPassees::class,
                'query_builder' => $qb
            ])
            ->add('fichiers', FileType::class, [
                'multiple' => true,


            ])
            ->add('national', CheckboxType::class, [
                'label' => 'interacadémique',
                'required' => false
            ])
            ->add('Valider', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $files = $form->get('fichiers')->getData();
            $equipe = $form->get('equipepassee')->getData();
            $national = !$form->get('national')->getData();

            //$files=$form->get('serveur')->getData();

            if ($files !== null) {
                foreach ($files as $photoFile) {
                    $photo = new Photos();
                    $photo->setEquipepassee($equipe);
                    $photo->setEditionspassees($equipe->getEditionspassees());
                    $photo->setNational($national);
                    $photo->setPhotoFile($photoFile);
                    $this->doctrine->getManager()->persist($photo);
                    $this->doctrine->getManager()->flush();
                }
            }
            $url = $this->adminUrlGenerator
                ->setController(OdpfPhotosCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            return $this->redirect($url);
        }
        return $this->renderForm('OdpfAdmin/charger-photos.html.twig', array('form' => $form));


    }

    public function configureActions(Actions $actions): Actions
    {
        $attribEditionPassee = Action::new('charger-photos-passees', 'Attribuer les photos passees', 'fa fa-file-download')
            ->linkToRoute('charge-photos')->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Déposer')->setHtmlAttributes(['concours' => $this->requestStack->getCurrentRequest()->query->get('concours')]);
            })
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Déposer une photo')->setHtmlAttributes(['concours' => $this->requestStack->getCurrentRequest()->query->get('concours')]);
            })
            ->add(Crud::PAGE_INDEX, $attribEditionPassee)
            ->setPermission($attribEditionPassee, 'ROLE_SUPER_ADMIN');;

    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $response = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->leftJoin('entity.editionspassees', 'ed')
            ->addOrderBy('ed.edition', 'DESC')
            ->leftJoin('entity.equipepassee', 'eq')
            ->addOrderBy('eq.numero', 'ASC');

        return $response;
        //return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters); // TODO: Change the autogenerated stub
    }
}