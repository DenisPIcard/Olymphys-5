<?php

namespace App\Service;

class PhotoNamer
{
    public function PhotoName($equipe, $photo)
    {
        $editionEd = $equipe->getEdition()->getEd();
        $lettre_equipe = '';
        $centre = ' ';
        if ($equipe->getCentre()) {
            $centre = $equipe->getCentre()->getCentre();

        }
        $numero_equipe = $equipe->getNumero();
        if ($equipe->getLettre()) {
            $lettre_equipe = $equipe->getLettre();
        }

        $nom_equipe = $equipe->getTitreProjet();
        $nom_equipe = str_replace("à", "a", $nom_equipe);
        $nom_equipe = str_replace("ù", "u", $nom_equipe);
        $nom_equipe = str_replace("è", "e", $nom_equipe);
        $nom_equipe = str_replace("é", "e", $nom_equipe);
        $nom_equipe = str_replace("ë", "e", $nom_equipe);
        $nom_equipe = str_replace("ê", "e", $nom_equipe);
        $nom_equipe = str_replace("ô", "o", $nom_equipe);
        $nom_equipe = str_replace("?", "", $nom_equipe);
        $nom_equipe = str_replace("ï", "i", $nom_equipe);
        setLocale(LC_CTYPE, 'fr_FR');


        $nom_equipe = iconv('UTF-8', 'ASCII//TRANSLIT', $nom_equipe);
        //$nom_equipe= str_replace("'","",$nom_equipe);
        //$nom_equipe= str_replace("`","",$nom_equipe);

        //$nom_equipe= str_replace("?","",$nom_equipe);
        if ($photo->getNational() == FALSE) {
            $fileName = $editionEd . '-' . $centre . '-eq-' . $numero_equipe . '-' . $nom_equipe . '.' . uniqid();
        }
        if ($photo->getNational() == TRUE) {
            $fileName = $editionEd . '-CN-eq-' . $lettre_equipe . '-' . $nom_equipe . '.' . uniqid();
        }

        return $fileName . '.jpg';
    }
}