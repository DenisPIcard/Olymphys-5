<?php

namespace App\Controller;

use App\Controller\OdpfAdmin\OdpfDashboardController;
use App\Controller\OdpfAdmin\OdpfFichierIndexCrudController;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfFichierIndex;
use App\Entity\Odpf\OdpfFichierspasses;
use App\Entity\Photos;
use App\Service\Mailer;
use Doctrine\DBAL\Types\TextType;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use mysql_xdevapi\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\fileExists;

class SearchController extends AbstractController
{

    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
        $this->adminUrlGenerator = $adminUrlGenerator;

    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request): Response  //d'après https://programmatek.com/build-php-search-engine/
    {

        $imagesRepo = $this->doctrine->getRepository(Photos::class);
        $indexRepo = $this->doctrine->getRepository(OdpfFichierIndex::class);
        $textSearch = $request->query->get('search-text');
        $config = new Config();//config du parser
        $config->setFontSpaceLimit(-60);//Si le parser crée trop d'espaces.
        $parser = new Parser([], $config);
        $modeSearch = 'or';//Par defaut le mode de recherche est "ou"

        $items = [];
        if ($textSearch != '') {
            if (str_contains($textSearch, '+')) {//Pour les recherches le + signifie  "et"
                $items = explode('+', $textSearch);
                $modeSearch = 'and';
            }
            if (str_contains($textSearch, ' ')) {//Pour les recherches le " "  signifie  "or"
                $items = explode(' ', $textSearch);
                $modeSearch = 'or';
            }
            /*$fichiers = $fichiersRepo->createQueryBuilder('f')
                ->where('f.typefichier <=:type1') //Mémoire, annexe, résumé, Diaporama national
                ->andWhere('f.publie = TRUE')
                ->setParameters(['type1' => 3])
                ->leftJoin('f.equipepassee', 'eq')
                ->andWhere('eq.selectionnee = TRUE')
                ->getQuery()->getResult();*/

            $images = $imagesRepo->findAll();
            $photosFind = [];
            $i = 0;
            foreach ($images as $image) {
                foreach ($items as $item) {
                    if (str_contains($image->getPhoto(), $item)) {
                        $photosFind[$i] = $image;
                    }
                }
                $i++;
            }

            $fichiers = [];
            try {
                $i = 0;
                $qb = $indexRepo->createQueryBuilder('i');
                if ($modeSearch == 'and') {
                    foreach ($items as $item) {
                        $qb->andWhere('i.motClef =:item' . $i)
                            ->setParameter('item' . $i, $item);
                        $i++;
                    }
                }
                if ($modeSearch == 'or') {
                    foreach ($items as $item) {
                        $qb->orWhere('i.motClef =:item' . $i)
                            ->setParameter('item' . $i, $item);
                        $i++;
                    }
                }
                $indexesFind = $qb->getQuery()->getResult();

                if ($indexesFind !== null) {
                    $j = 0;
                    foreach ($indexesFind as $indexFind) {
                        $fichiers[$j] = $indexFind->getFichiers();
                        $j++;
                    }
                }
                dd($fichiers);

            } catch (\Exception $e) {

            }
            return $this->render('search/searchResult.html.twig', ['fichiers' => $fichiers, 'searchText' => $textSearch]);
        } else {


            return $this->redirectToRoute('core_home');

        }

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/initialize_indexes,{idFichier}', name: 'initialize_indexes')]
    public function initializeIndex($idFichier)
    {//Pour créer l'indexation d'un nouveau fichier
        $fichier = $this->doctrine->getRepository(OdpfFichierspasses::class)->find($idFichier);
        $pathFile = 'odpf/odpf-archives/' . $fichier->getEquipepassee()->getEditionspassees()->getEdition() . '/fichiers/' . $this->getParameter('type_fichier')[$fichier->getTypefichier() == 1 ? $typefichier = 0 : $typefichier = $fichier->getTypefichier()] . '/publie/' . $fichier->getNomfichier();
        $config = new Config();//config du parser
        $config->setFontSpaceLimit(-60);//Si le parser crée trop d'espaces.
        $parser = new Parser([], $config);
        $indexes = $this->doctrine->getRepository(OdpfFichierIndex::class)->findAll();
        if (file_exists($pathFile)) {
            try {
                set_time_limit(300);
                $textefile = $parser->parseContent(file_get_contents($pathFile))->getText();
                foreach ($indexes as $index) {
                    if (!$index->getFichiers()->contains($fichier))
                        if (str_contains($textefile, $index->getMotClef()) or str_contains($fichier->getNomfichier(), $index->getMotClef())) {

                            $nb = substr_count($textefile, $index->getMotClef());
                            $index->addFichier($fichier);
                            $this->doctrine->getManager()->persist($index);
                            $this->doctrine->getManager()->flush();
                        }
                }


            } catch (\Exception $e) {
                $this->requestStack->getSession()->set('info', 'L\'indexation du fichier a échoué' . $e);
            }

        }
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/chargeMotsClefs', name: 'charge_mots_clefs')]
    public function chargeMotsClefs(Request $request)//Charge les mots clefs à partir d'un fichier excel une colonne mots_clefs
    {

        $form = $this->createFormBuilder()
            ->add('fichier', FileType::class)
            ->add('valider', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        $indexes = $this->doctrine->getRepository(OdpfFichierIndex::class)->findAll();
        if ($form->isSubmitted() and $form->isValid()) {
            $file = $form->get('fichier')->getData();
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $spreadsheet->getActiveSheet()->getHighestRow();

            for ($ligne = 0; $ligne < $highestRow; $ligne++) {
                $item = $sheet->getCell('A' . $ligne)->getValue();
                foreach ($indexes as $index) {
                    $flag = false;
                    if ($index->getMotClef() == $item) {
                        $flag = true;
                    }
                }
                if ($flag == false) {

                    $newIndex = new OdpfFichierIndex();
                    $newIndex->setMotClef($item);
                    $this->doctrine->getManager()->persist($newIndex);
                    $this->doctrine->getManager()->flush();


                }

            }
            $url = $this->adminUrlGenerator
                ->setDashboard(OdpfDashboardController::class)
                ->setController(OdpfFichierIndexCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
            return $this->redirect($url);
        }
        return $this->render('search/inputFileIndex.html.twig', ['form' => $form->createView()]);

    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/indexationFichiers,{idEdition}', name: 'indexation_fichiers')]
    public function indexationFichiers(Request $request, $idEdition)
    {//Indexation des fichiers d'une édition passée
        $edition = $this->doctrine->getRepository(OdpfEditionsPassees::class)->find($idEdition);
        $fichiers = $this->doctrine->getRepository(OdpfFichierspasses::class)->createQueryBuilder('f')
            ->where('f.editionspassees =:edition')
            ->andWhere('f.publie = TRUE')
            ->andWhere('f.typefichier <=:type')
            ->setParameters(['edition' => $edition, 'type' => 3])
            ->getQuery()->getResult();


    }

}
