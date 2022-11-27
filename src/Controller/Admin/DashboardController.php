<?php

namespace App\Controller\Admin;

use App\Entity\Cadeaux;
use App\Entity\Centrescia;
use App\Entity\Repartprix;
use App\Entity\Coefficients;
use App\Entity\Docequipes;
use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipes;
use App\Entity\Equipesadmin;
use App\Entity\Fichiersequipes;
use App\Entity\Jures;
use App\Entity\Photos;
use App\Entity\Prix;
use App\Entity\User;
use App\Entity\Videosequipes;
use App\Entity\Visites;
use App\Entity\Professeurs;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private AdminContextProvider $adminContextProvider;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminContextProvider $adminContextProvider, AdminUrlGenerator $adminUrlGenerator)
    {

        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->adminContextProvider = $adminContextProvider;
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="https://upload.wikimedia.org/wikipedia/commons/3/36/Logo_odpf_long.png"" alt="logo des OdpF"  width="160"/>');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/fonts.css');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm:ss')
            ->setTimeFormat('HH:mm');
    }

    public function configureMenuItems(): iterable
    {

        $submenu1 = [
            MenuItem::linkToCrud('Centres interacadémiques', 'fas fa-city', Centrescia::class),

            MenuItem::linkToCrud('Les mémoires', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 0)
                ->setQueryParameter('concours', 0),

            MenuItem::linkToCrud('Les résumés', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 2)
                ->setQueryParameter('concours', 0),
            MenuItem::linkToCrud('Les fiches sécurités', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 4)
                ->setQueryParameter('concours', 0),
            MenuItem::linkToCrud('Les diaporamas', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 5)
                ->setQueryParameter('concours', 0),
            MenuItem::linkToCrud('Les vidéos des équipes', 'fas fa-film', Videosequipes::class),

            MenuItem::linkToCrud(' Les autorisations photos', 'fas fa-balance-scale', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 6)
                ->setQueryParameter('concours', 0),
            MenuItem::linkToCrud(' Les photos', 'fas fa-images', Photos::class)
                ->setController(PhotosCrudController::class)
                ->setQueryParameter('concours', 'interacadémique'),
            MenuItem::linkToCrud(' Les questionnaires ', 'fas fa-images', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 7)
                ->setQueryParameter('concours', 'interacadémique'),
        ];

        $submenu2 = [
            MenuItem::section('Equipes'),
            MenuItem::linkToCrud('Palmares des équipes', 'fas fa-asterisk', Equipes::class)->setQueryParameter('palmares', true),
            MenuItem::linkToCrud('Administration des équipes', 'fas fa-user-friends', Equipes::class)->setQueryParameter('palmares', false),
            MenuItem::linkToCrud('Les mémoires', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 0)
                ->setQueryParameter('concours', 1),
            MenuItem::linkToCrud('Les résumés', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 2)
                ->setQueryParameter('concours', 1),
            MenuItem::linkToCrud('Les présentations', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 3)
                ->setQueryParameter('concours', 1),

            MenuItem::linkToCrud('Les vidéos des équipes', 'fas fa-film', Videosequipes::class),
            MenuItem::linkToCrud('Les photos', 'fas fa-images', Photos::class)
                ->setController(PhotosCrudController::class)
                ->setQueryParameter('concours', 'national'),
            MenuItem::linkToCrud('Les fiches sécurité', 'fas fa-book', Fichiersequipes::class)
                ->setController(FichiersequipesCrudController::class)
                ->setQueryParameter('typefichier', 4)
                ->setQueryParameter('concours', 1),
            MenuItem::section('Les recompenses')->setPermission('ROLE_SUPER_ADMIN'),
            MenuItem::linkToCrud('Répartition des prix', 'fas fa-asterisk', Repartprix::class)->setPermission('ROLE_SUPER_ADMIN'),
            MenuItem::linkToCrud('Les Prix', 'fas fa-asterisk', Prix::class)->setPermission('ROLE_SUPER_ADMIN'),
            MenuItem::linkToCrud('Les Visites', 'fas fa-asterisk', Visites::class)->setPermission('ROLE_SUPER_ADMIN'),
            MenuItem::linkToCrud('Cadeaux', 'fas fa-asterisk', Cadeaux::class)->setPermission('ROLE_SUPER_ADMIN'),
        ];
        yield MenuItem::linktoRoute('Retour à la page d\'accueil', 'fas fa-home', 'core_home');
        yield MenuItem::linkToCrud('Gestion des éditions', 'fas fa-cogs', Edition::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Affectation des jurés', 'fas fa-graduation-cap', Jures::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Coefficients', 'fas fa-graduation-cap', Coefficients::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Documents à télécharger', 'fas fa-book', Docequipes::class);
        yield MenuItem::linkToCrud('Equipes inscrites', 'fas fa-user-friends', Equipesadmin::class);
        yield MenuItem::linkToCrud('Elèves inscrits', 'fas fa-child', Elevesinter::class);
        yield MenuItem::linkToCrud('Professeurs', 'fas fa-chalkboard-teacher', Professeurs::class);
        yield MenuItem::linkToCrud('Etablissements', 'fas fa-school', Equipesadmin::class)
            ->setController(EquipesadminCrudController::class)
            ->setQueryParameter('lycees', 1);
        yield MenuItem::subMenu('Concours interacadémique')->setSubItems($submenu1)->setCssClass('text-bold');
        yield MenuItem::subMenu('Concours national')->setSubItems($submenu2);
        yield MenuItem::linktoRoute('Administration du site', 'fa-solid fa-pager', 'odpfadmin');

        yield MenuItem::linktoRoute('Retour à la page d\'accueil', 'fas fa-home', 'core_home');
        yield MenuItem::linktoRoute('Secrétariat du jury', 'fas fa-pencil-alt', 'secretariatjury_accueil')->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToLogout('Deconnexion', 'fas fa-door-open');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        if ($this->adminContextProvider->getContext()->getRequest()->query->get('routeName') != null) {

            return $this->redirectToRoute('admin');
        };

        return $this->render('bundles/EasyAdminBundle/page_accueil.html.twig');
    }
}