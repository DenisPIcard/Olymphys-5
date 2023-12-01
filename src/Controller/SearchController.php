<?php

namespace App\Controller;

use App\Entity\Odpf\OdpfFichierspasses;
use App\Entity\Photos;
use App\Service\Mailer;
use Doctrine\DBAL\Types\TextType;

use Doctrine\Persistence\ManagerRegistry;
use mysql_xdevapi\Exception;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\fileExists;

class SearchController extends AbstractController
{

    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine,)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;

    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request): Response  //d'après https://programmatek.com/build-php-search-engine/
    {
        $fichiersRepo = $this->doctrine->getRepository(OdpfFichierspasses::class);
        $imagesRepo = $this->doctrine->getRepository(Photos::class);
        $textSearch = $request->query->get('search-text');
        $config = new \Smalot\PdfParser\Config();
        $config->setFontSpaceLimit(-60);
        $parser = new Parser([], $config);
        if ($textSearch != '') {
            $items = explode(' ', $textSearch);

            $fichiers = $fichiersRepo->createQueryBuilder('f')
                ->where('f.typefichier <=:type1') //Mémoire, annexe, résumé, Diaporama national
                ->andWhere('f.publie = TRUE')
                ->setParameters(['type1' => 3])
                ->leftJoin('f.equipepassee', 'eq')
                ->andWhere('eq.selectionnee = TRUE')
                ->getQuery()->getResult();

            $images = $imagesRepo->findAll();
            $photosFind = [];
            $filesFind = [];
            $i = 0;
            foreach ($images as $image) {
                foreach ($items as $item) {
                    if (str_contains($image->getPhoto(), $item)) {
                        $photosFind[$i] = $image;
                    }
                }
                $i++;
            }
            $i = 0;
            try {
                foreach ($fichiers as $fichier) {

                    $pathFile = 'odpf/odpf-archives/' . $fichier->getEquipepassee()->getEditionspassees()->getEdition() . '/fichiers/' . $this->getParameter('type_fichier')[$fichier->getTypefichier() == 1 ? $typefichier = 0 : $typefichier = $fichier->getTypefichier()] . '/publie/' . $fichier->getNomfichier();
                    if (file_exists($pathFile)) {
                        try {
                            set_time_limit(300);
                            $textefile = $parser->parseContent(file_get_contents($pathFile))->getText();
                            dd($textefile);
                            /* if (strlen($textefile) > 1000) {
                                 $textefile = str_split($textefile, 1000)[0];
                             }*/
                            $match = false;
                            foreach ($items as $item) {
                                if (str_contains($textefile, $item) or str_contains($fichier->getNomfichier(), $item)) {

                                    $nb = substr_count($textefile, $item);
                                    dump($i . ':' . $nb);
                                    $filesFind[$i] = $fichier;
                                    $match = true;
                                }
                            }
                            if ($match == true) {
                                $i++;
                            }

                        } catch (\Exception $e) {

                        }

                    }

                }
            } catch (\Exception $e) {

            }
            dd($filesFind);
        } else {


            return $this->redirectToRoute('core_home');

        }

    }
}
