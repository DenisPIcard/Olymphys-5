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
     * @throws Exception
     */
    #[Route("/", name: "core_home")]
    public function accueil(ManagerRegistry $doctrine): RedirectResponse|Response
    {

        $user = $this->getUser();

        $repository = $doctrine->getRepository(Edition::class);
        $edition = $repository->findOneBy([], ['id' => 'desc']);
        $editionN1 = $this->doctrine->getRepository(Edition::class)->findOneBy(['ed' => $edition->getEd() - 1]);
        $this->requestStack->getSession()->set('edition', $edition);
        $this->requestStack->getSession()->set('editionN1', $editionN1);
        if (null != $user) {
            $datecia = $edition->getConcourscia();
            $dateconnect = new datetime('now');
            $concours = '';
            if ($dateconnect > $datecia) {
                $concours = 'national';
            } else {
                $concours = 'interacadémique';
            }
            $this->requestStack->getSession()->set('concours', $concours);
        }
        $this->requestStack->getSession()->set('pageCourante', 1);//actus
        $this->requestStack->getSession()->set('pageFCourante', 1);//FAQ
        //pour construire les paginateurs des Actus et de la FAQ
        $repo = $doctrine->getRepository(OdpfArticle::class);
        $tab = $repo->accueil_actus();
        //Construit le tableau des données à transmettre au template : les actus de l'acceil
        $article = $repo->findOneBy(['choix' => 'accueil']);
        $tab['article'] = $article;
        // ajoute les articles destinés à l'accueil

        $listfaq = $repo->listfaq();
        $tab['listfaq'] = $listfaq;
        // ajoute la liste de la Foire aux questions
        // le tout est transmis au template
        if ($_SERVER['SERVER_NAME'] == 'olympessais.olymphys.fr') {
            $this->requestStack->getSession()->set('info', 'Vous êtes sur le site d\'essais d\'olymphys, utilisé pour le 
            développement du site, mais dont le contenu n\'est pas à jour.
            Il faut se connecter sur  olymphys.fr pour réaliser des actions pérennes ');
        }
        return $this->render('core/odpf-accueil.html.twig', $tab);

    }

    #[Route("/core/pages,{choix}", name: "core_pages")]
    public function pages(Request $request, $choix, ManagerRegistry $doctrine, OdpfCreateArray $OdpfCreateArray, OdpfListeEquipes $OdpfListeEquipes): Response
    {
        // construit les pages
        // try catch sert à rediriger les internautes vers le départ si la session s'est interrompue
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
        if ($choix == 'les_equipes') { // dirige vers le traitement de chaque choix
            $tab = $OdpfListeEquipes->getArray($choix);//Le service construit la liste
            $tab['listfaq'] = $listfaq;
        } elseif ($choix == 'mecenes' or $choix == 'donateurs') {
            $repo1 = $doctrine->getRepository(OdpfLogos::class);
            $tab = $repo1->logospartenaires($choix);// la fonction est dans le repository
            $repo2 = $doctrine->getRepository(OdpfPartenaires::class);
            $tab['partenaires'] = $repo2->textespartenaires();//la fonction est dans le repository
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
            $tab = $OdpfCreateArray->getArray($choix);// construit le tableau de résultat à afficher par le template
            $tab['edition_affichee'] = $editionaffichee;
            $tab['editions'] = $editions;
            $tab['choice'] = $choice;
            $tab['listfaq'] = $listfaq;
            $tab['photostest'] = $photostest;
            return $this->render('core/odpf-pages-editions.html.twig', $tab);

        } else {
            $tab = $OdpfCreateArray->getArray($choix);
            $tab['listfaq'] = $listfaq;
        }

        return $this->render('core/odpf-pages.html.twig', $tab);
    }

    #[Route("/core/actus,{tourn}", name: "core_actus")]
    public function odpf_actus(Request $request, $tourn, ManagerRegistry $doctrine): Response
    {
        // construit le tableau des éléments à passer au template pour créer le menu des actus
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

        // construit la liste des éléments à afficher, et à avoir, pour le menu Actus
        $repo = $doctrine->getRepository(OdpfArticle::class);

        $categorie = $this->doctrine->getRepository(OdpfCategorie::class)->findOneBy(['categorie' => 'Les actus']);
        $tab = $repo->actuspaginees();
        $listfaq = $repo->listfaq();
        $tab['listfaq'] = $listfaq;
        $nbpages = $tab['nbpages'];
        $pageCourante = $this->requestStack->getSession()->get('pageCourante');
        // paginateur
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
        $tab['categorie'] = $categorie;
        $tab['pageCourante'] = $pageCourante;
        $this->requestStack->getSession()->set('pageCourante', $pageCourante);

        $actutil = $tab['affActus'];

        $affActus = $actutil[$pageCourante - 1];

        $tab['affActus'] = $affActus;

        return $this->render('core/odpf-pages.html.twig', $tab);
    }

    #[Route("/core/faq,{tourn}", name: "core_faq")]
    public function faq(Request $request, $tourn, ManagerRegistry $doctrine): Response
    {
        // construit le tableau des éléments à passer au template pour créer la page des FAQ
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
        // construit la liste des éléments à afficher, et à avoir, pour le meu FAQ
        $listfaq = $repo->listfaq();
        $tab['listfaq'] = $listfaq;
        $nbpages = $faq['nbpages'];
        $tab['nbpages'] = $nbpages;
        $tab['edition'] = $edition;
        $tab['choix'] = 'faq';
        $tab['titre'] = $faq['titre'];
        $pageFCourante = $this->requestStack->getSession()->get('pageFCourante');
        // paginateur identique à celui des actus. La différence est dans le FCourante
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

    #[Route("/core/mentions,{mention}", name: "core_mentions")]
    public function mentions(Request $request, ManagerRegistry $doctrine, $mention): Response
    {
        // construit le tableau des éléments à passer au template pour créer le menu des mentions de bas de page
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

        return $this->render('core/odpf-pages.html.twig', $tab);
    }

}
