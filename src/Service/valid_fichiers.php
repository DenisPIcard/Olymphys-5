<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class valid_fichiers

{
    private ValidatorInterface $validator;
    private ParameterBagInterface $parameterBag;
    private RequestStack $requestStack;

    public function __construct(ValidatorInterface $validator, ParameterBagInterface $parameterBag, RequestStack $requestStack)
    {

        $this->validator = $validator;
        $this->parameterBag = $parameterBag;
        $this->requestStack = $requestStack;
    }

    public function validation_fichiers(UploadedFile $file, $num_type_fichier, int $idFichier = null): array
    {
        $session = $this->requestStack->getSession();
        switch ($num_type_fichier) {
            case 0 :
                $max_size = '2600k';
                $mimeTYpes = ['application/pdf',];
                $nbPageMax = 20;
                break;
            case 1 :
                $max_size = '2600k';
                $mimeTYpes = ['application/pdf',];
                $nbPageMax = 20;
                break;
            case 2 :
                $max_size = '1024k';
                $mimeTYpes = ['application/pdf',];
                $nbPageMax = 1;
                break;
            case 3 :
                $max_size = '10000k';
                $mimeTYpes = ['application/pdf',];
                break;
            case 4 : //fiche sécurité oral
                $max_size = '1024k';
                $mimeTYpes = ['application/pdf', 'application/x-pdf', "application/msword",
                    'application/octet-stream',
                    'application/vnd.oasis.opendocument.text',
                    'image/jpeg'];
                break;
            case 5 :
                $max_size = '10000k';
                $mimeTYpes = ['application/pdf',];
                break;
            case 6 :
                $max_size = '1024k';
                $mimeTYpes = ['application/pdf', 'application/x-pdf'];
                break;
            case 7 :
                $max_size = '1024k';
                $mimeTYpes = ['application/pdf', 'application/x-pdf', "application/msword",
                    'application/octet-stream',
                    'application/vnd.oasis.opendocument.text',
                    'image/jpeg'];
                break;
            case 8 ://fiche securité expo
                $max_size = '1024k';
                $mimeTYpes = ['application/pdf', 'application/x-pdf', "application/msword",
                    'application/octet-stream',
                    'application/vnd.oasis.opendocument.text',
                    'image/jpeg'];
                break;
            case 9 :
                $max_size = '10000k'; // Les photos
                $mimeTYpes = [
                    'image/jpeg', 'image/jpg'];
                break;
        }

        $violations = $this->validator->validate(
            $file,
            [new NotBlank(),
                new File(['maxSize' => $max_size,
                        'mimeTypes' => $mimeTYpes,
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier du bon format',
                    ]
                )]);
        if ($violations->count() > 0) {
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];
            //dd($_REQUEST['FichierID']);
            $session->set('idFichier', $idFichier);// nécessaire dans le cas d'un upload de fichier non valide, valid_fichier fait disparaître les paramètres de $request->query

            return ['text' => $violation->getMessage()];

        }
        if (($num_type_fichier == 0) or ($num_type_fichier == 1) or ($num_type_fichier == 2)) {
            $sourcefile = $file;
            $stringedPDF = file_get_contents($sourcefile, true);
            $regex = "/\/Page |\/Page\/|\/Page\n|\/Page\r\n|\/Page>>\r/";//selon l'outil de codage en pdf utilisé, les pages ne sont pas repérées de la m^me façon
            $pages = preg_match_all($regex, $stringedPDF, $title);

            if ($pages == 0) {
                $regex = "/\/Pages /";
                $pages = preg_match_all($regex, $stringedPDF, $title);

            }
            if ($pages > $nbPageMax) { //S'il y a plus de 20 ou 1  pages la procédure est interrompue et on return à la page d'accueil avec un message d'avertissement
                $session->set('idFichier', $idFichier);// nécessaire dans le cas d'un upload de fichier non valide, valid_fichier fait disparaître les paramètres de $request->query

                return ['text' => 'Votre ' . $this->parameterBag->get('type_fichier_lit')[$num_type_fichier] . ' contient  ' . $pages . ' pages. Il n\'a pas pu être accepté, il ne doit pas dépasser ' . $nbPageMax . ' page(s) !'];

            }
        }


        return ['text' => ''];


    }
}