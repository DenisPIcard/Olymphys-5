<?php

namespace App\Controller\OdpfAdmin;

use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfCarousels;
use App\Entity\Odpf\OdpfImagescarousels;
use App\Form\OdpfChargeDiapoType;
use App\Form\OdpfImagesType;
use App\Service\ImagesCreateThumbs;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PharIo\Version\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class OdpfCarouselsCrudController extends AbstractCrudController
{

    private ManagerRegistry $doctrine;
    private AdminContextProvider $context;
    private AdminUrlGenerator $adminUrlGenerator;


    public function __construct(ManagerRegistry $doctrine, AdminContextProvider $adminContextProvider, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->doctrine = $doctrine;
        $this->context = $adminContextProvider;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return OdpfCarousels::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $title1 = '';
        $title2 = '';

        if (isset($_REQUEST['crudAction'])) {
            if ($_REQUEST['crudAction'] == 'edit') {
                $title1 = 'Edition du carousel : ' . $this->doctrine->getRepository(OdpfCarousels::class)->findOneBy(['id' => $_REQUEST['entityId']])->getName();
            }
            if ($_REQUEST['crudAction'] == 'new') {
                $title2 = 'Nouveau carousel ';
            }

        }
        $crud = Crud::new()->setFormThemes(['bundles/EasyAdminBundle/odpf/odpf_form_images_carousels.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->overrideTemplate('crud/edit', 'bundles/EasyAdminBundle/crud/edit.html.twig')
            ->setPageTitle('edit', $title1)
            ->setPageTitle('new', $title2)
            ->setHelp('new', ' Veuillez saisir le nom et puis cliquer sur créer et  continuer pour ajouter des diapositives');

        return $crud;
    }

    /*
        public function new(AdminContext $context): RedirectResponse
        {
            $carousel = new OdpfCarousels();
            $nombre = count($this->doctrine->getRepository(OdpfCarousels::class)->findAll());
            $carousel->setName('Nouveau carousel' . $nombre);
            //$this->doctrine->getManager()->persist($carousel);
            //$this->doctrine->getManager()->flush();
            //$idCarousel = $carousel->getId();
            $url = $this->adminUrlGenerator
                ->setController(OdpfCarouselsCrudController::class)
                ->setAction('new')
                ->setDashboard(OdpfDashboardController::class)
                ->generateUrl();
            return new RedirectResponse($url);

        }
    */

    public function configureAssets(Assets $assets): Assets
    {
        $url = $this->generateUrl('supr_diapo');

        return $assets
            ->addHtmlContentToBody('
            <div class="modal fade" id="modaldiapo" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
        
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="ModaldiapoLabel">La diapositive sera supprimée </h4>
                            <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            
                            <div class="alert hidden" role="alert" id="modalAlert"></div>
                            
                               <p><h6 >Attention ! <br>Supprimer la diapositive ? </h6> </p>
                        
                        </div>
                        <div class="modal-footer">
                            <form action="' . $url . '">
                                <button type="button" data-bs-dismiss="modal" class="btn btn-secondary">
                                        <span class="btn-label">Annuler</span>
                                </button>
                                
                                <input type="hidden" class="form-control" id="diapoId" name="diapoID" value="recipient-name"/>
                                <button type="submit"  class="btn btn-danger" data-bs-dismiss="modal" id="diapo-delete-button">
                                        <span class="btn-label">Supprimer</span>
                                </button>
                            </form>
                        </div>
                        
        
                    </div>
                </div>
            </div>
    <script text/javascript>

        var modal = document.getElementById(\'modaldiapo\')
        modal.addEventListener(\'show.bs.modal\', function (event) {
                // Button that triggered the modal
                var button = event.relatedTarget
                // Extract info from data-bs-* attributes
                var recipient = button.getAttribute(\'data-bs-idDiapo\')
                console.log(recipient)
                var modalFooterInput = modal.querySelector(\'.modal-footer input\')
                modalFooterInput.value = recipient
        }) 
           
        </script>
        <script text/javascript>
         $("#modaldiapo").on("submit", function (e) {
                    var formURL = $(this).attr("action");
                    console.log(formURL);
                    $.ajax({
                        url: formURL,
                        type: "GET",
                        data: {
                            idDiapo: $("#diapoId").val(),
                           
                        },
                        console.log(data);
                    });
                });    
    
         </script>
         ');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_EDIT, Action::INDEX, 'Retour à la liste')
            ->add(Crud::PAGE_NEW, Action::INDEX, 'Retour à la liste')
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        return parent::configureActions($actions); // TODO: Change the autogenerated stub
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName == 'edit') {

            $carousel = $this->doctrine->getRepository(OdpfCarousels::class)->find($_REQUEST['entityId']);
            $listeImages = $this->doctrine->getRepository(OdpfImagescarousels::class)->createQueryBuilder('i')
                ->where('i.carousel =:carousel')
                ->setParameter('carousel', $carousel)
                ->addOrderBy('i.numero', 'ASC');
        }
        $name = TextField::new('name', 'nom');
        $images = CollectionField::new('images')->setEntryType(OdpfImagesType::class)
            ->setFormTypeOptions(['block_name' => 'image', 'allow_add' => true, 'prototype' => true, 'allow_delete' => false])
            ->setEntryIsComplex(true)
            ->renderExpanded(true);
        $blackbgnd = BooleanField::new('blackbgnd', 'Fond noir');
        $updatedAt = DateTimeField::new('updatedAt');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $images, $updatedAt];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$name, $images, $updatedAt];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $blackbgnd];
        } elseif (Crud::PAGE_EDIT === $pageName) {

            return [$name, $blackbgnd, $images];
        }


    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $repositoryArticle = $this->doctrine->getRepository(OdpfArticle::class);
        $em = $this->doctrine->getManager();

        $articles = $repositoryArticle->findBy(['carousel' => $entityInstance]);
        foreach ($articles as $article) {
            $article->setCarousel(null);
            $em->persist($article);
            $em->flush();
        }
        $images = $entityInstance->getImages();

        foreach ($images as $image) {


            $entityInstance->removeImage($image);


        }
        parent::deleteEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        $this->doctrine->getManager()->persist($entityInstance);
        $this->doctrine->getManager()->flush();
        $images = $entityInstance->getImages();
        foreach ($images as $image) {
            if (file_exists('odpf/odpf-images/imagescarousels/' . $image->getName())) {
                $imagesCreateThumbs = new ImagesCreateThumbs();
                $imagesCreateThumbs->createThumbs($image);
            }
        }
        parent::persistEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $idCarousel = $entityInstance->getId();
        $carouselOrigine = $this->doctrine->getRepository(OdpfCarousels::class)->findOneBy(['id' => $idCarousel]);
        $images = $entityInstance->getImages();

        $i = 0;
        $imagesRemoved = null;
        foreach ($images as $image) {

            if ($image->getImagefile()->getPath() == '/tmp') {

                $imagesRemoved[$i] = $image->getName();
                $i = +1;
            }
        }
        $this->doctrine->getManager()->persist($entityInstance);
        $this->doctrine->getManager()->flush();
        //$imagesCreateThumbs = new ImagesCreateThumbs();
        /*foreach ($images as $image) {
            if (file_exists('odpf-images/imagescarousels/'.$image->getName())) {

                $imagesCreateThumbs->createThumbs($image);
            }
        }*/
        if ($imagesRemoved !== null) {   //pour effacer les images intiales après leur remplacement dans le carousel
            foreach ($imagesRemoved as $imageRemoved) {
                if ($imageRemoved !== null) {
                    if (file_exists($this->getParameter('app.path.imagescarousels') . '/' . $imageRemoved)) {
                        unlink($this->getParameter('app.path.imagescarousels') . '/' . $imageRemoved);
                    }
                }
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @Route("/admin/OdpfCarousels/add_diapo,{idCarousel},{idDiapo}", name="add_diapo")
     *
     */
    public function addDiapo(Request $request, $idCarousel, $idDiapo): RedirectResponse|Response
    {
        $carousel = $this->doctrine->getRepository(OdpfCarousels::class)->findOneBy(['id' => $idCarousel]);
        $url = $this->adminUrlGenerator
            ->setController(OdpfCarouselsCrudController::class)
            ->setAction('edit')
            ->setEntityId($idCarousel)
            ->setDashboard(OdpfDashboardController::class)
            ->generateUrl();
        $idDiapo == 0 ? $diapo = new OdpfImagescarousels() : $diapo = $this->doctrine->getRepository(OdpfImagescarousels::class)->findOneBy(['id' => $idDiapo]);
        $diapo->setCarousel($carousel);
        $form = $this->createForm(OdpfChargeDiapoType::class, $diapo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if (null !== $form->get('image')->getData()) {
                $filePath = substr($form->get('image')->getData(), 1);
                $filePath = str_replace("%20", " ", $filePath);
                $filePath = str_replace("%2C", ",", $filePath);
                $pathtmp = $this->getParameter('app.path.odpf') . '/odpf-images/imagescarousels/tmp/';
                $arrayPath = explode('/', $filePath);
                $filename = $arrayPath[array_key_last($arrayPath)];
                try {
                    copy($filePath, $pathtmp . $filename);
                    $file = new UploadedFile($pathtmp . $filename, $filename, null, null, true);
                    $diapo->setImageFile($file);
                } catch (Exception $e) {

                }

            }
            $listImages = $carousel->getImages();

            if (count($listImages) != 0) {
                $i = 0;
                foreach ($listImages as $image) {
                    $numeros[$i] = $image->getNumero();
                }
                $nummax = max($numeros);
                $numero = $nummax + 1;
                foreach ($listImages as $image) {
                    if ($idDiapo == $image->getId()) {
                        $numero = $image->getNumero();
                    }
                }

            } else {
                $numero = 1;
            }
            $diapo->setNumero($numero);
            $em = $this->doctrine->getManager();
            $em->persist($diapo);
            $em->flush();
            return new RedirectResponse($url);
        }
        return $this->render('OdpfAdmin/charge-diapo.html.twig', ['form' => $form->createView(), 'idCarousel' => $idCarousel, 'url' => $url]);

    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @Route("/admin/OdpfCarousels/supr_diapo", name="supr_diapo")
     *
     */
    public function suprDiapo(Request $request): RedirectResponse|Response
    {

        $idDiapo = str_replace('"', '', $request->query->get('diapoID'));
        $diapo = $this->doctrine->getRepository(OdpfImagescarousels::class)->findOneBy(['id' => $idDiapo]);
        $numeroDiapoSupr = $diapo->getNumero();
        $carousel = $diapo->getCarousel();
        $idCarousel = $carousel->getId();
        $url = $this->adminUrlGenerator
            ->setController(OdpfCarouselsCrudController::class)
            ->setAction('edit')
            ->setEntityId($idCarousel)
            ->setDashboard(OdpfDashboardController::class)
            ->generateUrl();
        $carousel->removeImage($diapo);
        $listImages = $this->doctrine->getRepository(OdpfImagescarousels::class)->createQueryBuilder('i')
            ->where('i.carousel =:carousel')
            ->andWhere('i.numero >:numsupr')
            ->setParameters(['carousel' => $carousel, 'numsupr' => $numeroDiapoSupr])
            ->addOrderBy('i.numero', 'ASC')
            ->getQuery()->getResult();

        $em = $this->doctrine->getManager();
        $em->remove($diapo);
        $em->flush();
        $em->persist($carousel);
        $em->flush();

        if ($listImages) {
            foreach ($listImages as $image) {
                $nvNumero = $image->getNumero() - 1;
                $ancNom = $image->getName();
                $image->setNumero($nvNumero);
                $image = $this->renameDiapo($nvNumero, $image);
                $em->persist($image);
                $em->flush();
                if (file_exists('odpf/odpf-images/imagescarousels/' . $ancNom)) {
                    unlink('odpf/odpf-images/imagescarousels/' . $ancNom);
                }
            }
        }
        if (file_exists('odpf/odpf-images/imagescarousels/' . $diapo->getName())) {
            unlink('odpf/odpf-images/imagescarousels/' . $diapo->getName());
        }
        return new RedirectResponse($url);

    }

    public function renameDiapo($numero, $diapo): OdpfImagescarousels
    {
        $nomPhoto = $diapo->getName();
        $noms = explode('diapo', $nomPhoto);
        $pos = strpos($noms[1], '_');
        $substr = substr($noms[1], $pos);
        $substr = $numero . $substr;
        $nvNomPhoto = $noms[0] . 'diapo' . $substr;
        $diapo->setName($nvNomPhoto);
        rename('odpf/odpf-images/imagescarousels/' . $nomPhoto, 'odpf/odpf-images/imagescarousels/' . $nvNomPhoto);
        return $diapo;
    }

    /**
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @Route("/admin/OdpfCarousels/bouge_diapo,{idDiapo},{updown}", name="bouge_diapo")
     *
     */
    public function bougeDiapo($idDiapo, $updown): RedirectResponse|Response
    {
        $diapoBouge = $this->doctrine->getRepository(OdpfImagescarousels::class)->findOneBy(['id' => $idDiapo]);
        $idCarousel = $diapoBouge->getCarousel()->getId();
        $listeImages = $this->doctrine->getRepository(OdpfImagescarousels::class)->createQueryBuilder('i')
            ->where('i.carousel =:carousel')
            ->setParameter('carousel', $diapoBouge->getCarousel())
            ->addOrderBy('i.numero', 'ASC')
            ->getQuery()->getResult();
        $numeroMax = $listeImages[count($listeImages) - 1]->getNumero();
        $url = $this->adminUrlGenerator
            ->setController(OdpfCarouselsCrudController::class)
            ->setAction('edit')
            ->setEntityId($idCarousel)
            ->setDashboard(OdpfDashboardController::class)
            ->generateUrl();
        $numero = $diapoBouge->getNumero();

        if (($updown == 'up') and ($numero == 1)) {
            return new RedirectResponse($url);
        }
        if (($updown == 'down') and ($numero == $numeroMax)) {
            return new RedirectResponse($url);
        }
        $updown == 'down' ? $nvNumero = $numero + 1 : $nvNumero = $numero - 1;
        $ancNomDiapoBouge = $diapoBouge->getName();
        $diapoBouge->setNumero($nvNumero);
        $diapoBouge = $this->renameDiapo($nvNumero, $diapoBouge);

        $diapoUpDown = $this->doctrine->getRepository(OdpfImagescarousels::class)->findOneBy(['numero' => $nvNumero, 'carousel' => $diapoBouge->getCarousel()]);
        $diapoUpDown->setNumero($numero);
        $ancNomDiapoUpDown = $diapoUpDown->getName();
        $diapoUpDown = $this->renameDiapo($numero, $diapoUpDown);

        $this->doctrine->getManager()->persist($diapoBouge);
        $this->doctrine->getManager()->persist($diapoUpDown);
        $this->doctrine->getManager()->flush();

        unlink('odpf/odpf-images/imagescarousels/' . $ancNomDiapoBouge);
        unlink('odpf/odpf-images/imagescarousels/' . $ancNomDiapoUpDown);

        return new RedirectResponse($url);
    }

    public function edit(AdminContext $context)
    {
        $idCarousel = $context->getRequest()->get('entityId');
        $carousel = $this->doctrine->getRepository(OdpfCarousels::class)->findOneBy(['id' => $idCarousel]);
        $diapos = $carousel->getImages();
        if ($diapos) {
            $i = 1;
            foreach ($diapos as $diapo) {
                $numeros[$i] = $diapo->getNumero();
                if (($numeros[$i] === null) or ($numeros[$i] == 0)) {

                    $diapo->setNumero($i);
                    $nom = $diapo->getName();
                    if (file_exists('odpf/odpf-images/imagescarousels/' . $nom)) {
                        $noms = explode('.', $nom);
                        $uploadTime = new datetime('now');
                        $time = $uploadTime->format('y-m-d_H-i-s');
                        $noms[count($noms) - 2] = $noms[count($noms) - 2] . '-diapo' . $i . '_' . $time . '.';
                        $nvNom = '';
                        for ($j = 0; $j < count($noms); $j++) {
                            $nvNom = $nvNom . $noms[$j];
                        }
                        $diapo->setName($nvNom);

                        rename('odpf/odpf-images/imagescarousels/' . $nom, 'odpf/odpf-images/imagescarousels/' . $nvNom);
                        $this->doctrine->getManager()->persist($diapo);
                        $this->doctrine->getManager()->flush();
                        $i = $i + 1;
                    }


                }
            }

        }


        return parent::edit($context); // TODO: Change the autogenerated stub
    }

}
