<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use App\Entity\Odpf\OdpfFichierspasses;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private RepositoryManagerInterface $repositoryManager;
    private PaginatedFinderInterface $finder;

    public function __construct(PaginatedFinderInterface $finder, RepositoryManagerInterface $repositoryManager)
    {
        $this->finder = $finder;
        $this->repositoryManager = $repositoryManager;
    }
    /**
     *
     * @return Response
     * @Route ("/search/searchAction", name="search")
     */
    public function searchAction()
    {
        //$finder = $this->container->get('fos_elastica.finder.odpfFichierspasses');
       //$results = $this->finder->find('Soleil');
       //$search = Util::escapeTerm('30-eq-5-Resume-Les Cafeines.pdf');
        //dd($odpfFichierspassesFinder);


        //dump($search);
        // Option 2. Returns a set of hybrid results that contain all Elasticsearch results
        // and their transformed counterparts. Each result is an instance of a HybridResult
       /* $paginator = $this->get('knp_paginator');
        $results = $this->finder->createPaginatorAdapter('bob');
        $pagination = $paginator->paginate($results, $page, 10);

        $options = [
            'sortNestedPath' => 'owner',
            'sortNestedFilter' => new Query\Term(['enabled' => ['value' => true]]),
        ];*/
        $repository = $this->repositoryManager->getRepository(OdpfFichierspasses::class);


        $articles = $repository->find('soleil');
        dd($articles);
        // sortNestedPath and sortNestedFilter also accepts a callable
        // which takes the current sort field to get the correct sort path/filter

       // $pagination = $paginator->paginate($results, $page, 10, $options);
    }

}