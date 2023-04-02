<?php

namespace App\Controller\OdpfAdmin;

use App\Controller\Admin\Filter\CustomCentreFilter;
use App\Controller\Admin\Filter\CustomEditionFilter;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Controller\Admin\Filter\CustomEditionspasseesFilter;
use App\Controller\Admin\Filter\CustomEquipespasseesFilter;
use App\Entity\Centrescia;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Photos;
use App\Form\Type\Admin\CustomEquipespasseesFilterType;
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
use Imagick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
            ->add(CustomEquipespasseesFilter::new('equipepassee'))
            ->add(BooleanFilter::new('national'));
    }

    public function configureFields(string $pageName): iterable
    {


        $id = IntegerField::new('id', 'ID');
        $equipe = AssociationField::new('equipepassee');
        $editionpassee = AssociationField::new('editionspassees', 'edition')->setSortable(true);
        $photo = TextField::new('photo')
            ->setTemplatePath('bundles\EasyAdminBundle\photos.html.twig')
            ->setLabel('Photo')->setSortable(false)
            ->setFormTypeOptions(['disabled' => 'disabled']);
        $coment = TextField::new('coment', 'commentaire');
        $national = Field::new('national');
        $updatedAt = DateTimeField::new('updatedAt', 'Déposé le ');
        $equipeTitreprojet = AssociationField::new('equipepassee', 'Projet') ;
        $equipeLettre = TextField::new('equipepassee.lettre', 'Lettre équipe')->setSortable(true);
        $equipenumero = IntegerField::new('equipepassee.numero', 'N° équipe')->setSortable(true);
        $imageFile = Field::new('photoFile')
            ->setFormType(FileType::class)
            ->setLabel('Photo')
            ->onlyOnForms();

        if (Crud::PAGE_INDEX === $pageName) {

            return [$editionpassee, $equipenumero, $equipeLettre, $equipeTitreprojet, $photo, $coment, $updatedAt];


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
        $oldEntity = $this->doctrine->getRepository(Photos::class)->findOneBy(['id' => $entityInstance->getId()]);


        if ($entityInstance->getPhotoFile() !== null) //on dépose une nouvelle photo
        {
            $name = $entityInstance->getPhoto();
            $pathFile = 'odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/';
            $file = $entityInstance->getPhotoFile();
            $typeImage = $file->guessExtension();//Les .HEIC donnent jpg
            $originalFilename = $file->getClientOriginalName();
            $parsedName = explode('.', $originalFilename);
            $ext = end($parsedName);// détecte les .JPG et .HEIC
            $nameExtLess = explode('.' . $ext, $originalFilename)[0];
            if (($typeImage != 'jpg') or ($ext != 'jpg')) {// dans ce cas on change la compression du fichier en jpg.
                // création du fichier temporaire pour la transformation en jpg

                $entityInstance->setPhoto($originalFilename);//Pour que le fonction setImageTYpe ait une valeur dans le champ photo
                try {//on dépose le fichier dans le temp
                    $file->move(
                        'temp/',
                        $originalFilename
                    );
                } catch (FileException $e) {

                }
                $nameExtLess = $parsedName[0];
                $imax = count($parsedName);
                for ($i = 1; $i <= $imax - 2; $i++) {// dans le cas où le nom de  fichier comporte plusieurs points
                    $nameExtLess = $nameExtLess . '.' . $parsedName[$i];
                }
                $this->setImageType($entityInstance, $nameExtLess, 'temp/');//appelle de la fonction de transformation de la compression
                if (isset($_REQUEST['erreur'])) {

                    unlink('temp/' . $originalFilename);

                }
                if (!isset($_REQUEST['erreur'])) {
                    $file = new UploadedFile('temp/' . $nameExtLess . '.jpg', $nameExtLess . '.jpg', filesize('temp/' . $nameExtLess . '.jpg'), null, true);
                    unlink('temp/' . $originalFilename);
                    $entityInstance->setPhotoFile($file);//pour que vichUploader n'intervienne pas sinon erreur
                }

                parent::updateEntity($entityManager, $entityInstance);

            } else {
                if (file_exists($pathFile . 'thumbs/' . $name)) {//suppression de l'ancien fichier thumb
                    unlink($pathFile . 'thumbs/' . $name);

                }
                if (file_exists($pathFile . $name)) {//suppression de l'ancien fichier
                    unlink($pathFile . $name);

                }
                $entityManager->persist($entityInstance);
                $entityManager->flush();//


            }
        }

        //if (($entityInstance->getEquipepassee() != $oldEntity->getEquipepassee())or($entityInstance->getNational() != $oldEntity->getNational())) //on veut modifier l'équipe attribuée à la photo sans modifier la photo

        //{  Il faut donc modifier le nom de la  photos déposée et de sa vignette "à la main"

        $name = $entityInstance->getPhoto();
        $parseOldName = explode('-', $name);
        $endName = end($parseOldName);
        $slugger = new AsciiSlugger();
        $ed = $entityInstance->getEditionspassees()->getEdition();
        $equipepassee = $entityInstance->getEquipepassee();
        $equipe = $entityInstance->getEquipe();
        if ($equipe !== null) {

                $nlleEquipe = $this->doctrine->getRepository(Equipesadmin::class)->findOneBy(['edition' => $equipe->getEdition(), 'numero' => $equipe->getNumero()]);//il faut réattribuer la bonne équipe la photo
                $entityInstance->setEquipe($nlleEquipe);
             }
            $centre = ' ';
            $lettre_equipe = '';
            if ($equipe) {
                if ($equipe->getCentre()) {
                    $centre = $equipe->getCentre()->getCentre() . '-eq';
                } else(
                $centre = 'CIA-eq'
                );

            }
            $numero_equipe = $equipepassee->getNumero();
            $nom_equipe = $equipepassee->getTitreProjet();
            $nom_equipe = $slugger->slug($nom_equipe)->toString();
            if ($entityInstance->getNational() == FALSE) {
                $newFileName = $slugger->slug($ed . '-' . $centre .'-'.$numero_equipe . '-' . $nom_equipe . '.' . $endName);
            }
            if (($entityInstance->getNational() == TRUE) or ($entityInstance->getEquipepassee()->getNumero()>=100) ) {
                $equipepassee->getLettre() === null ? $idEquipe = $equipepassee->getNumero() : $idEquipe = $equipepassee->getLettre();

                $newFileName = $ed . '-CN-eq-' . $idEquipe . '-' . $nom_equipe . '.' . $endName;
            }
            $entityInstance->setPhoto($newFileName);
            $oldPathName = 'odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/' . $name;
            $newPathName = 'odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/' . $newFileName;
            $oldPathNameThumb = 'odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name;
            $newPathNameThumb = 'odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $newFileName;
            if (file_exists($oldPathName)) {
                rename($oldPathName, $newPathName);
            }
            if (file_exists($oldPathNameThumb)) {
                rename($oldPathNameThumb, $newPathNameThumb);
            }

        //}
        parent::updateEntity($entityManager, $entityInstance);
        //$entityInstance->createThumbs($entityInstance);
    }
    public function setImageType($image,$nameExtLess,$path)
    {
        try {
            $imageOrig = new Imagick($path . $image->getPhoto());
            $imageOrig->readImage($path . $image->getPhoto());
            $imageOrig->setImageCompression(Imagick::COMPRESSION_JPEG);
            $fileNameParts = explode('.', $image->getPhoto());
            $imageOrig->writeImage($path . $nameExtLess . '.jpg');
        }
        catch(\Exception $e){


            $_REQUEST['erreur']='yes';

        }

    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setEditionspassees($entityInstance->getEquipepassee()->getEditionspassees());
        $entityInstance->getEquipe() === null ? $entityInstance->setEdition(null) : $entityInstance->setEdition($entityInstance->getEquipe()->getEdition());

        if ($entityInstance->getEquipePassee()->getNumero()>=100){
            $entityInstance->setNational(true);
        }

        //$this->doctrine->getManager()->flush();
        parent::persistEntity($entityManager, $entityInstance);
    }

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $url = $this->adminUrlGenerator->setEntityId($context->getEntity()->getInstance()->getId())
            ->setAction(Action::DETAIL)
            ->generateUrl();

        return $this->redirect($url);
    }

    #[Route("/Admin/PhotosCrud/charge-photos",name:"charge-photos")]
    public function charger_photos(Request $request, AdminContext $context)
    {//fontion appelée à disparaître lorsque le basculement odpf vers Olymphys sera achevé
        $qb = $this->doctrine->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e')
            ->leftJoin('e.editionspassees', 'ed')
            ->addOrderBy('ed.edition', 'DESC')
            ->addOrderBy('e.numero', 'ASC');
        $qb2 = $this->doctrine->getRepository(OdpfEditionsPassees::class)->createQueryBuilder('ed')
            ->addOrderBy('ed.edition', 'DESC');

        $form = $this->createFormBuilder()
            /* ->add('edition',ChoiceType::class,[
                 'choices'=> range(1, 30),
                 'label' => 'Choisir le numéro de l\'édition'
             ])*/
            ->add('editionpassee', EntityType::class, [
                'class' => OdpfEditionsPassees::class,
                'query_builder' => $qb2
            ])
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
        $response = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters) //le tri selon les éditions ne fonctionne pas bien

            ->join('entity.equipepassee', 'eq')
            ->join('entity.editionspassees', 'ed')
            ->resetDQLPart('orderBy');

        if (isset($_REQUEST['sort'])){
            $sort=$_REQUEST['sort'];
            if (key($sort)=='equipepassee.lettre'){
                $response->addOrderBy('eq.lettre', $sort['equipepassee.lettre']);
            }
            if (key($sort)=='equipepassee.numero'){
                $response->addOrderBy('eq.numero', $sort['equipepassee.numero']);
            }

            if (key($sort)=='editionspassees'){
               $response->addOrderBy('ed.edition', $sort['editionspassees'])
                        ->addOrderBy('eq.numero', 'ASC')
                        ->addOrderBy('eq.lettre', 'ASC');
            }
        }
        else {

            $response->OrderBy('ed.edition', 'DESC')
                     ->addOrderBy('eq.numero', 'ASC');
        }

        return $response;
        //return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters); // TODO: Change the autogenerated stub
    }
}