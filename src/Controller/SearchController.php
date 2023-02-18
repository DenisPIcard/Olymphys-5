<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use App\Entity\Odpf\OdpfFichierspasses;


use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{


    public function __construct(private PaginatedFinderInterface $finder, EntityManagerInterface $doctrine)
    {

    }
    #[Route("/search/searchAction", name:"search")]

    public function searchAction()
    {

        $articles = $this->finder->findHybrid('soleil');
        dd($articles);

    }

}