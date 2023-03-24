<?php
// app/src/Controller/SearchController.php
namespace App\Controller;
use ACSEO\TypesenseBundle\Finder\CollectionFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use ACSEO\TypesenseBundle\Finder\TypesenseQuery;
use Symfony\Component\Routing\Annotation\Route;

//
class TypeSenseController extends AbstractController
{
    private $userFinder;

    public function __construct($userFinder)
    {
        $this->userFinder = $userFinder;
    }
    #[Route("/search/search", name:"searchTypeSense")]
    public function search()
    {
        $query = new TypesenseQuery('jouve', 'nom');
        //dd($this->userFinder);
        // Get Doctrine Hydrated objects
        $results = $this->userFinder->query($query)->getResults();
        dd($results);
        // dump($results)
        // array:2 [▼
        //    0 => App\Entity\Book {#522 ▶}
        //    1 => App\Entity\Book {#525 ▶}
        //]

        // Get raw results from Typesence
        $rawResults = $this->userFinder->rawQuery($query)->getResults();
        dd($rawResults);
        // dump($rawResults)
        // array:2 [▼
        //    0 => array:3 [▼
        //        "document" => array:4 [▼
        //        "author" => "Jules Vernes"
        //        "id" => "100"
        //        "published_at" => 1443744000
        //        "title" => "Voyage au centre de la Terre "
        //       ]
        //       "highlights" => array:1 [▶]
        //       "seq_id" => 4
        //    ]
        //    1 => array:3 [▼
        //        "document" => array:4 [▶]
        //        "highlights" => array:1 [▶]
        //        "seq_id" => 6
        //    ]
        // ]
    }}