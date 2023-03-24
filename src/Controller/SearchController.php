<?php
// app/src/Controller/SearchController.php
namespace App\Controller;

use AllowDynamicProperties;
use App\Entity\Odpf\OdpfFichierspasses;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Monolog\Handler\ElasticaHandler;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use function json_decode;

class SearchController extends AbstractController
{
    private $repositoryManager;

    public function __construct(RepositoryManagerInterface $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    #[Route("/search/searchAction", name:"search")]
    public function searchAction(): Response
    {
        $repository = $this->repositoryManager->getRepository(User::class);

        /** var array of App\UserBundle\Entity\User */
        $users = $repository->find('Jouve');
        dd($users);

    }

}