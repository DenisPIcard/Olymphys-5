<?php
// src/Controller/CoreController.php
namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfCategorie;
use App\Entity\Odpf\OdpfLogos;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfPartenaires;
use App\Entity\Photos;
use App\Service\OdpfCreateArray;
use App\Service\OdpfListeEquipes;
use DateInterval;
use datetime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class CoreController extends AbstractController
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;


    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }


    /**
     * @Route("/", name="core_home")
     * @throws Exception
     */
    public function accueil(ManagerRegistry $doctrine): RedirectResponse|Response
    {

        $user = $this->getUser();

        $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
        //dd($edition);
        $this->requestStack->getSession()->set('edition', $edition);

        if (null != $user) {
            $datecia = $edition->getConcourscia();
            $dateconnect = new datetime('now');
            if ($dateconnect > $datecia) {
                $concours = 'national';
            }
            if ($dateconnect <= $datecia) {
                $concours = 'interacadémique';
            }
            $datelimphotoscia = date_create();
            $datelimphotoscn = date_create();
            $datelimdiaporama = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d'));
            $p = new DateInterval('P7D');
            $datelimlivredor = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d'));

            $datelivredor = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d') . '00:00:00');
            $datelimlivredoreleve = new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d') . '18:00:00');
            date_date_set($datelimphotoscia, $edition->getconcourscia()->format('Y'), $edition->getconcourscia()->format('m'), $edition->getconcourscia()->format('d') + 30);
            date_date_set($datelimphotoscn, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') + 30);
            date_date_set($datelivredor, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') - 1);
            date_date_set($datelimdiaporama, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') - 7);
            date_date_set($datelimlivredor, $edition->getconcourscn()->format('Y'), $edition->getconcourscn()->format('m'), $edition->getconcourscn()->format('d') + 8);
            $this->requestStack->getSession()->set('concours', $concours);
            $this->requestStack->getSession()->set('datelimphotoscia', $datelimphotoscia);
            $this->requestStack->getSession()->set('datelimphotoscn', $datelimphotoscn);
            $this->requestStack->getSession()->set('datelivredor', $datelivredor);
            $this->requestStack->getSession()->set('datelimlivredor', $datelimlivredor);
            $this->requestStack->getSession()->set('datelimlivredoreleve', $datelimlivredoreleve);
            $this->requestStack->getSession()->set('datelimdiaporama', $datelimdiaporama);
            $this->requestStack->getSession()->set('dateclotureinscription', new DateTime($this->requestStack->getSession()->get('edition')->getConcourscn()->format('Y-m-d H:i:s')));


        }
        $this->requestStack->getSession()->set('pageCourante', 1);
        $this->requestStack->getSession()->set('pageFCourante', 1);
        $repo = $doctrine->getRepository(OdpfArticle::class);
        $tab = $repo->accueil_actus();
        $listfaq = $repo->listfaq();
        //dd($listfaq);
        $article = $repo->findOneBy(['choix' => 'accueil']);
        $tab['listfaq'] = $listfaq;
        $tab['article'] = $article;
        // dd($tab);
        if ($this->requestStack->getSession()->get('resetpwd') == true) {

            return $this->redirectToRoute('forgotten_password');

        } else {
            return $this->render('core/odpf-accueil.html.twig', $tab);
        }
    }

    /**
     * @Route("/core/pages,{choix}", name="core_pages")
     */
    public function pages(Request $request, $choix, ManagerRegistry $doctrine, OdpfCreateArray $OdpfCreateArray, OdpfListeEquipes $OdpfListeEquipes): Response
    {     /*  if($this->requestStack->getSession()->get('edition') == false){
                $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
                $this->requestStack->getSession()->set('edition', $edition);
            }*/
        try {
            $edition = $this->requestStack->getSession()->get('edition');
            if ($edition === null) {
                $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
                $this->requestStack->getSession()->set('edition', $edition);
                return $this->redirectToRoute('core_home');

            }
        } catch (Exception $e) {
            $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
            return $this->redirectToRoute('core_home');
        }

        $repo = $doctrine->getRepository(OdpfArticle::class);
        $listfaq = $repo->listfaq();
        if ($choix == 'les_equipes') {
            $tab = $OdpfListeEquipes->getArray($choix);
            $tab['listfaq'] = $listfaq;
        } elseif ($choix == 'mecenes' or $choix == 'donateurs') {
            $repo1 = $doctrine->getRepository(OdpfLogos::class);
            $tab = $repo1->logospartenaires($choix);
            $repo2 = $doctrine->getRepository(OdpfPartenaires::class);
            $tab['partenaires'] = $repo2->textespartenaires();
            $tab['listfaq'] = $listfaq;
        } elseif ($choix == 'editions') {
            $editions = $doctrine->getRepository(OdpfEditionsPassees::class)->createQueryBuilder('e')
                ->andWhere('e.edition !=:lim')
                ->addOrderBy('e.edition', 'DESC')
                ->setParameter('lim', $this->requestStack->getSession()->get('edition')->getEd())
                ->getQuery()->getResult();
            $editionaffichee = $doctrine->getRepository(OdpfEditionsPassees::class)->findOneBy(['edition' => $this->requestStack->getSession()->get('edition')->getEd() - 1]);//C'est l'édition précédente qui est affichée
            $choice = 'editions';
            $choix = 'edition' . $doctrine->getRepository(OdpfEditionsPassees::class)
                    ->findOneBy(['edition' => $editionaffichee->getEdition()])->getEdition();
            $photosed = $this->doctrine->getRepository(photos::class)->findBy(['editionspassees' => $editionaffichee]);
            count($photosed) != 0 ? $photostest = true : $photostest = false;
            $tab = $OdpfCreateArray->getArray($choix);
            $tab['edition_affichee'] = $editionaffichee;
            $tab['editions'] = $editions;
            $tab['choice'] = $choice;
            $tab['listfaq'] = $listfaq;
            $tab['photostest'] = $photostest;
            // dd($tab);
            return $this->render('core/odpf-pages-editions.html.twig', $tab);

        } else {
            $tab = $OdpfCreateArray->getArray($choix);
            $tab['listfaq'] = $listfaq;
        }

        return $this->render('core/odpf-pages.html.twig', $tab);
    }

    /**
     * @Route("/core/actus,{tourn}", name="core_actus")
     */
    public function odpf_actus(Request $request, $tourn, ManagerRegistry $doctrine): Response
    {
        try {
            $edition = $this->requestStack->getSession()->get('edition');
            if ($edition === null) {
                $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
                $this->requestStack->getSession()->set('edition', $edition);
                return $this->redirectToRoute('core_home');

            }
        } catch (Exception $e) {
            $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
        }
        if ($this->requestStack->getSession()->get('edition') == false) {
            $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
        }
        $repo = $doctrine->getRepository(OdpfArticle::class);
        $categorie = $this->doctrine->getRepository(OdpfCategorie::class)->findOneBy(['categorie' => 'Les actus']);
        $tab = $repo->actuspaginees();
        $listfaq = $repo->listfaq();
        $tab['listfaq'] = $listfaq;
        $tab['categorie'] = $categorie;
        $nbpages = $tab['nbpages'];
        $pageCourante = $this->requestStack->getSession()->get('pageCourante');

        switch ($tourn) {
            case 'debut':
                $pageCourante = 1;
                break;
            case 'prec':
                $pageCourante = $pageCourante - 1;
                break;
            case 'suiv'  :
                $pageCourante += 1;
                break;
            case 'fin' :
                $pageCourante = $nbpages;
                break;

        }

        $tab['pageCourante'] = $pageCourante;
        $this->requestStack->getSession()->set('pageCourante', $pageCourante);

        $actutil = $tab['affActus'];

        $affActus = $actutil[$pageCourante - 1];

        $tab['affActus'] = $affActus;
        //dd($tab);

        return $this->render('core/odpf-pages.html.twig', $tab);
    }

    /**
     * @Route("/core/faq,{tourn}", name="core_faq")
     */
    public function faq(Request $request, $tourn, ManagerRegistry $doctrine): Response
    {
        try {
            $edition = $this->requestStack->getSession()->get('edition');
            if ($edition === null) {
                $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
                $this->requestStack->getSession()->set('edition', $edition);
                return $this->redirectToRoute('core_home');

            }
        } catch (Exception $e) {
            $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
        }
        $repo = $doctrine->getRepository(OdpfArticle::class);
        $categorie = $this->doctrine->getRepository(OdpfCategorie::class)->findOneBy(['categorie' => 'faq']);
        $faq = $repo->faq_paginee();
        $listfaq = $repo->listfaq();
        $tab['listfaq'] = $listfaq;
        $nbpages = $faq['nbpages'];
        $tab['nbpages'] = $nbpages;
        $tab['edition'] = $edition;
        $tab['choix'] = 'faq';
        $tab['titre'] = $faq['titre'];
        $pageFCourante = $this->requestStack->getSession()->get('pageFCourante');

        switch ($tourn) {
            case 'debut':
                $pageFCourante = 1;
                break;
            case 'prec':
                $pageFCourante = $pageFCourante - 1;
                break;
            case 'suiv'  :
                $pageFCourante += 1;
                break;
            case 'fin' :
                $pageFCourante = $nbpages;
                break;

        }
        $tab['categorie'] = $categorie;
        $tab['pageFCourante'] = $pageFCourante;
        $this->requestStack->getSession()->set('pageFCourante', $pageFCourante);
        $faqutil = $faq['afffaq'];

        $afffaq = $faqutil[$pageFCourante - 1];

        $tab['afffaq'] = $afffaq;
        //dd($tab);
        return $this->render('core/odpf-pages.html.twig', $tab);
    }

    /**
     * @Route("/core/mentions,{mention}", name="core_mentions")
     */
    public function mentions(Request $request, ManagerRegistry $doctrine, $mention): Response
    {
        try {
            $edition = $this->requestStack->getSession()->get('edition');
            if ($edition === null) {
                $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
                $this->requestStack->getSession()->set('edition', $edition);
                return $this->redirectToRoute('core_home');

            }
        } catch (Exception $e) {
            $edition = $doctrine->getRepository(Edition::class)->findOneBy([], ['id' => 'desc']);
            $this->requestStack->getSession()->set('edition', $edition);
        }
        $repo = $doctrine->getRepository(OdpfArticle::class);
        $listfaq = $repo->listfaq();
        $tab['listfaq'] = $listfaq;
        $article = null;
        switch ($mention) {
            case 'legales' :
                $article = $repo->findOneBy(['titre' => 'Mentions Légales']);
                break;
            case 'remerciements' :
                $article = $repo->findOneBy(['titre' => 'Remerciements']);
                break;
            case 'credits':
                $article = $repo->findOneBy(['titre' => 'Crédits']);
                break;
        }
        $categorie = $this->doctrine->getRepository(OdpfCategorie::class)->findOneBy(['categorie' => 'mentions']);
        $tab['choix'] = 'mentions';
        $tab['categorie'] = $categorie;
        $tab['edition'] = $edition;
        $tab['article'] = $article;
        $tab['titre'] = 'mentions';
        //dd($tab);
        return $this->render('core/odpf-pages.html.twig', $tab);
    }

}
