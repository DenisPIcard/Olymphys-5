<?php

namespace App\Controller\Admin;

use App\Entity\Equipes;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

        $visite = AssociationField::new('visite');
        $cadeau = AssociationField::new('cadeau');
        $phrases = AssociationField::new('phrases');

        $prix = AssociationField::new('prix');
        $infoequipe = TextField::new('equipeinter.infoequipe');

        $notess = AssociationField::new('notess');
        //$hote = AssociationField::new('hote');
        //$interlocuteur = TextField::new('interlocuteur');
        $observateur = TextField::new('observateur');
        $infoequipeLyceeAcademie = TextareaField::new('equipeinter.lyceeAcademie', 'académie');
        $infoequipeLycee = TextareaField::new('equipeinter.Lycee', 'lycée');
        $infoequipeTitreProjet = TextareaField::new('equipeinter.TitreProjet');
        $id = IntegerField::new('id', 'ID');
        //$hotePrenomNom = TextareaField::new('hote.PrenomNom', 'hote');
        // $interlocuteurPrenomNom = TextareaField::new('interlocuteur.PrenomNom', 'interlocuteur');


            if ($_REQUEST['palmares'] == 1) {
                if (Crud::PAGE_INDEX === $pageName) {
                    return [$lettre, $titreProjet, $infoequipeLyceeAcademie, $infoequipeLycee, $classement, $rang, $prix, $phrases, $cadeau, $visite];
                }
                elseif (Crud::PAGE_DETAIL === $pageName) {
                    return [$id, $lettre, $titreProjet, $ordre, $classement,  $visite, $cadeau, $phrases, $prix ];
                } elseif (Crud::PAGE_NEW === $pageName) {
                    return [$lettre, $titreProjet,  $classement, $rang, $nbNotes, $visite, $cadeau, $phrases, $prix];
                } elseif (Crud::PAGE_EDIT === $pageName) {
                    return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $visite, $cadeau, $phrases, $prix ];
                }
            }
            if ($_REQUEST['palmares'] == 0) {
                if (Crud::PAGE_INDEX === $pageName) {
                    return [$lettre, $titreProjet, $infoequipeLyceeAcademie, $infoequipeLycee, $heure, $salle, $ordre];
                }
                elseif (Crud::PAGE_DETAIL === $pageName) {
                    return [$id, $lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes,  $infoequipe, $notess];
                } elseif (Crud::PAGE_NEW === $pageName) {
                    return [$lettre, $titreProjet, $ordre, $heure, $salle, $total, $classement, $rang, $nbNotes,  $infoequipe, $notess];
                } elseif (Crud::PAGE_EDIT === $pageName) {
                    return [$lettre, $infoequipeLyceeAcademie, $infoequipeLycee, $infoequipeTitreProjet, $heure, $salle,$ordre];
                }
            }


    }

    public function edit(AdminContext $context)
    {
        $param= explode('=',explode('&',$context->getReferrer())[2])[1];
        $_REQUEST['palmares']=$param;
        return parent::edit($context); // TODO: Change the autogenerated stub
    }
    public function index(AdminContext $context)
    {
        $referrer= $context->getReferrer();
        if ($referrer!==null) {
            $param = explode('=', explode('&', $context->getReferrer())[2])[1];
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
}
