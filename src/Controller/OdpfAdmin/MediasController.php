<?php

namespace App\Controller\OdpfAdmin;

use FM\ElfinderBundle\Form\Type\ElFinderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MediasController extends AbstractController
{
   #[Isgranted('ROLE_ADMIN')]
   #[Route("/odpf/medias/admin_medias", name:"admin_medias")]
    public function admin_medias(): Response
    {
        $form = $this->createFormBuilder()
            ->add('Cliquer', ElFinderType::class, ['label' => 'Explorer les médias'])
            ->getForm();

        return $this->render('OdpfAdmin/gestions-medias.html.twig', array('form' => $form->createView()));

    }


}