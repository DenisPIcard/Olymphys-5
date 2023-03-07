<?php

namespace App\Controller\Adminodpf;

use App\Entity\Cadeaux;
use App\Entity\Centrescia;
use App\Entity\Classement;
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
use App\Entity\Uai;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardOdPFController extends AbstractDashboardController
{
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
        yield MenuItem::linktoRoute('Retour à la page d\'accueil', 'fas fa-home', 'core_home');
        yield MenuItem::linktoRoute('Secrétariat du jury', 'fas fa-pencil-alt', 'secretariatjury_accueil')->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToLogout('Deconnexion', 'fas fa-door-open');


    }

    /**
     * @Route("/adminodpf", name="adminodpf")
     */
    public function index(): Response
    {
        return $this->render('bundles/EasyAdminBundle/page_accueil_odpf.html.twig');
    }
}
