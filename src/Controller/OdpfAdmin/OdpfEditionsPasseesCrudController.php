<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfEditionsPassees;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use Imagick;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;


class OdpfEditionsPasseesCrudController extends AbstractCrudController
{
    private ManagerRegistry $doctrine;
    private RequestStack $requestStack;
    private AdminContextProvider $adminContextProvider;

    function __construct(ManagerRegistry $doctrine, RequestStack $requestStack, AdminContextProvider $adminContext)
    {
        $this->doctrine = $doctrine;
        $this->requestStack = $requestStack;
        $this->adminContextProvider = $adminContext;
    }

    public static function getEntityFqcn(): string
    {
        return OdpfEditionsPassees::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['edition' => 'DESC']);

        // ->overrideTemplate('crud/field/photoParrain', 'bundles/EasyAdminBundle/odpf/odpf-photoParrain.html.twig');

    }

    public function configureFields(string $pageName): iterable
    {

        $photoParrain = TextField::new('photoParrain');
        $affiche = TextField::new('affiche');//->setTemplatePath( 'bundles/EasyAdminBundle/odpf/odpf-affiche.html.twig');;

        if (Crud::PAGE_EDIT === $pageName) {
            $idEdition = $_REQUEST['entityId'];
            $editionpassee = $this->doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['id' => $idEdition]);
            $photoParrain = ImageField::new('photoParrain')->setUploadDir('public/odpf-archives/' . $editionpassee->getEdition() . '/parrain');
            $photoParrain = ImageField::new('photoParrain')->setUploadDir('public/odpf-archives/' . $editionpassee->getEdition() . '/affiche');
            $photoFile = Field::new('photoParrain', 'Photo du parrain')
                ->setFormType(FileUploadType::class)
                ->setLabel('Photo du parrain')
                ->onlyOnForms()
                ->setFormTypeOptions(['data_class' => null, 'upload_dir' => $this->getParameter('app.path.odpf_archives') . '/' . $editionpassee->getEdition() . '/parrain']);

            $afficheFile = Field::new('affiche', 'Affiche')
                ->setFormType(FileUploadType::class)
                ->setLabel('Affiche')
                ->onlyOnForms()
                ->setCustomOptions(['type' => 'affiche'])
                ->setFormTypeOptions(['data_class' => null, 'upload_dir' => $this->getParameter('app.path.odpf_archives') . '/' . $editionpassee->getEdition() . '/affiche']);

        }

        $id = IntegerField::new('id');
        $edition = IntegerField::new('edition');

        $pseudo = TextField::new('pseudo');
        $lieu = TextField::new('lieu');
        $annee = TextField::new('annee');
        $ville = TextField::new('ville');
        $datecia = TextField::new('dateCia');
        $datecn = TextField::new('dateCn');
        $dateinscription = TextField::new('dateinscription');
        $nomParrain = TextField::new('nomParrain');
        $titreParrain = TextField::new('titreParrain');
        $lienParrain = TextField::new('lienparrain');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$edition, $pseudo, $annee, $lieu, $ville, $datecia, $datecn];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $edition, $pseudo, $annee, $lieu, $ville, $datecia, $datecn, $dateinscription, $nomParrain, $titreParrain, $photoParrain, $lienParrain, $affiche];
        }
        /*if (Crud::PAGE_NEW === $pageName) {
            return [$edition, $pseudo, $annee, $lieu, $ville, $datecia, $datecn, $dateinscription, $nomParrain, $titreParrain, $photoFiLe, $afficheFile];

        }*/
        if (Crud::PAGE_EDIT === $pageName) {
            return [$edition, $pseudo, $annee, $lieu, $ville, $datecia, $datecn, $dateinscription, $nomParrain, $titreParrain, $lienParrain, $photoFile, $afficheFile];

        }

        return parent::configureFields($pageName); // TODO: Change the autogenerated stub
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_EDIT, Action::INDEX, 'Retour à la liste')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $parraintag = $this->adminContextProvider->getContext()->getRequest()->files->get('OdpfEditionsPassees')['photoParrain']['file'];
        $affichetag = $this->adminContextProvider->getContext()->getRequest()->files->get('OdpfEditionsPassees')['affiche']['file'];//dépôt de l'affiche et création du  fichiers  afficheEd_HR
        if ($affichetag !== null) {

            $pathaffiche = $this->getParameter('app.path.odpf_archives') . '/' . $entityInstance->getEdition() . '/affiche/';
            $affiche = $entityInstance->getAffiche();
            $afficheUploader = new FileUploader('affiche', $pathaffiche, $entityInstance->getEdition());
            $fileimage = new UploadedFile($pathaffiche . $affiche, $affiche, null, null, true);
            $ext = $fileimage->guessExtension();
            $afficheUploader->upload($fileimage);
            $afficheBR = new Imagick();
            $afficheBR->readImage($pathaffiche . 'affiche' . $entityInstance->getEdition() . '-HR.' . $ext);
            $width = $afficheBR->getImageWidth();
            $height = $afficheBR->getImageHeight();
            $afficheBR->thumbnailImage(230, 230 * $height / $width);
            $afficheBR->writeImage($pathaffiche . 'affiche' . $entityInstance->getEdition() . '.' . $ext);
            $entityInstance->setAffiche('affiche' . $entityInstance->getEdition() . '.' . $ext);
        }
        //dépôt de la photo du parrain
        if ($parraintag !== null) {
            $pathParrain = $this->getParameter('app.path.odpf_archives') . '/' . $entityInstance->getEdition() . '/parrain/';
            $entityInstance->getNomparrain() !== null ? $nomParrain = $entityInstance->getNomparrain() : $nomParrain = 'nomparrain';
            $photoParrain = $entityInstance->getPhotoParrain();
            $parrainUploader = new FileUploader('parrain', $pathParrain, $entityInstance->getEdition());
            $fileParrain = new UploadedFile($pathParrain . $photoParrain, $photoParrain, null, null, true);
            $ext = $fileParrain->guessExtension();
            $parrainUploader->upload($fileParrain);

            $photoParraintmp = new Imagick();
            $photoParraintmp->readImage($pathParrain . 'parrain' . $entityInstance->getEdition() . '.' . $ext);
            $width = $photoParraintmp->getImageWidth();
            $height = $photoParraintmp->getImageHeight();
            $photoParraintmp->thumbnailImage(230, 230 * $height / $width);
            $photoParraintmp->writeImage($pathParrain . $nomParrain . '-parrain' . $entityInstance->getEdition() . '.' . $ext);
            $entityInstance->setPhotoparrain($nomParrain . '-parrain' . $entityInstance->getEdition() . '.' . $ext);
        }


        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }


}
