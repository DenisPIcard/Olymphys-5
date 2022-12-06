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
{
    /**
     *
     * @return Response
     * @Route ("/search/searchAction", name="search")
     */
    public function searchAction(TransformedFinder $odpfFichierspassesFinder) : Response
    {
        //$finder = $this->container->get('fos_elastica.finder.odpfFichierspasses');
        //$results = $this->finder->find('');
       $search = Util::escapeTerm('30-eq-5-Resume-Les Cafeines.pdf');
        //dd($odpfFichierspassesFinder);
        $params = [
            'index' => 'odpfFichierspasses',
            'body'  => [
                'query' => [
                    'match' => [
                        'testField' => $search
                    ]
                ]
            ]
        ];
      $results =  $odpfFichierspassesFinder->find($search, 10);
        //dump($search);
        // Option 2. Returns a set of hybrid results that contain all Elasticsearch results
        // and their transformed counterparts. Each result is an instance of a HybridResult

        dd($results);

        return $this->render("views/empty.html.twig");
    }
}