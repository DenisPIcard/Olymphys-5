<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class GoutteClientController extends AbstractController
{
    /**
     * @Route("/goutte/client", name="goutte_client")
     */
    public function index(): Response
    {
        $url = "https://odpf.org/la-xxvie-2019.html";
        $client = new Client();
        $crawler = $client->request('GET', $url);
        $nb_liens = $crawler->count();
        //dd($nb_liens);

        $i = 0;
        if ($nb_liens > 0) {
            $liens1 = $crawler->filter('a.gd-rouge')->links();
            $liens2 = $crawler->filter('a.big-red-pdf')->links();
            //dump($liens);
            $tous_liens1 = [];
            $tous_liens2 = [];
            foreach ($liens1 as $lien) {
                $tous_liens1[] = $lien->getUri();
            }
            $tous_liens1 = array_unique($tous_liens1);
            foreach ($liens2 as $lien) {
                $tous_liens2[] = $lien->getUri();
            }
            $tous_liens2 = array_unique($tous_liens2);

            $nodeValuesmem = $crawler->filter('.thesis-list li a')->each(function (Crawler $node) {
                return $node->text();
            });

            $nodeValues = $crawler->filter('p')->each(function (Crawler $node, $i) {
                return $node->text();
            });
            $liensmem = $crawler->filter('ul.thesis-list a')->links();

            $tous_liensmem = [];
            foreach ($liensmem as $lien) {
                $tous_liensmem[] = $lien->getUri();
            }
            $tous_liensmem = array_unique($tous_liensmem);


        } else {
            $liens[0] = "Pas de liens";
        }

        return $this->render('goutte_client/crawl.html.twig', array('tous_liens1' => $tous_liens1,
                'tous_liens2' => $tous_liens2,
                'tous_liensmem' => $tous_liensmem,
                'nodevalues' => $nodeValues,
                'nodevaluesmem' => $nodeValuesmem,)
        );
    }

}
