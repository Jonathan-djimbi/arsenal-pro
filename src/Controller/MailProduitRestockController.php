<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\MailRetourStock;
use App\Entity\Produit;
use App\Service\MailRestockService as ServiceMailRestockService;
use Doctrine\ORM\EntityManagerInterface;
use MailRestockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailProduitRestockController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/sauvegarde_alerte', name: 'sauvegarde_alerte_restock_produit_mail')]
    public function mailRestockSave(Request $req): Response
    {
        $email =  $req->request->get("email");
        $produit_id = $req->request->get("id");
        if($email && $produit_id){
            $verifSiMailDejaFait = $this->entityManager->getRepository(MailRetourStock::class)->findBy(['email' => $email, 'produit' => $produit_id]);
            $produit_selectionne = $this->entityManager->getRepository(Produit::class)->findBy(['id' => $produit_id]);
            // dd($produit_selectionne[0]);

            if($verifSiMailDejaFait && count($verifSiMailDejaFait) > 0){ //si alerte déjà existante pour le même produit à la même adresse mail
                // echo "<script>console.log('alerte déjà existante')</script>";
                return new Response();
            } else {

                if(!empty($email)){
                    $newMail = new MailRetourStock();
                    $newMail->setEmail($email);
                    $newMail->setProduit($produit_selectionne[0]);
                    $newMail->setMailEtat(0);
                    $this->entityManager->persist($newMail); // MAJ BDD
                    $this->entityManager->flush();
                    // return $this->redirect($req->getUri());
                }
            }
        }
        return new Response();
    }
    public function envoiMailAction(ServiceMailRestockService $mailRestockService) //executer le service pour l'envoi de mail quand restock produit //voir dans /Service/MailRestockService.php
    {
        $mailRestockService->envoiMail();
        return new Response();
    }
    
}
