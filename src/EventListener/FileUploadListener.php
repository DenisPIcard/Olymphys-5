<?php
// src/EventListener/FileUploadListener.php
namespace App\EventListener;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\Memoires;
use App\Entity\Equipes;
use App\Entity\Totalequipes;
use App\Repository\TotalequipesRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;


class FileUploadListener
{
    private $uploader;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;

    }


    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    private function uploadFile($entity)
    {


        if (!$entity instanceof Memoires) {
            return;
        }

        $file = $entity->getMemoire();
        $lettre_equipe = $entity->getEquipe()->getLettre();
        $nom_equipe = $entity->getEquipe()->getTitreProjet();
        //$nom_equipe=$entity->getNom_equipe($lettre_equipe);

        // only upload new files
        if ($file instanceof UploadedFile) {
            $fileName_ext = $file->guessExtension();
            //$filename=$lettre_equipe.'-memoire-'.$nom_equipe.'.'.$filename;
            $filename = $lettre_equipe . '-memoire-' . $nom_equipe . '.' . $fileName_ext;

            $entity->setMemoire($filename);
            $entity->setEquipe($lettre_equipe);
        } elseif ($file instanceof File) {
            // prevents the full file path being saved on updates
            // as the path is set on the postLoad listener
            $entity->setMemoires($file->getFileName());
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Product) {
            return;
        }

        if ($fileName = $entity->getMemoire()) {
            $entity->setMemoire(new File($this->uploader->getTargetDirectory() . '/' . $fileName));
        }
    }
}

