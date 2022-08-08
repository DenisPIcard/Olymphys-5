<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

//use App\Service\FileUploader;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Naming\PropertyNamer;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use App\Entity\Edition;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * @ORM\Table(name="photosinterthumb")
 * @ORM\Entity(repositoryClass="App\Repository\PhotosinterthumbRepository")
 * @Vich\Uploadable
 */
class Photosinterthumb
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255,  nullable=true)
     * @Assert\Unique
     * @var string
     */
    private $photo;


    /**
     *
     * @var File
     * @Vich\UploadableField(mapping="photosinterthumb", fileNameProperty="photo")
     *
     */
    private $photoFile;


    /**
     *
     * x
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    public function getEdition()
    {
        return $this->edition;
    }

    public function setEdition($edition)
    {
        $this->edition = $edition;
        return $this;
    }

    public function getPhotoFile()
    {
        return $this->photoFile;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
        if ($photo) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTimeImmutable();

            //return $this;
        }
    }


    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $photoFile
     */
    public function setPhotoFile(?File $photoFile = null): void

    {
        $this->photoFile = $photoFile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost

    }


    public function getId()
    {
        return $this->id;
    }


    public function directoryName(): string
    {
        /*$em=$this->getDoctrine()->getManager();
               $edition=$this->session->get('edition');
               $edition=$em->merge($edition);*/
        $edition = $this->getEdition();
        $path = '/' . $edition->getEd() . '/int/thumb';

        return $path;
    }


    public function personalNamer()    //permet à vichuploeder et à easyadmin de renommer le fichier, ne peut pas être utilisé directement
    {

        $edition = $this->getEdition();
        /*$equipe=$this->getEquipe();
        $centre=$equipe->getCentre()->getCentre();
        $numero_equipe=$equipe->getNumero();
        $nom_equipe=$equipe->getTitreProjet();
        $nom_equipe= str_replace("à","a",$nom_equipe);
        $nom_equipe= str_replace("ù","u",$nom_equipe);
        $nom_equipe= str_replace("è","e",$nom_equipe);
        $nom_equipe= str_replace("é","e",$nom_equipe);
        $nom_equipe= str_replace("ë","e",$nom_equipe);
        $nom_equipe= str_replace("ê","e",$nom_equipe);
        $nom_equipe= str_replace("ô","o",$nom_equipe);
        $nom_equipe= str_replace("?","",$nom_equipe);
        $nom_equipe= str_replace("ï","i",$nom_equipe);
         setLocale(LC_CTYPE,'fr_FR');


        $nom_equipe = iconv('UTF-8','ASCII//TRANSLIT',$nom_equipe);
        //$nom_equipe= str_replace("'","",$nom_equipe);
        //$nom_equipe= str_replace("`","",$nom_equipe);

        //$nom_equipe= str_replace("?","",$nom_equipe);*/
        $fileName = $edition->getEd() . uniqid();

        return $fileName;
    }


    /**
     * Updates the hash value to force the preUpdate and postUpdate events to fire.
     */
    public function refreshUpdated()
    {
        $this->setUpdated(new \DateTime());
    }


    public function setUpdatedAt($date)
    {
        $this->updatedAt = $date;

        return $this;
    }


    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


}

