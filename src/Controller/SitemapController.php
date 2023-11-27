<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap', name: 'app_sitemap')]
    public function index(): Response
    {
        return $this->render('sitemap/index.html.twig', [
            'controller_name' => 'SitemapController',
        ]);
    }
}
