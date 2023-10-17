<?php

namespace App\Controller\OdpfAdmin;

use FM\ElfinderBundle\Form\Type\ElFinderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


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