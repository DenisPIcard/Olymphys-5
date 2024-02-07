<?php

namespace App\Service;


use App\Entity\Odpf\OdpfArticle;
use App\Entity\Odpf\OdpfCategorie;
use App\Entity\Odpf\OdpfEditionsPassees;
use App\Entity\Odpf\OdpfEquipesPassees;
use Doctrine\ORM\EntityManagerInterface;

class CreatePageEdPassee
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

    }

    public function create(OdpfEditionsPassees $editionsPassees): OdpfArticle
    {
        $repositoryOdpfArticles = $this->em->getRepository(OdpfArticle::class);
        if ($repositoryOdpfArticles->findOneBy(['choix' => 'edition' . $editionsPassees->getEdition()]) == null) {

            $article = new OdpfArticle();
            $texte = '<p>Pour la ' . $editionsPassees->getEdition() . '<sup>e</sup> édition des Olympiades de Physique France, les inscriptions ont été ouvertes du ' . $editionsPassees->getDateinscription() . '.<br>

            Les concours intercadémiques ont eu lieu le ' . $editionsPassees->getDateCia() . '.<br>
            
            Le compte rendu des Concours interacadémiques.<br>
            
           
            <p>Le concours national a eu lieu à ' . $editionsPassees->getLieu() . ' le ' . $editionsPassees->getDateCn() . '
             Le parrain de cette ' . $editionsPassees->getPseudo() . '<sup>e</sup> édition était ' . $editionsPassees->getNomParrain() . ', ' . $editionsPassees->getTitreParrain() . '.<br>
            
            Le palmarès.<br>
            
            Liste des équipes
            <ul>';
        } else {
            $article = $repositoryOdpfArticles->findOneBy(['choix' => 'edition' . $editionsPassees->getEdition()]);
            $texte = $article->getTexte();
            $textes = explode('<p>Liste des équipes</p>', $texte);//&eacute;
            $texte = $textes[0] . 'Liste des équipes';//Permets la mise à jour de la liste des  équipes sans effacer les autres données

        }
        $listeEquipesSel = $this->em->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.editionspassees =:edition')
            ->andWhere('e.selectionnee = TRUE')
            ->setParameters(['edition' => $editionsPassees])
            ->addOrderBy('e.lettre', 'ASC')
            ->getQuery()->getResult();
        $listeEquipesNonsel = $this->em->getRepository(OdpfEquipesPassees::class)->createQueryBuilder('e')
            ->select('e')
            ->andWhere('e.editionspassees =:edition')
            ->andWhere('e.numero <:numero')
            ->andWhere('e.selectionnee = FALSE')
            ->setParameters(['edition' => $editionsPassees, 'numero' => 100])
            ->addOrderBy('e.numero', 'ASC')
            ->getQuery()->getResult();
        $article->setTitre($editionsPassees->getEdition() . 'e edition');
        $article->setChoix('edition' . $editionsPassees->getEdition());
        $ed = $editionsPassees->getEdition();
        $dateCia = $editionsPassees->getDateCia();

        if ($listeEquipesSel !== []) {
            foreach ($listeEquipesSel as $equipe) {
                // sur le site
                //$texte = $texte . '<li><a href="/public/index.php/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $equipe->getLettre() . ' ' . $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';
                // en local
                //$texte = $texte . '<li class="rougeodpf"> <a href="/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $equipe->getLettre() . ' '. $equipe->getTitreProjet() .'</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';
                $repEquipe = $equipe->getLettre();
                if ($_SERVER['SERVER_NAME'] == '127.0.0.1') {
                    $texte = $texte . '<li class="rougeodpf"> <a href="/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $repEquipe . ' ' . $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';

                } else {
                    $texte = $texte . '<li class="rougeodpf"> <a href="/public/index.php/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $repEquipe . ' ' . $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';

                }

            }
            foreach ($listeEquipesNonsel as $equipe) {
                $repEquipe = $equipe->getNumero();
                if ($_SERVER['SERVER_NAME'] == '127.0.0.1') {//en localhost
                    $texte = $texte . '<li class="rougeodpf"> <a href="/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $repEquipe . ' ' . $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';

                } else {//sur le site
                    $texte = $texte . '<li class="rougeodpf"> <a href="/public/index.php/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $repEquipe . ' ' . $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';

                }
            }


            //sur le site
            //$texte = $texte . '<li class="rougeodpf"> <a href="/public/index.php/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' . $equipe->getNumero() . ' ' . $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';
            //en local
            //$texte = $texte . '<li class="rougeodpf"> <a href="/odpf/editionspassees/equipe,' . $equipe->getId() . '" >' .$equipe->getNumero().' '. $equipe->getTitreProjet() . '</a>, lycée ' . $equipe->getLycee() . ', ' . $equipe->getVille() . '</li>';


        }
        $texte = $texte . '</ul>';
        if ($_SERVER['SERVER_NAME'] == '127.0.0.1') {//en localhost
            $lienCarteFrance = '/odpf/odpf-archives/' . $editionsPassees->getEdition() . '/cartes/' . $editionsPassees->getEdition() . '-Carte_France.png';
            $lienCarteMonde = '/odpf/odpf-archives/' . $editionsPassees->getEdition() . '/cartes/' . $editionsPassees->getEdition() . '-Carte_mrance.png';
        } else {//sur le site
            $lienCarteFrance = 'https://www.olymphys.fr/public/odpf/odpf-archives/' . $editionsPassees->getEdition() . '/cartes/' . $editionsPassees->getEdition() . '-Carte_France.png';
            $lienCarteMonde = 'https://www.olymphys.fr/public/odpf/odpf-archives/' . $editionsPassees->getEdition() . '/cartes/' . $editionsPassees->getEdition() . '-Carte_mrance.png';
        }
        $texte = $texte . '<br> Les cartes des équipes : 
                    <p><img src="' . $lienCarteFrance . '" style="width: 800px; height: auto" alt="carte de France">   </p>
                    <p><img src="' . $lienCarteMonde . '" style="width: 800px; height: auto" alt="carte du monde">   </p>';
        $article->setTexte($texte);
        $categorie = $this->em->getRepository(odpfCategorie::class)->findOneBy(['id' => 4]);
        $article->setCategorie($categorie);
        $article->setTitreObjectifs('Retrouvez aussi :');
        return $article;
    }

}