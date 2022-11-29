<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Elastica\Util;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{   private $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }
    /**
     * @param TransformedFinder $fichierspassesFinder
     * @return Response
     * @Route ("/search/searchAction", name="search")
     */
    public function searchAction() : Response
    {
        $results = $this->finder->find('example.net');

        // Option 2. Returns a set of hybrid results that contain all Elasticsearch results
        // and their transformed counterparts. Each result is an instance of a HybridResult
        $results = $this->finder->findHybrid('soleil');
        dd($results);

        return $this->render("views/empty.html.twig");
    }
}