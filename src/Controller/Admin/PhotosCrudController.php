<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\CustomPhotosEquipesFilter;
use App\Entity\Edition;
use App\Entity\Equipesadmin;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Photos;
use DateTime;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Imagick;
use PHPUnit\Exception;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

//use Symfony\Component\HttpFoundation\File\File;


class PhotosCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;
    private ManagerRegistry $doctrine;
    private AdminUrlGenerator $adminUrlGenerator;


    public function __construct(RequestStack $requestStack, AdminContextProvider $adminContextProvider, ManagerRegistry $doctrine, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->adminContextProvider = $adminContextProvider;
        $this->doctrine = $doctrine;
        $this->adminUrlGenerator = $adminUrlGenerator;

    }

    public static function getEntityFqcn(): string
    {
        return Photos::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $concours = $this->requestStack->getCurrentRequest()->query->get('concours');
        $edition= $this->requestStack->getSession()->get('edition');
        if (new DateTime('now')<$this->requestStack->getSession()->get('dateouverturesite')){
            $edition=$this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$edition->getEd()-1]);

        }



        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, '<h2 class="rougeodpf">Les photos du ' . $edition->getEd() . '<sup>e</sup> concours ' . $concours . '</h2>')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une photo du concours ' . $concours)
            ->setPageTitle(Crud::PAGE_NEW, 'Déposer une  photo du concours ' . $concours)
            ->setSearchFields(['id', 'photo', 'coment'])
            ->setPaginatorPageSize(30)
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig']);
        //->overrideTemplates(['crud/index'=>'bundles/EasyAdminBundle/custom/odpf-crawl.html.twig','crud/edit'=>'bundles/EasyAdminBundle/custom/edit.html.twig']);
        //->overrideTemplate('crud/edit', 'bundles/EasyAdminBundle/custom/edit.html.twig');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CustomPhotosEquipesFilter::new('equipe', 'Equipe'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $concours = $this->requestStack->getCurrentRequest()->query->get('concours');
        $urlIndex=$this->generateUrl('admin',['crudAction'=>'index','crudController'=>'photosCrudController','concours'=>$concours]);
        $attribEditionPassee = Action::new('attribEditionsPassees', 'Attribuer les éditions passéées', 'fa fa-file-download')
            ->linkToRoute('attribEditionsPassees')->createAsGlobalAction();
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
            ->update(Crud::PAGE_EDIT,Action::INDEX,function (Action $action){
                return $action->setLabel('Retour à la liste')->setHtmlAttributes(['concours' => $this->requestStack->getCurrentRequest()->query->get('concours')]);
            })
            ->add(Crud::PAGE_INDEX, $attribEditionPassee)
            ->setPermission($attribEditionPassee, 'ROLE_SUPER_ADMIN');

    }

    /**
     * @Route("/Admin/PhotosCrud/attribEditionsPassees",name="attribEditionsPassees")
     */
    public function attribEditionsPassees()
    {//fonction outil appelée à disparaître après la mise au point du site odpf

        $photos = $this->doctrine->getRepository(Photos::class)->findAll();
        $repositoryEditionspassees = $this->doctrine->getRepository(OdpfEditionsPassees::class);
        $repositoryEquipespassees = $this->doctrine->getRepository(OdpfEquipesPassees::class);
        foreach ($photos as $photo) {
            $edition = $photo->getEdition();
            $equipe = $photo->getEquipe();
            $editionpassee = $repositoryEditionspassees->findOneBy(['edition' => $edition->getEd()]);
            $equipepassee = $repositoryEquipespassees->findOneBy(['editionspassees' => $editionpassee, 'numero' => $equipe->getNumero()]);
            $photo->setEditionspassees($editionpassee);
            $photo->setEquipepassee($equipepassee);
            $this->doctrine->getManager()->persist($photo);
            $this->doctrine->getManager()->flush();

        }

        return $this->redirectToRoute('admin');
    }

    public function configureFields(string $pageName): iterable
    {
        $concours = $this->requestStack->getCurrentRequest()->query->get('concours');

        $repositoryEdition = $this->doctrine->getRepository(Edition::class);
        $edition=$this->requestStack->getSession()->get('edition');
        if(new DateTime('now')<$this->requestStack->getSession()->get('dateouverturesite')){
            $edition=$repositoryEdition->findOneBy(['ed'=>$edition->getEd()-1]);
        }
        //dd($_REQUEST);
        if (($concours==null) and (str_contains($_REQUEST['referrer'],'national'))){
            $concours='national';

        }

        if (($concours==null) and (str_contains($_REQUEST['referrer'],'interacademique'))){
            $concours='interacademique';

        }
        $this->requestStack->getCurrentRequest()->query->set('concours',$concours);
        /*if($_REQUEST['crudAction']=='index') {
            if ($concours == null) {
                $_REQUEST['menuIndex'] == 10 ? $concours = 'national' : $concours = 'interacadémique';
            }
        }
        if($_REQUEST['crudAction']=='edit') {
            if ($concours == null) {
              $this->doctrine->getRepository(Photos::class)->find($_REQUEST['entityId'])->getNational()==true?$concours='natiuonal': $concours = 'interacadémique' ;
            }
        }

        $context = $this->adminContextProvider->getContext();

        if($_REQUEST['crudAction']=='index') {
            //$_REQUEST['menuIndex'] == 10 ? $concours = 'national' : $concours = 'interacadémique';
            $concours = $_REQUEST['concours'];
        }
        if($_REQUEST['crudAction']=='edit') {
                    $this->doctrine->getRepository(Photos::class)->find($_REQUEST['entityId'])->getNational()==true?$concours='national': $concours = 'interacadémique' ;
        }
        */
        $concours == 'national' ? $tag = 1 : $tag = 0;

        $listeEquipes= $this->doctrine->getRepository(Equipesadmin::class)->createQueryBuilder('e')
                              ->andWhere('e.edition =:edition')
                              ->setParameter('edition', $edition)
                              ->addOrderBy('e.numero', 'ASC')
                              ->addOrderBy('e.lettre', 'ASC')
                              ->getQuery()->getResult()  ;
        $panel1 = FormField::addPanel('<p style="color:red" > Choisir le fichier à déposer pour la ' . $this->requestStack->getSession()->get('edition')->getEd() . '<sup>e</sup> édition</p> ');
        $equipe = AssociationField::new('equipe')
            ->setFormTypeOptions(['class' => Equipesadmin::class,
                                  'choices'=>$listeEquipes,

                                    ])->setSortable(true);

        $edition = AssociationField::new('edition')->setSortable(true);
        $editionpassee = AssociationField::new('editionspassees', 'Edition')->setSortable(true);
        $id = IntegerField::new('id', 'ID');
        $photo = TextField::new('photo')
            ->setTemplatePath('bundles\EasyAdminBundle\photos.html.twig')
            ->setLabel('Photo')
            ->setFormTypeOptions(['disabled' => 'disabled']);
        //

        $coment = TextField::new('coment', 'Commentaire');
        $concours == 'national' ? $valnat = true : $valnat = false;
        $national = Field::new('national')->setFormTypeOption('data', $valnat);

        $updatedAt = DateTimeField::new('updatedAt', 'Déposé le ')->setSortable(true);


        $equipeCentreCentre = TextField::new('equipe.centre', 'Centre académique')->setSortable(true);
        $equipeNumero = IntegerField::new('equipe.numero', 'N° équipe')->setSortable(true);
        $equipeTitreprojet = TextareaField::new('equipe.titreprojet', 'Projet')->setSortable(true);
        $equipeLettre = TextField::new('equipe.lettre', 'Lettre')->setSortable(true);
        $imageFile = Field::new('photoFile')
            ->setFormType(FileType::class)
            ->setLabel('Photo')
            ->onlyOnForms()/*->setFormTypeOption('constraints', [
                            'mimeTypes' => ['image/jpeg','image/jpg'],
                            'mimeTypesMessage' => 'Déposer un  document jpg',
                            'data_class'=>'photos'
                    ]
                )*/
        ;
        /*$imagesMultiples=CollectionField::new('photoFile')
            ->setLabel('Photo(s)')

            ->onlyOnForms()
            ->setFormTypeOptions(['by_reference'=>false])
            ;*/

        if (Crud::PAGE_INDEX === $pageName) {
            if ($concours == 'interacademique') {
                return [$editionpassee, $equipeCentreCentre, $equipeNumero, $equipeTitreprojet, $photo, $coment, $updatedAt];
            }
            if ($concours == 'national') {
                return [$editionpassee, $equipeLettre, $equipeTitreprojet, $photo, $coment, $updatedAt];
            }

        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $photo, $coment, $national, $updatedAt, $equipe, $edition];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$panel1, $equipe, $imageFile, $coment, $national, $coment,$edition];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            $this->requestStack->getCurrentRequest()->query->set('concours', $concours);
            return [$photo, $imageFile, $equipe, $national, $coment];
        }
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder

    {
        $concours = $this->requestStack->getCurrentRequest()->query->get('concours');

        if (null == $concours) {
            $concours = 'interacademique';
            if (str_contains($_REQUEST['referrer'],'national')){
                $concours='national';
            }

        }
        $edition= $this->requestStack->getSession()->get('edition');
        if (new DateTime('now')<$this->requestStack->getSession()->get('dateouverturesite')){
            $edition=$this->doctrine->getRepository(Edition::class)->findOneBy(['ed'=>$edition->getEd()-1]);

        }

        $qb = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.edition =:edition')
            ->setParameter('edition', $edition);

        if ($concours == 'interacademique') {

            $qb->andWhere('entity.national =:concours')
                ->setParameter('concours', 0);

        }
        if ($concours == 'national') {
            $qb->andWhere('entity.national =:concours')
                ->setParameter('concours', 1);
        }

        $qb->leftJoin('entity.equipe', 'e');

        if (isset($_REQUEST['sort'])){
            $sort=$_REQUEST['sort'];
            if (key($sort)=='equipe.lettre'){
                $qb->addOrderBy('e.lettre', $sort['equipe.lettre']);
            }
            if (key($sort)=='equipe.numero'){
                $qb->addOrderBy('e.numero', $sort['equipe.numero']);
            }
            if (key($sort)=='equipe.centre.centre'){
                $qb->addOrderBy('e.centre.centre', $sort['equipe.centre.centre']);
            }

        }
        else{
            if ($concours == 'interacadémique') {
                $qb->addOrderBy('e.numero', 'ASC');
            }
            if ($concours == 'national') {
                $qb->addOrderBy('e.lettre', 'ASC');
            }
        }
        return $qb;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance):void
    {

        $edition = $entityInstance->getEquipe()->getEdition();

        $editionpassee = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $edition->getEd()]);
        $equipepassee = $this->doctrine->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('p')
            ->andWhere('p.numero =:numero')
            ->leftJoin('p.editionspassees', 'ed')
            ->andWhere('ed.edition =:edition')
            ->setParameters(['numero' => $entityInstance->getEquipe()->getNumero(), 'edition' => $edition->getEd()])
            ->getQuery()->getSingleResult();

        $entityInstance->setEdition($edition);
        $entityInstance->setEditionspassees($editionpassee);
        $entityInstance->setEquipepassee($equipepassee);

        $file = $entityInstance->getPhotoFile();
        $typeImage= $file->guessExtension();//Les .HEIC donnent jpg
        $originalFilename=$file->getClientOriginalName();
        $parsedName = explode('.', $originalFilename);
        $ext = end($parsedName);// détecte les .JPG et .HEIC
        $nameExtLess=explode('.'.$ext, $originalFilename)[0];
        if (($typeImage!='jpg') or ($ext != 'jpg')) {// dans ce cas on change la compression du fichier en jpg.
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
            $imax=count($parsedName );
            for ($i=1;$i<=$imax-2;$i++) {// dans le cas où le nom de  fichier comporte plusieurs points
                $nameExtLess =$nameExtLess.'.'.$parsedName[$i];
            }
            $this->setImageType($entityInstance, $nameExtLess,'temp/');//appelle de la fonction de transformation de la compression
            if (isset($_REQUEST['erreur'])){

                unlink('temp/'. $originalFilename);

            }
            if (!isset($_REQUEST['erreur'])){
                $file=new UploadedFile('temp/'.$nameExtLess.'.jpg',$nameExtLess.'.jpg', filesize('temp/'.$nameExtLess.'.jpg'), null, true);
                unlink('temp/'. $originalFilename);
                $entityInstance->setPhotoFile($file);//pour que vichUploader n'intervienne pas sinon erreur
            }
        }
        if (!isset($_REQUEST['erreur'])) {

            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }

    }




    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        if ($entityInstance->getPhotoFile() !== null) //on dépose une nouvelle photo
        {
            $name = $entityInstance->getPhoto();
            $pathFile = 'odpf/odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/';
            $file = $entityInstance->getPhotoFile();
            $typeImage= $file->guessExtension();//Les .HEIC donnent jpg
            $originalFilename=$file->getClientOriginalName();
            $parsedName = explode('.', $originalFilename);
            $ext = end($parsedName);// détecte les .JPG et .HEIC
            $nameExtLess=explode('.'.$ext, $originalFilename)[0];
            if (($typeImage!='jpg') or ($ext != 'jpg')) {// dans ce cas on change la compression du fichier en jpg.
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
                $imax=count($parsedName );
                for ($i=1;$i<=$imax-2;$i++) {// dans le cas où le nom de  fichier comporte plusieurs points
                    $nameExtLess =$nameExtLess.'.'.$parsedName[$i];
                }
                $this->setImageType($entityInstance, $nameExtLess,'temp/');//appelle de la fonction de transformation de la compression
                if (isset($_REQUEST['erreur'])){

                    unlink('temp/'. $originalFilename);

                }
                if (!isset($_REQUEST['erreur'])){
                    $file=new UploadedFile('temp/'.$nameExtLess.'.jpg',$nameExtLess.'.jpg', filesize('temp/'.$nameExtLess.'.jpg'), null, true);
                    unlink('temp/'. $originalFilename);
                    $entityInstance->setPhotoFile($file);//pour que vichUploader n'intervienne pas sinon erreur
                }

                parent::updateEntity($entityManager, $entityInstance);

            }
            else{
                if (file_exists($pathFile.'thumbs/' . $name)) {//suppression de l'ancien fichier thumb
                    unlink($pathFile.'thumbs/' . $name);

                }
                if (file_exists($pathFile. $name)) {//suppression de l'ancien fichier
                    unlink($pathFile. $name);

                }
                parent::updateEntity($entityManager, $entityInstance);

            }
        }



        if ($entityInstance->getPhotoFile() === null) //on veut modifier l'équipe attribuée à la photo sans modifier la photo

       {  //Il faut donc modifier le nom de la  photos déposée et de sa vignette "à la main"
            $equipe = $entityInstance->getEquipe();
            $name = $entityInstance->getPhoto();
            $parseOldName=explode('-',$name);
            $endName=end($parseOldName);
            $slugger = new AsciiSlugger();
            $ed = $entityInstance->getEditionspassees()->getEdition();
            $equipepassee = $entityInstance->getEquipepassee();
            $equipe = $entityInstance->getEquipe();
            $nlleEquipepassee=$this->doctrine->getRepository(OdpfEquipesPassees::class)->findOneBy(['editionspassees'=>$equipepassee->getEditionspassees(),'numero'=>$equipe->getNumero()]);//il faut réattribué la bonne équipepassee à la photo
            $entityInstance->setEquipepassee($nlleEquipepassee);
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
            if ($entityInstance->getNational() == TRUE) {
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
            $entityManager->persist($entityInstance);
            $entityManager->flush();//

        }

        //$entityInstance->createThumbs($entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $name = $entityInstance->getPhoto();
        if(file_exists('odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name)) {
            unlink('odpf-archives/' . $entityInstance->getEditionsPassees()->getEdition() . '/photoseq/thumbs/' . $name);
        }
        parent::deleteEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
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
    public function NamePhotos($photo)  : string  // renomme le fichier dans le cas d'un persist
    {
        $slugger = new AsciiSlugger();
        $ed = $photo->getEditionspassees()->getEdition();
        $equipepassee = $photo->getEquipepassee();
        $equipe = $photo->getEquipe();
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

        if ($photo->getNational() == FALSE) {
            $fileName = $slugger->slug($ed . '-' . $centre . $numero_equipe . '-' . $nom_equipe . '.' . uniqid())->toString();
        }
        if ($photo->getNational() == TRUE) {
            $equipepassee->getLettre() === null ? $idEquipe = $equipepassee->getNumero() : $idEquipe = $equipepassee->getLettre();

            $fileName = $ed . '-CN-eq-' . $idEquipe . '-' . $nom_equipe . '.' . uniqid();
        }


        return $fileName;
    }


}
