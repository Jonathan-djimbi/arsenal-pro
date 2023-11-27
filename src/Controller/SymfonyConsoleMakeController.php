<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SymfonyConsoleMakeController extends AbstractController
{
    #[Route('/symfony/console/make', name: 'app_symfony_console_make')]
    public function index(): Response
    {
        return $this->render('symfony_console_make/index.html.twig', [
            'controller_name' => 'SymfonyConsoleMakeController',
        ]);
    }
}
