<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use App\Entity\Odpf\OdpfFichierspasses;


use Doctrine\ORM\EntityManagerInterface;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(private readonly PaginatedFinderInterface $finder)
    {

    }
    #[Route("/search/searchAction", name:"search")]
    public function searchAction(): Response
    {

        $results = $this->finder->find('memoire');
        dd($results);

    }

}