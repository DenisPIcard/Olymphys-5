<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Livredor;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Cell;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LivredorController extends AbstractController
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }


    /**
     * @IsGranted("ROLE_PROF")
     * @Route("/livredor/choix_equipe", name="livredor_choix_equipe")
     * @return RedirectResponse|Response
     */
    public function choix_equipe(Request $request, RequestStack $requestStack)
    {

        $idprof = $this->getUser()->getId();
        $qb = $this->doctrine
            ->getRepository(Equipesadmin::class)
            ->createQueryBuilder('e')
            ->where('e.edition =:edition')
            ->setParameter('edition', $requestStack->getSession()->get('edition'))
            ->andWhere('e.idProf1 =:prof1  or e.idProf2 =:prof2')
            ->setParameter('prof1', $idprof)
            ->setParameter('prof2', $idprof)
            ->andWhere('e.selectionnee = 1')
            ->addOrderBy('e.lettre', 'ASC');
        $equipes = $qb->getQuery()->getResult();
        if (count($equipes) > 1) {
            $form = $this->createFormBuilder()
                ->add('equipe', EntityType::class,
                    ['class' => Equipesadmin::class,
                        'query_builder' => $qb,
                        'choice_label' => 'getInfoequipenat',
                    ])
                ->add('save', SubmitType::class, ['label' => 'Valider'])
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $equipe = $form->get('equipe')->getData();
                $id = $equipe->getId();
                return $this->redirectToRoute('livredor_saisie_texte', ['id' => 'equipe-' . $id]);
            }
            $content = $this->renderView('livredor\choix_equipe.html.twig', ['form' => $form->createView()]);

            return new Response($content);
        }

        return $this->redirectToRoute('livredor_saisie_texte', ['id' => 'equipe-' . $equipes[0]->getId()]);


    }

    /**
     * @IsGranted("ROLE_PROF")
     * @Route("/livredor/saisie_texte,{id}", name="livredor_saisie_texte")
     * @return RedirectResponse|Response
     */
    public function saisie_texte(Request $request, $id): Response
    {
        $em = $this->doctrine->getManager();
        $editionId = $this->requestStack->getSession()->get('edition')->getId();
        $edition = $this->doctrine->getRepository(Edition::class)->findOneBy(['id' => $editionId]);

        $form = $this->createFormBuilder();
        $user = $this->getUser();
        $ids = explode('-', $id);
        $type = $ids[0];
        $id_ = $ids[1];

        if ($type == 'equipe') {

            $equipe = $this->doctrine
                ->getManager()
                ->getRepository(Equipesadmin::class)->findOneById(['id' => $id_]);

            $livredor = $this->doctrine
                ->getManager()
                ->getRepository(Livredor::class)->findOneByEquipe(['equipe' => $equipe]);
            if ($livredor != null) {
                $texteini = $livredor->getTexte();
            }
            if (!isset($texteini)) {
                $texteini = '';
            }

            $listeEleves = $this->doctrine
                ->getManager()
                ->getRepository(Elevesinter::class)
                ->createQueryBuilder('e')
                ->where('e.equipe =:equipe')
                ->setParameter('equipe', $equipe)
                ->getQuery()->getResult();
            $noms = '';
            foreach ($listeEleves as $eleve) {
                $noms = $noms . $eleve->getPrenom() . ', ';

            }
            $noms = substr($noms, 0, -2);

        }
        if (($type == 'prof') or ($type == 'comite') or ($type == 'jury')) {

            $prof = $this->doctrine
                ->getManager()
                ->getRepository(User::class)->findOneById(['id' => $id_]);
            $livredor = $this->doctrine
                ->getManager()
                ->getRepository(Livredor::class)->findOneByUser(['user' => $prof]);
            if ($livredor != null) {
                $texteini = $livredor->getTexte();

            }
            if (!isset($texteini)) {
                $texteini = '';
            }
        }

        $form->add('texte', TextareaType::class, [
            'label' => 'Texte (1000 char. maxi)',
            'data' => $texteini,
        ])
            ->add('save', SubmitType::class, ['label' => 'Valider']);
        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $texte = $form->get('texte')->getData();
            if (($type == 'equipe')) {
                $livredor = $this->doctrine
                    ->getManager()
                    ->getRepository(Livredor::class)->findOneByEquipe(['equipe' => $equipe]);
                if ($livredor == null) {
                    $livredor = new livredoreleves();
                }
                $livredor->setNom($noms);
                $livredor->setTexte($texte);
                $livredor->setEquipe($equipe);
                $livredor->setEdition($edition);
            }
            if (($type == 'prof') or ($type == 'comite') or ($type == 'jury')) {
                try {
                    $livredor = $this->doctrine->getManager()->getRepository(Livredor::class)
                        ->createQueryBuilder('c')
                        ->Where('c.edition =:edition')
                        ->setParameter('edition', $edition)
                        ->andWhere('c.user =:user')
                        ->setParameter('user', $user)
                        ->getQuery()->getSingleResult();
                } catch (Exception $e) {
                    $livredor = null;
                }
                if ($livredor == null) {
                    $livredor = new livredor();
                }
                $livredor->setNom($user->getPrenom() . ' ' . $user->getNom());
                $livredor->setUser($user);
                $livredor->setTexte($texte);
                $livredor->setEdition($edition);
                $livredor->setCategorie($type);
            }

            $em->persist($livredor);
            $em->flush();
            return $this->redirectToRoute('core_home');
        }

        if ($type == 'equipe') {
            $content = $this->renderView('livredor\saisie_texte.html.twig', ['form' => $form->createView(), 'equipe' => $equipe, 'type' => 'equipe']);
        }
        if (($type == 'prof') or ($type == 'comite') or ($type == 'jury')) {
            $content = $this->renderView('livredor\saisie_texte.html.twig', ['form' => $form->createView(), 'user' => $this->getUser(), 'type' => $type]);
        }
        return new Response($content);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/livredor/choix_edition,{action}", name="livredor_choix_edition")
     * @return RedirectResponse|Response
     */
    public function choix_edition(Request $request, $action): Response
    {
        $repositoryEdition = $this->doctrine
            ->getRepository(OdpfEditionsPassees::class);
        $qb = $repositoryEdition->createQueryBuilder('e')
            ->orderBy('e.ed', 'DESC');
        $repositoryLivredor = $this->doctrine
            ->getManager()
            ->getRepository(Livredor::class);


        $Editions = $qb->getQuery()->getResult();

        foreach ($Editions as $edition) {
            $livredors[$edition->getid()] = $repositoryLivredor->createQueryBuilder('l')
                ->where('l.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->getQuery()->getResult();
        }


        return $this->render('livredor/choix_edition.html.twig', array('editions' => $Editions, 'livredors' => $livredors, 'action' => $action));
    }


    /**
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     * @Route("/livredor/lire,{choix}", name="livredor_lire")
     * @return RedirectResponse|Response
     */
    public function lire(Request $request, $choix): Response
    {
        $type = explode('-', $choix)[1];
        $idedition = explode('-', $choix)[0];
        $edition = $repositoryEditionspassees = $this->doctrine
            ->getRepository(OdpfEditionsPassees::class)->findOneById(['id' => $idedition]);

        $edition == $_SESSION['_sf2_attributes']['edition'] ? $archives = 1 : $archives = 0;


        if ($type == 'eleves') {
            $listetextes = $this->doctrine
                ->getManager()
                ->getRepository(Livredor::class)->CreateQueryBuilder('l')
                ->leftJoin('l.equipe', 'eq')
                ->andWhere('l.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('l.categorie =:categorie')
                ->setParameter('categorie', 'equipe')
                ->addOrderBy('eq.lettre', 'ASC')
                ->getQuery()->getResult();

            $content = $this
                ->renderView('livredor\lire.html.twig', ['listetextes' => $listetextes, 'choix' => $type, 'archives' => $archives, 'edition' => $edition]);
        }
        if ($type == 'profs') {
            $listetextes = $this->doctrine
                ->getRepository(Livredor::class)->CreateQueryBuilder('l')
                ->select('l')
                ->andWhere('l.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('l.categorie =:categorie')
                ->setParameter('categorie', 'prof')
                ->leftJoin('l.user', 'u')
                ->addOrderBy('u.nom', 'ASC')
                ->getQuery()->getResult();
            $equipes = $this->doctrine
                ->getManager()
                ->getRepository(OdpfEquipesPassees::class)
                ->createQueryBuilder('e')
                ->Where('e.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('e.selectionnee = 1')
                ->addOrderBy('e.lettre', 'ASC')
                ->getQuery()
                ->getResult();
            $i = 0;

            foreach ($listetextes as $texte) {
                $prof = $texte->getUser();
                $lettres_equipes_prof[$i] = '';
                foreach ($equipes as $equipe) {
                    $nomprofs = explode(', ', $equipe->getProfs());

                    if (strtoupper($nomprofs[0]) == strtoupper($prof->getPrenomNom())) {

                        if (strlen($lettres_equipes_prof[$i]) > 0) {
                            $lettres_equipes_prof[$i] = $lettres_equipes_prof[$i] . ', ' . $equipe->getLettre();

                        }
                        if (strlen($lettres_equipes_prof[$i]) == 0) {
                            $lettres_equipes_prof[$i] = $lettres_equipes_prof[$i] . $equipe->getLettre();

                        }

                    }

                    if (isset($nomprofs[1])) {
                        if (strtoupper($nomprofs[1]) == strtoupper($prof->getPrenomNom())) {
                            if (strlen($lettres_equipes_prof[$i]) > 0) {
                                $lettres_equipes_prof[$i] = $lettres_equipes_prof[$i] . ', ' . $equipe->getLettre();
                            }
                            if (strlen($lettres_equipes_prof[$i]) == 0) {
                                $lettres_equipes_prof[$i] = $lettres_equipes_prof[$i] . $equipe->getLettre();
                            }
                        }
                    }
                }
                $i = $i + 1;
            }

            $content = $this
                ->renderView('livredor\lire.html.twig', ['listetextes' => $listetextes, 'lettres_equipes_prof' => $lettres_equipes_prof, 'choix' => $type, 'archives' => $archives, 'edition' => $edition]);
        }
        if (($type == 'comite') or ($type == 'jury')) {
            $listetextes = $this->doctrine
                ->getRepository(Livredor::class)->CreateQueryBuilder('l')
                ->select('l')
                ->andWhere('l.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('l.categorie =:categorie')
                ->setParameter('categorie', $type)
                ->leftJoin('l.user', 'u')
                ->addOrderBy('u.nom', 'ASC')
                ->getQuery()->getResult();


            $content = $this
                ->renderView('livredor\lire.html.twig', ['listetextes' => $listetextes, 'choix' => $type, 'archives' => $archives, 'edition' => $edition]);
        }
        return new Response($content);

    }

    /**
     * @IsGranted("ROLE_COMITE")
     * @Route("/livredor/editer,{choix}", name="livredor_editer")
     *
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function editer(Request $request, $choix)
    {

        $idedition = explode('-', $choix)[0];
        $type = explode('-', $choix)[1];
        $edition =$this->doctrine
            ->getRepository(OdpfEditionsPassees::class)->findOneById(['id' => $idedition]);

        $phpWord = new  PhpWord();

        $section = $phpWord->addSection();
        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, array('align' => Cell::VALIGN_CENTER, 'spaceAfter' => 100));

        $phpWord->addTitleStyle(1, array('bold' => true, 'size' => 14, 'spaceAfter' => 240));
        $fontTitre = 'styletitre';
        $phpWord->addFontStyle(
            $fontTitre,
            array('name' => 'Tahoma', 'size' => 12, 'color' => '0000FF', 'bold' => true, 'align' => 'center')
        );
        //$fontTitre = new \PhpOffice\PhpWord\Style\Font();
        $fontTexte = 'styletexte';
        $phpWord->addFontStyle(
            $fontTexte,
            array('name' => 'Arial', 'size' => 12, 'color' => '000000')
        );

        if (($type == 'prof') or ($type == 'comite') or ($type == 'jury')) {
            $livredor = $this - $this->doctrine
                    ->getRepository(Livredor::class)->createQueryBuilder('l')
                    ->leftJoin('l.user', 'p')
                    ->addOrderBy('p.nom', 'ASC')
                    ->andWhere('l.categorie =:categorie')
                    ->setParameter('categorie', $type)
                    ->andWhere('l.editionspassees =:edition')
                    ->setParameter('edition', $edition)
                    ->getQuery()->getResult();

            if ($type == 'prof') {
                $equiperepository = $this->doctrine
                    ->getManager()
                    ->getRepository(Equipesadmin::class);
                $section->addText('Livre d\'or des professeurs - Edition ' . $edition->getEd() . ' année ' . $edition->getAnnee(), array('bold' => true, 'size' => 14, 'spaceAfter' => 240), 'pStyle');
                $section->addTextBreak(3);
                if ($livredor != null) {
                    foreach ($livredor as $texte) {
                        $prof = $texte->getUser();

                        $equipes = $equiperepository->getEquipes_prof_cn($prof, $edition);
                        if (count($equipes) > 1) {
                            $titreprof = $prof->getNomPrenom() . '( équipes ';
                        } else {
                            $titreprof = $prof->getNomPrenom() . '( équipe ';
                        }

                        $i = 0;
                        foreach ($equipes as $equipe) {
                            $titreprof = $titreprof . $equipe->getLettre();
                            if ($i < array_key_last($equipes))
                                $titreprof = $titreprof . ', ';
                            $i++;
                        }
                        $titreprof = $titreprof . ' )';
                        $section->addText($titreprof, 'styletitre');
                        $textlines = explode("\n", $texte->getTexte());

                        $textrun = $section->addTextRun();
                        $textrun->addText(array_shift($textlines), 'styletexte');
                        foreach ($textlines as $line) {
                            $textrun->addTextBreak();
                            // maybe twice if you want to seperate the text
                            // $textrun->addTextBreak(2);
                            $textrun->addText($line, null, 'styletexte');
                        }
                        // $section->addText($texte->getTexte(),'styletexte');
                        //$lineStyle = array('weight' => 1, 'width' => 200, 'height' => 0, 'color'=> '0000FF');

                        $section->addTextBreak(3);
                        //$section->addLine($lineStyle);
                        $section->addText('------', null, 'pStyle');
                    }
                }
            }
            if (($type == 'comite') or ($type == 'jury')) {

                $categorie = $type;
                $titrepage = 'Livre d\'or du ' . $categorie . ' - Edition ' . $edition->getEd() . ' année ' . $edition->getAnnee();


                $section->addText($titrepage, array('bold' => true, 'size' => 14, 'spaceAfter' => 240), 'pStyle');
                $section->addTextBreak(3);
                if ($livredor != null) {
                    foreach ($livredor as $texte) {
                        $titre = $texte->getNom();

                        $section->addText($titre, 'styletitre');

                        $textlines = explode("\n", $texte->getTexte());

                        $textrun = $section->addTextRun();
                        $textrun->addText(array_shift($textlines), 'styletexte');
                        foreach ($textlines as $line) {
                            $textrun->addTextBreak();
                            // maybe twice if you want to seperate the text
                            // $textrun->addTextBreak(2);
                            $textrun->addText($line, 'styletexte');
                        }

                        $section->addTextBreak(3);
                        //$section->addLine($lineStyle);
                        $section->addText('------', null, 'pStyle');
                    }

                }
            }
        }
        if ($type == 'equipe') {
            $livredor = $this->doctrine
                ->getRepository(Livredor::class)
                ->createQueryBuilder('e')
                ->where('e.editionspassees =:edition')
                ->setParameter('edition', $edition)
                ->andWhere('e.categorie =:categorie')
                ->setParameter('categorie', $type)
                ->leftJoin('e.equipe', 'eq')
                ->orderBy('eq.lettre', 'ASC')
                ->getQuery()->getResult();

            if ($livredor != null) {
                $section->addText('Livre d\'or des élèves- Edition ' . $edition->getEd() . ' année ' . $edition->getAnnee(), array('bold' => true, 'size' => 14, 'spaceAfter' => 240), 'pStyle');
                $section->addTextBreak(3);
                foreach ($livredor as $texte) {

                    $equipe = $texte->getEquipe();

                    $titreEquipe = 'Equipe ' . $texte->getEquipe()->getInfoequipenat() . ' (' . $texte->getNom() . ')';
                    $titre = $section->addText($titreEquipe);
                    $titre->setFontStyle('styletitre');

                    $textlines = explode("\n", $texte->getTexte());

                    $textrun = $section->addTextRun();
                    $textrun->addText(array_shift($textlines), 'styletexte');
                    foreach ($textlines as $line) {
                        $textrun->addTextBreak();
                        // maybe twice if you want to seperate the text
                        // $textrun->addTextBreak(2);
                        $textrun->addText($line, 'styletexte');
                    }
                    //$lineStyle = array('weight' => 1, 'width' => 200, 'height' => 0, 'color'=> '0000FF');
                    $section->addTextBreak(3);
                    //$section->addLine($lineStyle);
                    $texte = $section->addText('------', null, 'pstyle');
                }
            }
        }
        $categorie = $type;
        $filesystem = new Filesystem();
        $fileName = $edition->getEdition() . ' annee ' . $edition->getAnnee() . ' livre d\'or ' . $categorie . '.docx';

        try {
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
        }
        $objWriter->save($this->getParameter('app.path.tempdirectory') . '/' . $fileName);
        $response = new Response(file_get_contents($this->getParameter('app.path.tempdirectory') . '/' . $fileName));//voir https://stackoverflow.com/questions/20268025/symfony2-create-and-download-zip-file
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Type', 'application/msword');
        $response->headers->set('Content-Disposition', $disposition);
        $filesystem->remove($this->getParameter('app.path.tempdirectory') . '/' . $fileName);
        return $response;


    }


}