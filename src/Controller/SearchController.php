<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use App\Entity\Odpf\OdpfFichierspasses;
use FOS\ElasticaBundle\Configuration\ManagerInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private ManagerInterface $doctrine;
    private PaginatedFinderInterface $finder;

    public function __construct(PaginatedFinderInterface $finder, ManagerInterface $doctrine)
    {
        $this->finder = $finder;
        $this->doctrine = $doctrine;
    }
    /**
     *
     * @return Response
     * @Route ("/search/searchAction", name="search")
     */
    public function searchAction()
    {

        $articles = $this->finder->findHybrid('soleil');
        dd($articles);

    }

}