<?php

namespace App\Controller\OdpfAdmin;


use App\Controller\Admin\DashboardController;
use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfCarousels;
use App\Entity\Odpf\OdpfCategorie;
use App\Entity\Odpf\OdpfDocuments;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\Odpf\OdpfFichierIndex;
use App\Entity\Odpf\OdpfFichierspasses;
use App\Entity\Odpf\OdpfLogos;

use App\Entity\Odpf\OdpfPartenaires;
use App\Entity\Odpf\OdpfVideosequipes;
use App\Entity\Photos;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OdpfDashboardController extends AbstractDashboardController
{
    private AdminContextProvider $adminContextProvider;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminContextProvider $adminContextProvider, AdminUrlGenerator $adminUrlGenerator)
    {

        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->adminContextProvider = $adminContextProvider;
    }

    #[Route("/odpfadmin", name: "odpfadmin")]
    public function index(): Response
    {
        if ($this->adminContextProvider->getContext()->getRequest()->query->get('routeName') != null) {

            return $this->redirectToRoute('odpfadmin');
        };
        return $this->render('bundles/EasyAdminBundle/odpf/message_accueil.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="https://upload.wikimedia.org/wikipedia/commons/3/36/Logo_odpf_long.png" alt="logo des OdpF"  width="160"/>');
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
            MenuItem::linkToCrud('Les éditions passées', 'fas fa-list', OdpfEditionsPassees::class),
            MenuItem::linkToCrud('Les équipes passées', 'fa-solid fa-user-group', OdpfEquipesPassees::class),

            MenuItem::linkToCrud('Les mémoires', 'fas fa-book', OdpfFichierspasses::class)
                ->setController(OdpfFichiersPassesCrudController::class)
                ->setQueryParameter('typefichier', 0),

            MenuItem::linkToCrud('Les résumés', 'fas fa-book', OdpfFichierspasses::class)
                ->setController(OdpfFichiersPassesCrudController::class)
                ->setQueryParameter('typefichier', 2),
            MenuItem::linkToCrud('Les présentations', 'fas fa-book', OdpfFichierspasses::class)
                ->setController(OdpfFichiersPassesCrudController::class)
                ->setQueryParameter('typefichier', 3),
            MenuItem::linkToCrud('Les autorisations photos', 'fas fa-book', OdpfFichierspasses::class)
                ->setController(OdpfFichiersPassesCrudController::class)
                ->setQueryParameter('typefichier', 6),

            MenuItem::linkToCrud('Les  photos', 'fas fa-images', Photos::class)
                ->setController(OdpfPhotosCrudController::class),
            MenuItem::linkToCrud('Les  vidéos', 'fas fa-images', OdpfVideosequipes::class)
                ->setController(OdpfVideosEquipesCrudController::class),

        ];

        yield MenuItem::linktoDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Articles', 'fas fa-list', OdpfArticle::class);
        yield MenuItem::linkToCrud('Categories', 'fas fa-list', OdpfCategorie::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Documents du site', 'fas fa-book', OdpfDocuments::class);
        yield MenuItem::linkToCrud('Logos du site', 'fa-solid fa-icons', OdpfLogos::class);
        yield MenuItem::linkToCrud('Carrousels', 'fa-solid fa-clapperboard', OdpfCarousels::class);
        yield MenuItem::linkToCrud('Partenaires', 'fa-solid fa-list', OdpfPartenaires::class);
        yield MenuItem::linkToCrud('Index', 'fas fa-list', OdpfFichierIndex::class);
        yield MenuItem::subMenu('Les éditions passées', 'fa-solid fa-book-bookmark')->setSubItems($submenu1)->setCssClass('text-bold');
        yield MenuItem::linktoRoute('Aller à l\'admin du concours', 'fa-solid fa-marker', 'admin');
        yield MenuItem::linktoRoute('Retour à la page d\'accueil', 'fas fa-home', 'core_home');
        yield MenuItem::linkToLogout('Déconnexion', 'fas fa-door-open');
    }


}
