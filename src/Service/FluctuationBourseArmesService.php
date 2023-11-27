<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\BourseArmes;
use App\Entity\MailRetourStock;
use App\Entity\Produit;
use App\Entity\VenteFlash;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FluctuationBourseArmesService extends AbstractController {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updatePrixArmes(){ //NON UTILISE
        $lesProduitsEnBourse = $this->entityManager->getRepository(BourseArmes::class)->findAll();
        $prixStock = [];
        $prixFluctue = 0;
        $prixPromotion = 0;
        $operateurRandom = rand(0,1); //choisir un operateur selon + ou - d'après la condition ci-dessous
        foreach($lesProduitsEnBourse as $unProduit){
            if($unProduit->isAffiche() && $unProduit->getPid()->getIsAffiche()){ //si produit en bourse est affiché (disponible)
                $prixStock = []; //vide tableau prix à chaque boucle
                if($unProduit->getPid()->getPricePromo() > 0 && $unProduit->getPid()->getPricePromo() < $unProduit->getPid()->getPrice()){
                    $prixPromotion = $unProduit->getPid()->getPricePromo();
                } else {
                    $prixPromotion = $unProduit->getPid()->getPrice();
                }
                if($operateurRandom == 0){ //FLUCTUATION DE PRIX
                    if(($prixPromotion + ($prixPromotion * (rand(1,5)/100))) < $prixPromotion){ //si prix fluctué n'est pas supérieur au prix de base
                        $prixFluctue = ($prixPromotion + ($prixPromotion * (rand(1,3)/100) )); 
                    } else {
                        $prixFluctue = $unProduit->getPid()->getPrice() - ($prixPromotion * 0.01);
                    }
                } else {
                    $prixFluctue = ($prixPromotion - ($prixPromotion * (rand(1,3)/100) ));
                }

                foreach($unProduit->getPrixArray() as $prix){
                    $prixStock[] = $prix; //on récupère les valeurs précédentes des prix du tableau
                    if(count($prixStock) === count($unProduit->getPrixArray())){
                        $prixStock[] = floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2) / 100); //prix finale pour l'instant
                        $unProduit->getPid()->setPricePromo(floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2))); //pour éviter conflit lors du paiement ou sinon erreur
                    }
                }
                if(count($unProduit->getPrixArray()) === 0){ //CONDITION POUR SEULEMENT NOUVEAU TABLEAU PRIX (vide)
                    $prixStock[] = floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2) / 100); //prix finale pour l'instant
                    $unProduit->getPid()->setPricePromo(floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2))); //pour éviter conflit lors du paiement ou sinon erreur
                }
                $unProduit->setPrixArray($prixStock);
            }
            $this->entityManager->flush();
        }
        // dd($lesProduitsEnBourse);
    }
    
    public function updatePrixUneArme($unProduit, $quantitePrise){
        $prixStock = [];
        $quantiteAchete = [];
        $prixFluctue = 0;
        $prixPromotion = 0;
        $dateNow = new \DateTimeImmutable('now +1 hours');

        if($unProduit->isAffiche() && $unProduit->getPid()->getIsAffiche() && $unProduit->getDateLimite() > $dateNow){ //si produit en bourse est affiché (disponible) ou date supérieur à today
            $prixStock = []; //vide tableau prix à chaque boucle
            $quantiteAchete = [];
            if($unProduit->getPrixTempsReel() > 0){ //si prixTempsReel supérieur à 0 que quand prixTempsReel a été déjà mise à jour
                $prixPromotion = $unProduit->getPrixTempsReel();
            } else { //seulement si, le prixTempsReel <= 0 que quand prixTempsReel n'a pas encore eu de mise à jour de prix //INIT 
                $prixPromotion = $unProduit->getPid()->getPrice();
            }
       
            $prixFluctue = $prixPromotion - (($prixPromotion - $unProduit->getPrixFinal())/$unProduit->getQuantiteMax());
            // dd($prixFluctue);
            foreach($unProduit->getPrixArray() as $prix){ //pour les prix
                $prixStock[] = $prix; //on récupère les valeurs précédentes des prix du tableau
                if(count($prixStock) === count($unProduit->getPrixArray())){ //si le nombre de tableau de prixStock match le nombre total de tableau de prixArray
                    $prixStock[] = floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2) / 100); //prix finale pour l'instant
                    $unProduit->setPrixTempsReel(floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2))); //STOCKAGE dernier prix
                }
            }
            foreach($unProduit->getQuantiteArray() as $quantite){ //pour les quantités
                $quantiteAchete[] = $quantite; //on récupère les valeurs précédentes des prix du tableau
                if(count($quantiteAchete) === count($unProduit->getQuantiteArray())){ //si le nombre de tableau de quantiteAchete match le nombre total de tableau de quantiteArray
                    $quantiteAchete[] = $quantite + $quantitePrise; //$quantite là équivaut au dernier valeur du tableau quantiteArray, on addition la dernière valeur avec la nouvelle valeur
                }
            }

            if(count($unProduit->getPrixArray()) === 0){ //CONDITION POUR SEULEMENT NOUVEAU TABLEAU PRIX ou TABLEAU QUANTITE (vide)
                $prixStock[] = floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2) / 100); //prix finale pour l'instant
                $unProduit->setPrixTempsReel(floatval(preg_replace('/[^\d.]/', '', number_format($prixFluctue),2))); //STOCKAGE dernier prix
                $quantiteAchete[] = $quantitePrise;
            }
            //update de nos array
            $unProduit->setPrixArray($prixStock);
            $unProduit->setQuantiteArray($quantiteAchete);

            // $this->addFlash('notice', "<span style='color: green;'>Le prix du produit en bourse <strong>". $unProduit->getPid()->getName() ."</strong> a <u>évolué/mis à jour</u>.</span>"); // message success
        }
    }
}
?>
