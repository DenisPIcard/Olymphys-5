<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Elastica\Util;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @param TransformedFinder $fichierspassesFinder
     * @return Response
     * @Route ("/search/searchAction", name="search")
     */
    public function searchAction(TransformedFinder $fichierspassesFinder) : Response
    {
        $search = Util::escapeTerm("30");

        $result = $fichierspassesFinder->findHybrid($search, 10);

        dd($result);

        return $this->render("views/empty.html.twig");
    }
}