<?php

namespace App\Service;

class FichierNamer
{
    public function FichierName($equipe, $typefichier, $fichier)
    {
        $edition = $fichier->getEquipe()->getEdition()->getEd();
        $equipe = $fichier->getEquipe();

        if ($equipe) {
            $lettre = $equipe->getLettre();
            $libel_equipe = $lettre;
            if ($fichier->getNational() == 0) {

                $libel_equipe = $equipe->getNumero();
            }
            $nom_equipe = $equipe->getTitreProjet();
            $nom_equipe = $fichier->code($nom_equipe);

            //$nom_equipe= str_replace("'","",$nom_equipe);
            //$nom_equipe= str_replace("`","",$nom_equipe);

            //$nom_equipe= str_replace("?","",$nom_equipe);
        } else {
            $libel_equipe = 'prof';

        }
        if ($fichier->getTypefichier() == 0) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-memoire-' . $nom_equipe;
        }
        if ($fichier->getTypefichier() == 1) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Annexe';
        }
        if ($fichier->getTypefichier() == 2) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Resume-' . $nom_equipe;

        }
        if ($fichier->getTypefichier() == 4) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Fichesecur-' . $nom_equipe;
        }

        if ($fichier->getTypefichier() == 3) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-Presentation-' . $nom_equipe;
        }

        if ($fichier->getTypefichier() == 5) {
            $fileName = $edition . '-eq-' . $libel_equipe . '-diaporama-' . $nom_equipe;
        }

        if ($fichier->getTypefichier() == 6) {
            $nom = $fichier->getNomautorisation();


            $fileName = $edition . '-eq-' . $libel_equipe . '-autorisation photos-' . $nom . '-' . uniqid();
        }
        if ($fichier->getTypefichier() == 7) {


            $fileName = $edition . '-eq-' . $libel_equipe . '-questionnaire equipe-' . $nom_equipe . '-' . uniqid();
        }
        return $fileName;
    }

    public function code($nom)
    {
        $nom = str_replace("à", "a", $nom);
        $nom = str_replace("ù", "u", $nom);
        $nom = str_replace("è", "e", $nom);
        $nom = str_replace("é", "e", $nom);
        $nom = str_replace("ë", "e", $nom);
        $nom = str_replace("ê", "e", $nom);
        $nom = str_replace("?", " ", $nom);
        $nom = str_replace("ï", "i", $nom);
        $nom = str_replace(":", "_", $nom);
        setLocale(LC_CTYPE, 'fr_FR');
        $nom = iconv('UTF-8', 'ASCII//TRANSLIT', $nom);


        return $nom;
    }
}