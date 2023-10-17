<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader   //pour télécharger l'affiche haute résolution depuis son ordinateur directement
{
    private $targetDirectory;
    private $edition;
    private $typefile;

    public function __construct($typefile, $targetDirectory, $edition)
    {
        $this->targetDirectory = $targetDirectory;
        $this->edition = $edition;
        $this->typefile = $typefile;
    }

    public function upload(UploadedFile $file)
    {

        switch ($this->typefile) {
            case 'affiche':
                $fileName = 'affiche' . $this->edition . '-HR.' . $file->guessExtension();
                break;
            case 'parrain':
                $fileName = 'parrain' . $this->edition . '.' . $file->guessExtension();
                break;
        }
        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload

        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}