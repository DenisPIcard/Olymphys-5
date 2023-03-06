<?php

namespace App\Controller;

use App\Entity\Edition;
use App\Entity\Elevesinter;
use App\Entity\Equipesadmin;
use App\Entity\Uai;
use App\Entity\User;
use App\Form\InscrireEquipeType;
use App\Form\ModifEquipeType;
use App\Form\ProfileType;
use App\Service\Mailer;
use App\Service\Maj_profsequipes;
use App\Service\OdpfRempliEquipesPassees;
use datetime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;


class UtilisateurController extends AbstractController
{
    private RequestStack $requestStack;
    private ManagerRegistry $doctrine;

    public function __construct(RequestStack $requestStack, ManagerRegistry $doctrine)
    {
        $this->requestStack = $requestStack;
        $this->doctrine = $doctrine;
    }

    #[Route("/profile_show", name:"profile_show")]
    public function profileShow(): Response
    {
        $user = $this->getUser();
        return $this->render('profile/show.html.twig', array(
            'user' => $user,
        ));
    }

    #[Route("profile_edit", name:"profile_edit")]
    public function profileEdit(Request $request, ManagerRegistry $doctrine)
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->setData($user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $nom = $form->get('nom')->getData();
            $nom = strtoupper($nom);
            $user->setNom($nom);
            $prenom = $form->get('prenom')->getData();
            $prenom = ucfirst(strtolower($prenom));
            $user->setPrenom($prenom);
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('core_home');
        }
        return $this->render('profile/edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    #[Route("/Utilisateur/inscrire_equipe,{idequipe}", name:"inscrire_equipe")]
    public function inscrire_equipe(Request $request, Mailer $mailer, ManagerRegistry $doctrine, $idequipe)
    {

        if (null != $this->getUser()) {
            $date = new datetime('now');
            $session = $this->requestStack->getSession();
            $user = $this->getUser();
            if (($user->getEmail() == '') or ($user->getPhone() == null) or ($user->getNom() == '') or ($user->getPrenom() == '') or ($user->getAdresse() == '')) {
                $this->requestStack->getSession()->set('message', 'Veuillez saisir toutes les informations dans votre profil. Elles sont nécessaires pour le bon déroulement du concours : pouvoir vous contacter directement en cas d\'information urgente ou  l\'envoi de vos cadeaux, etc...  L\'inscription d\'une équipe n\'est possible que si ce profil est complet.');
                return $this->redirectToRoute('profile_edit');


            }
            if ($idequipe == 'x') {
                if ($date < $session->get('edition')->getDateouverturesite() or ($date > $session->get('edition')->getDateclotureinscription())) {

                    $request->getSession()
                        ->getFlashBag()
                        ->add('info', 'Les inscriptions sont closes. Inscriptions entre le ' . $session->get('edition')->getDateouverturesite()->format('d-m-Y') . ' et le ' . $session->get('edition')->getDateclotureinscription()->format('d-m-Y') . ' 22 heures(heure de Paris)');


                    return $this->redirectToRoute('core_home');


                }
            }

            $em = $doctrine->getManager();
            $repositoryEquipesadmin = $doctrine->getRepository(Equipesadmin::class);
            $repositoryEleves = $doctrine->getRepository(Elevesinter::class);
            $repositoryUai = $doctrine->getRepository(Uai::class);
            $repositoryEdition = $doctrine->getRepository(Edition::class);


            $uai_objet = $repositoryUai->findOneBy(['uai' => $this->getUser()->getUai()]);
            if ($this->isGranted('ROLE_PROF')) {
                $edition = $session->get('edition');
                $idEdition = $edition->getId();
                $edition = $repositoryEdition->findOneBy(['id' => $idEdition]);
                if ($idequipe == 'x') {
                    $equipe = new Equipesadmin();
                    $form1 = $this->createForm(InscrireEquipeType::class, $equipe, ['uai' => $this->getUser()->getUai()]);
                    $modif = false;
                    $eleves = [];
                } else {
                    $equipe = $repositoryEquipesadmin->findOneBy(['id' => intval($idequipe)]);
                    $eleves = $repositoryEleves->findBy(['equipe' => $equipe]);

                    if ((!isset($_REQUEST['modif_equipe'])) and (null === $session->get('supr_eleve'))) {
                        $oldEquipe = $repositoryEquipesadmin->findOneBy(['id' => intval($idequipe)]);
                        $session->set('oldequipe', $oldEquipe);
                        $oldListeEleves = $repositoryEleves->findBy(['equipe' => $equipe]);
                        $session->set('oldlisteEleves', $oldListeEleves);
                    }


                    $eleves_supr = null;
                    if ($session->get('supr_eleve') !== null) { //le professeur efface l'élève sur le formulaire, mais ne le supprime pas encore
                        $eleves_supr = $session->get('supr_eleve');
                        $elevesinit = $repositoryEleves->findBy(['equipe' => $equipe]);

                        $i = 0;
                        foreach ($elevesinit as $eleveinit) {
                            $supr[$eleveinit->getId()] = false;
                            foreach ($eleves_supr as $eleve_supr) {
                                if ($eleveinit->getId() == $eleve_supr->getId()) {

                                    $supr[$eleveinit->getId()] = true;

                                }
                            }
                            if ($supr[$eleveinit->getId()] == false) {
                                $elevesaff[$i] = $eleveinit;

                                $i++;
                            }

                        }


                    }
                    if ($session->get('supr_eleve') == null) {
                        $elevesaff = $repositoryEleves->findBy(['equipe' => $equipe]);
                    }
                    $form1 = $this->createForm(ModifEquipeType::class, $equipe, ['uai' => $this->getUser()->getUai(), 'eleves' => $elevesaff]);
                    $modif = true;
                }

                $form1->handleRequest($request);
                if ($form1->isSubmitted() && $form1->isValid()) {

                    $oldEquipe = $session->get('oldequipe');
                    $oldListeEleves = $session->get('oldlisteEleves');

                    $repositoryUai = $em->getRepository(Uai::class);
                    $repositoryEleves = $em->getRepository(Elevesinter::class);
                    if ($session->get('supr_eleve') !== null) {
                        $eleves_supr = $session->get('supr_eleve');
                        foreach ($eleves_supr as $eleve_supr) {
                            $eleves = $repositoryEleves->findBy(['equipe' => $equipe]);
                            if (count($eleves) > 2) {
                                $eleveid = $eleve_supr->getId();
                                $this->supr_eleve($eleveid);


                            } elseif (count($eleves) == 2) {
                                $request->getSession()
                                    ->getFlashBag()
                                    ->add('alert', 'Une équipe ne peut pas avoir moins de deux élèves');
                                break;

                            }
                        }


                    }

                    if ($modif == false) {
                        $e = null;
                        try {
                            $lastEquipe = $repositoryEquipesadmin->createQueryBuilder('e')
                                ->select('e, MAX(e.numero) AS max_numero')
                                ->andWhere('e.edition = :edition')
                                ->setParameter('edition', $edition)
                                ->getQuery()->getSingleResult();
                        } catch (NoResultException|NonUniqueResultException $e) {
                        }

                        if (($e) and ($modif == false)) {
                            $numero = 1;
                            $equipe->setNumero($numero);
                        } elseif ($modif == false) {
                            $numero = intval($lastEquipe['max_numero']) + 1;
                            $equipe->setNumero($numero);
                        }
                    }
                    $uai_objet = $repositoryUai->findOneBy(['uai' => $this->getUser()->getUai()]);

                    $equipe->setPrenomprof1($form1->get('idProf1')->getData()->getPrenom());
                    $equipe->setNomprof1($form1->get('idProf1')->getData()->getNom());
                    if ($form1->get('idProf2')->getData() != null) {

                        $equipe->setPrenomprof2($form1->get('idProf2')->getData()->getPrenom());
                        $equipe->setNomprof2($form1->get('idProf2')->getData()->getNom());
                    }
                    // voir https://intellij-support.jetbrains.com/hc/en-us/community/posts/360008186620-Expected-parameter-of-type-App-Entity-User-object-provided-
                    /** @var Edition|object|null $edition */
                    $equipe->setEdition($edition);
                    if ($modif == false) {
                        $equipe->setSelectionnee(false);
                    }
                    $equipe->setUai($this->getUser()->getUai());
                    $equipe->setUaiId($uai_objet);
                    $equipe->setDenominationLycee($uai_objet->getDenominationPrincipale());
                    $equipe->setNomLycee($uai_objet->getnom());
                    $equipe->setLyceeAcademie($uai_objet->getAcademie());
                    $equipe->setLyceeLocalite($uai_objet->getCommune());
                    $nbeleves = $equipe->getNbeleves();
                    for ($i = 1; $i < 7; $i++) {
                        if ($form1->get('nomeleve' . $i)->getData() != null) {
                            $id = 0;
                            if ($modif == true) {

                                $id = $form1->get('id' . $i)->getData();
                            }
                            if ($id != 0) {
                                $id = $form1->get('id' . $i)->getData();
                                $eleve[$i] = $repositoryEleves->find(['id' => $form1->get('id' . $i)->getData()]);
                            } else {
                                $eleve[$i] = new Elevesinter();
                                $nbeleves = $nbeleves + 1;
                            }

                            if (($form1->get('prenomeleve' . $i)->getData() == null) or ($form1->get('nomeleve' . $i)->getData() == null) or ($form1->get('maileleve' . $i)->getData() == null) or ($form1->get('classeeleve' . $i)->getData() == null)) {
                                $request->getSession()
                                    ->getFlashBag()
                                    ->add('alert', 'Les données d\'un élève doivent être toutes complétées !');

                                return $this->render('register/inscrire_equipe.html.twig', array('form' => $form1->createView(), 'equipe' => $equipe, 'concours' => $session->get('concours'), 'choix' => 'liste_prof', 'modif' => $modif, 'eleves' => $eleves, 'uaiObj' => $uai_objet));
                            }
                            $eleve[$i]->setPrenom($form1->get('prenomeleve' . $i)->getData());
                            $eleve[$i]->setNom(strtoupper($form1->get('nomeleve' . $i)->getData()));
                            $eleve[$i]->setCourriel($form1->get('maileleve' . $i)->getData());
                            $eleve[$i]->setGenre($form1->get('genreeleve' . $i)->getData());
                            $eleve[$i]->setClasse($form1->get('classeeleve' . $i)->getData());
                            $eleve[$i]->setEquipe($equipe);

                            $em->persist($eleve[$i]);

                        }
                    }
                    $equipe->setNbEleves($nbeleves);
                    $em->persist($equipe);
                    $em->flush();
                    $checkChange = '';
                    if ($modif == true) {

                        $checkChange = $this->compare($equipe, $oldEquipe, $oldListeEleves);
                    }

                    $maj_profsequipes = new Maj_profsequipes($doctrine);
                    $maj_profsequipes->maj_profsequipes($equipe);
                    $rempliOdpfEquipesPassees = new OdpfRempliEquipesPassees($doctrine);
                    $rempliOdpfEquipesPassees->OdpfRempliEquipePassee($equipe);

                    $session->set('oldListeEleves', null);
                    $session->set('supr_eleve', null);

                    if ($modif == false) {
                        $mailer->sendConfirmeInscriptionEquipe($equipe, $this->getUser(), $modif, $checkChange);

                        return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-' . $session->get('concours') . '-liste_equipe'));
                    }
                    if (($modif == true) and ($checkChange != [])) {
                        try {
                            $mailer->sendConfirmeInscriptionEquipe($equipe, $this->getUser(), $modif, $checkChange);
                        }
                        catch(TransportExceptionInterface $e) {
                                dd($e);
                        }


                        return $this->redirectToRoute('fichiers_afficher_liste_fichiers_prof', array('infos' => $equipe->getId() . '-' . $session->get('concours') . '-liste_prof'));
                    }
                }
                return $this->render('register/inscrire_equipe.html.twig', array('form' => $form1->createView(), 'equipe' => $equipe, 'concours' => $session->get('concours'), 'choix' => 'liste_prof', 'modif' => $modif, 'eleves' => $eleves, 'uaiObj' => $uai_objet));

            } else {
                return $this->redirectToRoute('core_home');
            }
        } else {

            return $this->redirectToRoute('login');

        }


    }

    #[IsGranted('ROLE_PROF')]
    #[Route("/Utilisateur/supr_eleve,{eleve}", name:"supr_eleve")]
    public function supr_eleve($eleveId)
    {

        $em = $this->doctrine->getManager();
        $repositoryEleves = $em->getRepository(Elevesinter::class);

        $eleve = $repositoryEleves->find($eleveId);

        $equipe = $eleve->getEquipe();
        $eleves = $repositoryEleves->createQueryBuilder('e')
            ->where('e.equipe =:equipe')
            ->setParameter('equipe', $equipe)
            ->getQuery()->getResult();
        if (count($eleves) > 2) {

            if ($eleve->getAutorisationphotos() != null) {
                $autorisation = $eleve->getAutorisationphotos();
                $file = $autorisation->getFichier();
                copy('fichiers/autorisations/' . $file, 'fichiers/autorisations/trash/' . $file);// dans le cas où l'élève d'ésinscrit a participé aux cia avec une autroisation photo mais ne participe plus au cn

                $eleve->setAutorisationphotos(null);
                $em->remove($autorisation);
                $em->flush();

            }
            $equipe = $eleve->getEquipe();
            $equipe->setNbeleves($equipe->getNbeleves() - 1);
            $em->persist($equipe);
            $em->remove($eleve);
            $em->flush();
        }


        //return  $this->redirectToRoute('inscrire_equipe', array('idequipe'=>$equipe->getId()));


    }

    public function compare($equipe, $oldEquipe, $oldListeEleves): array
    {
        $session = $this->requestStack->getSession();
        $checkchange = [];
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $oldnom = $oldEquipe->getTitreprojet();
        $nom = $equipe->getTitreprojet();
        if ($nom != $oldnom) {
            $checkchange['nom'] = 'le nom de l\'équipe';
        }
        $oldprof1 = $oldEquipe->getIdProf1();
        $prof1 = $equipe->getIdProf1();
        if ($prof1->getId() != $oldprof1->getId()) {
            $checkchange['prof1'] = 'prof1';
        }
        $oldprof2 = $oldEquipe->getIdProf2();
        $prof2 = $equipe->getIdProf2();
        if ((null !== $prof2) and (null !== $oldprof2)) {
            if ($prof2->getId() != $oldprof2->getId()) {
                $checkchange['prof2'] = 'prof2';
            }
        }
        if ((null !== $prof2) and (null === $oldprof2)) {
            $checkchange['prof2'] = 'Ajout  du prof2';

        }
        if ((null === $prof2) and (null !== $oldprof2)) {
            $checkchange['prof2'] = 'Suppression du prof2';

        }
        $oldcontribfin = $oldEquipe->getContribfinance();
        $contribfin = $equipe->getContribfinance();
        if ($contribfin != $oldcontribfin) {
            $checkchange['contribfin'] = 'Contribution financière';
        }
        $oldOrigine = $oldEquipe->getOrigineprojet();
        $origine = $equipe->getOrigineprojet();
        if ($origine != $oldOrigine) {
            $checkchange['origine'] = 'Origine du projet';
        }
        $oldPartenaire = $oldEquipe->getPartenaire();
        $partenaire = $equipe->getPartenaire();
        if ($partenaire != $oldPartenaire) {
            $checkchange['partenaire'] = 'Partenaire';
        }
        $oldInscrite=$oldEquipe->getInscrite();
        $inscrite=$equipe->getInscrite();

        if ($inscrite != $oldInscrite) {
            $inscrite==false?$checkchange['inscrite'] = 'NON':$checkchange['inscrite'] = 'OUI';

        }
        $repositoryEleves = $this->doctrine->getRepository(Elevesinter::class);
        $listeEleves = $repositoryEleves->findByEquipe(['equipe' => $equipe]);

        $checkEleves = [];
        $checkOldEleves = [];
        foreach ($listeEleves as $eleve) {
            $checkEleves[$eleve->getId()] = $eleve->getId();
            foreach ($oldListeEleves as $oldEleve) {

                $checkOldEleves[$oldEleve->getId()] = $oldEleve->getId();
                if ($oldEleve->getId() == $eleve->getId()) {

                    if (($oldEleve->getNom() != $eleve->getNom()) or (($oldEleve->getPrenom()) != $eleve->getPrenom()) or ($oldEleve->getCourriel() != $eleve->getCourriel()) or ($oldEleve->getClasse() != $eleve->getClasse())) {
                        $checkchange['eleves' . $eleve->getId()] = 'Mofidification de l\'élève : ' . $eleve->getNom();
                    }
                }
            }
        }

        if (count($listeEleves) != count($oldListeEleves)) {
            $elevesdif = [];
            if (count($listeEleves) > count($oldListeEleves)) {
                foreach ($checkEleves as $checkEleve) {

                    if (in_array($checkEleve, $checkOldEleves, false) == false) {
                        $elevesdif[$checkEleve] = $checkEleve;
                    }
                }

                $message = '';
                foreach ($elevesdif as $elevedif) {
                    $eleve = $repositoryEleves->find($elevedif);

                    $message .= $eleve->getNom() . ' ' . $eleve->getPrenom() . ', ';
                }
                $checkchange['Eleve(s) inscrit(e-s)'] = 'Eleve(s) ajouté(e-s) : ' . $message;

            }

            if (count($listeEleves) < count($oldListeEleves)) {
                $listEleveSupr = $session->get('supr_eleve');
                $message = '';
                foreach ($listEleveSupr as $eleve) {

                    $message .= $eleve->getNom() . ' ' . $eleve->getPrenom() . ', ';
                }
                $checkchange['Eleve(s) désinscrit(e-s)'] = 'Eleve(s) désinscrit(e-s) : ' . $message;
            }
            $session->set('supr_eleve', null);
            $session->set('oldListeEleves', null);
        }

        return $checkchange;
    }

    #[IsGranted('ROLE_PROF')]
    #[Route("/Utilisateur/pre_supr_eleve", name:"pre_supr_eleve")]
    public function pre_supr_eleve(Request $request, ManagerRegistry $doctrine): RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $em = $doctrine->getManager();
        $repositoryEleves = $em->getRepository(Elevesinter::class);
        $ideleve = $request->get('myModalID');
        $eleve = $repositoryEleves->findOneBy(['id' => intval($ideleve)]);
        $listeEleveSupr = $session->get('supr_eleve');
        $listeEleveSupr[$eleve->getId()] = $eleve;
        $session->set('supr_eleve', $listeEleveSupr);
        $equipe = $eleve->getEquipe();
        return $this->redirectToRoute('inscrire_equipe', array('idequipe' => $equipe->getId()));

    }



}