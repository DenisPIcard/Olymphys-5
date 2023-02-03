<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use App\Entity\Equipes;
use App\Entity\Phrases;
use App\Entity\Prix;
use App\Entity\Visites;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use phpDocumentor\Reflection\Types\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class SelectionneesCrudController extends AbstractCrudController
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
        return Equipes::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'lettre', 'titreProjet', 'ordre', 'heure', 'salle', 'total', 'classement', 'rang', 'nbNotes', 'sallesecours', 'code'])
            ->setPaginatorPageSize(25);
    }

    public function configureActions(Actions $actions): Actions
    {
        $attribHeuresSalles = Action::new('attrib_heures_salles', 'Attribuer les salles et heures', 'fa fa_array',)
            // if the route needs parameters, you can define them:
            // 1) using an array
            ->linkToRoute('attrib_heures_salles')
            ->createAsGlobalAction();
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_INDEX, $attribHeuresSalles)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission('attrib_heures_salles', 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN');


    }

    public function configureFields(string $pageName): iterable
    {


        $lettre = TextField::new('equipeinter.lettre', 'lettre');
        $titreProjet = TextField::new('equipeinter.titreProjet', 'projet');
        $ordre = IntegerField::new('ordre');
        $heure = TextField::new('heure');
        $salle = TextField::new('salle');
        $total = IntegerField::new('total');
        $classement = TextField::new('classement');
        $rang = IntegerField::new('rang');
        $nbNotes = IntegerField::new('nbNotes');

        $visite = AssociationField::new('visite')->setFormType(EntityType::class)->setFormTypeOptions(['required'=>false,
                                            'mapped'=>true,
                                            'class'=>Visites::class,
                                            'query_builder'=>function() {
                                                return $this->doctrine->getRepository(Visites::class)->createQueryBuilder('v')
                                                    ->andWhere('v.attribue =:value')
                                                    ->setParameter('value', '0')
                                                    ->orderBy('v.intitule', 'ASC');
                                            },
                                            'choice_label'=>'getIntitule'
                                          ]);
        $cadeau = AssociationField::new('cadeau')->setFormType(EntityType::class)->setFormTypeOptions(['required'=>false,
                                            'class'=>Cadeaux::class,
                                            'query_builder'=>function() {
                                                return $this->doctrine->getRepository(Cadeaux::class)->createQueryBuilder('c')
                                                    ->andWhere('c.attribue =:value')
                                                    ->setParameter('value', '0')
                                                    ->orderBy('c.raccourci', 'ASC');
                                            },
                                              'choice_label'=>'getContenu']);

        $phrases = CollectionField::new('phrases');

        $prix = AssociationField::new('prix')->setFormType(EntityType::class)->setFormTypeOptions(['required'=>false,
                                            'mapped'=>true,
                                            'class'=>Prix::class,
                                            'query_builder'=>function() {
                                                return $this->doctrine->getRepository(Prix::class)->createQueryBuilder('c')
                                                    ->andWhere('c.attribue =:value')
                                                    ->setParameter('value', '0')
                                                    ->orderBy('c.niveau', 'ASC');
                                            },
                                            'choice_label'=>'getPrix']);
        $infoequipe = TextField::new('equipeinter.infoequipe');

        $notess = AssociationField::new('notess');
        $observateur = TextField::new('observateur');
        $infoequipeLyceeAcademie = TextareaField::new('equipeinter.lyceeAcademie', 'académie');
        $infoequipeLycee = TextareaField::new('equipeinter.nomLycee', 'lycée');
        $infoequipeTitreProjet = TextareaField::new('equipeinter.TitreProjet');
        $id = IntegerField::new('id', 'ID');

            if (isset($_REQUEST['palmares'])) {
                if ($_REQUEST['palmares'] == 1) {
                    if (Crud::PAGE_INDEX === $pageName) {
                        return [$lettre, $titreProjet, $infoequipeLyceeAcademie, $infoequipeLycee, $classement, $rang, $prix, $phrases, $cadeau, $visite];
                    } elseif (Crud::PAGE_DETAIL === $pageName) {
                        return [$id, $lettre, $titreProjet, $ordre, $classement, $visite, $cadeau, $phrases, $prix];
                    } elseif (Crud::PAGE_NEW === $pageName) {
                        return [$lettre, $titreProjet, $classement, $rang, $nbNotes, $visite, $cadeau, $phrases, $prix];
                    } elseif (Crud::PAGE_EDIT === $pageName) {
                        return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $visite, $cadeau, $phrases, $prix];
                    }
                }
                if ($_REQUEST['palmares'] == 0) {
                    if (Crud::PAGE_INDEX === $pageName) {
                        return [$lettre, $titreProjet, $infoequipeLyceeAcademie, $infoequipeLycee, $heure, $salle, $ordre];
                    } elseif (Crud::PAGE_DETAIL === $pageName) {
                        return [$id, $lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $infoequipe, $notess];
                    } elseif (Crud::PAGE_NEW === $pageName) {
                        return [$lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes, $infoequipe, $notess];
                    } elseif (Crud::PAGE_EDIT === $pageName) {
                        return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $heure, $salle, $ordre];
                    }
                }
            }
            else{
                dd($_REQUEST);
            }


    }

    public function edit(AdminContext $context)
    {
        if($context->getRequest()->query->get('palmares')!=null){

            $_REQUEST['palmares'] = $context->getRequest()->query->get('palmares');
        };
        if($context->getReferrer()!=null) {
            $pos = stripos($context->getReferrer(), 'palmares');
            $param = substr($context->getReferrer(), $pos + 9, 1);//Deux valeurs possibles pour $param : 0 : on édite l'administration d'un équipe, 1 : on édite le palmarès
            //le paramètre est défini dans le dashboard mais disparait lorsque l'index est affiché, on peut le trouver dans le referrer du $context
            $_REQUEST['palmares'] = $param;
        }

        return parent::edit($context); // TODO: Change the autogenerated stub
    }

    public function index(AdminContext $context)
    {
        $referrer= $context->getReferrer();

        if ($referrer!==null) {
            //Deux valeurs possibles pour $param : 0 : on édite l'administration d'un équipe, 1 : on édite le palmarès
            //le paramètre est défini dans le dashboard mais disparait  lorsque l'index est affiché, on peut le trouver dans le referrer du $context
            $pos= stripos($context->getReferrer(),'palmares');

            $param=substr($context->getReferrer(),$pos+9,1);

            $_REQUEST['palmares'] = $param;
        }
        return parent::index($context); // TODO: Change the autogenerated stub
    }

    /**
     * @Route("/Admin/SelectionneesCrud/attrib_heures_salles", name="attrib_heures_salles")
     */
    public function attrib_heure_salle(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('fichier', FileType::class)
            ->add('save', SubmitType::class)
            ->getForm();


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $fichier = $data['fichier'];
            $spreadsheet = IOFactory::load($fichier);
            $worksheet = $spreadsheet->getActiveSheet();

            $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();

            $em = $this->doctrine->getManager();
            //$lettres = range('A','Z') ;
            $repositoryEquipes = $this->doctrine->getManager()
                ->getRepository(Equipes::class);


            for ($row = 2; $row <= $highestRow; ++$row) {

                $lettre = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $equipe = $repositoryEquipes->createQueryBuilder('e')
                    ->leftJoin('e.equipeinter', 'eq')
                    ->andWhere('eq.lettre =:lettre')
                    ->setParameter('lettre', $lettre)
                    ->getQuery()->getSingleResult();
                $ordre = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $equipe->setOrdre($ordre);
                $heure = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                $equipe->setHeure($heure);
                $salle = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                $equipe->setSalle($salle);

                $em->persist($equipe);
                $em->flush();
            }

            return $this->redirectToRoute('admin');
        }
        return $this->render('/secretariatjury/charge_donnees_excel_equipes.html.twig', array('form' => $form->createView()));
    }
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {   dd($entityInstance);
        parent::updateEntity($entityManager, $entityInstance); // TODO: Change the autogenerated stub
    }
}
