<?php

namespace App\Controller;

use App\Entity\ConditionFidelite;
use App\Entity\ConditionGeneraleVente;
use App\Entity\MentionsLegales;
use App\Entity\ReglementGeneraleProtectionDonnees;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Node\Expression\ConditionalExpression;

class ReglementController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/reglement/cgv', name: 'app_reglement_cgv')]
    public function index(): Response
    {
        $cgv = $this->entityManager->getRepository(ConditionGeneraleVente::class)->findAll();

        return $this->render('reglement/cgv.html.twig', [
            'cgv' => $cgv

        ]);
    }
    #[Route('/reglement/rgpd', name: 'app_reglement_rgpd')]
    public function sousindex(): Response
    {
        $rgpd = $this->entityManager->getRepository(ReglementGeneraleProtectionDonnees::class)->findAll();

        return $this->render('reglement/rgpd.html.twig', [
            'rgpd' => $rgpd

        ]);
    }

    #[Route('/reglement/programme-fidelite', name: 'app_programme_fidelite')]
    public function programmeFidelite(): Response
    {
        $programme = $this->entityManager->getRepository(ConditionFidelite::class)->findAll();

        return $this->render('reglement/programme_fidelite.html.twig', [
            'programme' => $programme

        ]);
    }
    
    #[Route('/reglement/mentions-legales', name: 'app_mentions_legales')]
    public function mentionsLegales(): Response
    {
        $programme = $this->entityManager->getRepository(MentionsLegales::class)->findAll();

        return $this->render('reglement/mentions_legales.html.twig', [
            'legales' => $programme

        ]);
    }
}
