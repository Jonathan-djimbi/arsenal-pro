<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\MailRetourStock;
use App\Entity\Produit;
use App\Entity\VenteFlash;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VenteFlashProduitService extends AbstractController {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setVenteFlashAuProduit($produit){ 
        $venteFlashExistant = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(['pid' => $produit->getId()]); //vérifier si un produit est ou était déjà en vente flash
        $dateFinale = new \DateTimeImmutable('now +2 days +1 hours'); //heures defaut
        // dd($dateFinale);
        if($produit->isVenteFlash()){ //si deja en vente flash
            $venteFlash = $venteFlashExistant;
            $produit->setIsVenteFlash(0); //on annule, n'est plus en etat vente flash
            $venteFlash->setIsAffiche(0); //on annule, on n'affiche plus
            $this->addFlash('notice', "<span style='color: red;'>Le produit ". $produit->getName() ." n'est <strong>PLUS</strong> en <u>VENTE FLASH</u>.</span>"); //pas vente flash

        } else {
            if(!$produit->isVenteFlash()){
                $produit->setIsVenteFlash(1);
            }
            if($venteFlashExistant){ //juste update
                $venteFlash = $venteFlashExistant;
                $venteFlash->setTemps($dateFinale); //update temps
                $venteFlash->setIsAffiche(1);
            } else { //juste création
                $venteFlash = new VenteFlash(); //nouveau produit inséré pour la table vente flash
                $venteFlash->setTemps($dateFinale);
                $venteFlash->setIsAffiche(1);
                $venteFlash->setPid($produit); //insertion dans la BDD
                $venteFlash->setNewPrice($produit->getPrice()); //insertion auto prix de base
                $this->entityManager->persist($venteFlash);
            }

            $this->addFlash('notice', "<span style='color: green;'>Le produit ". $produit->getName() ." est en <strong><u>VENTE FLASH</u></strong>.</span>"); // activer vente flash
        }
        
    }

    public function checkIfVenteFlash($produit){
        $venteFlashExistant = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(['pid' => $produit->getId()]); //vérifier si un produit est ou était déjà en vente flash
        if($venteFlashExistant){
            if($produit->isVenteFlash()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function updateAutoVenteFlashState(){
        $venteFlashExistant = $this->entityManager->getRepository(VenteFlash::class)->findAll(); //vérifier si un produit est ou était déjà en vente flash
        $datenow = new \DateTimeImmutable('now +1 hours');

        foreach($venteFlashExistant as $flash){
            if($flash->getTemps() < $datenow){
                $produit = $this->entityManager->getRepository(Produit::class)->findOneById($flash->getPid());
                $flash->setIsAffiche(false); //désaffiche
                $produit->setIsVenteFlash(false); //désactive depuis produit
                //effacer aussi on peut le faire avec remove pour enlever le produit de la BDD venteflash
                $this->entityManager->remove($flash);
            }
            $this->entityManager->flush();
        }
    }

}
?>
