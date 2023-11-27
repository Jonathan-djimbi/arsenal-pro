<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PrestationController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/nos-prestations', name: 'app_prestation')]
    public function index(Request $req): Response
    {
        $prestations = $this->entityManager->getRepository(Produit::class)->findBy(['category' => 7]);
        // dd($prestations);

        return $this->render('prestation/index.html.twig', [
            'prestations' => $prestations
        ]);
    }
}
