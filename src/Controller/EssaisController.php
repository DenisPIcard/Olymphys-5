<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EssaisController extends AbstractController
{
    #[Route('/essais', name: 'app_essais')]
    public function index(): Response
    {
        return $this->render('essais/index.html.twig', [
            'controller_name' => 'EssaisController',
        ]);
    }
}
