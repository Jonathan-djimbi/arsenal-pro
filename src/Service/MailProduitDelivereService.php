<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\CarteFidelite;
use App\Entity\MailRetourStock;
use App\Entity\Order;
use App\Entity\Produit;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MailProduitDelivereService{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function envoiMail($orderId){ //ENVOI DE MAIL AUTOMATIQUE
       
        $commande = $this->entityManager->getRepository(Order::class)->findOneBy(['id' => $orderId]);
        $mailEnvoi = new Mail();
        $subject = 'Votre commande a été délivrée !';
        // dd($this->mailContent($commande));
        // dd($commande->getUser()->getEmail());
        // dd($commande->getUser()->getCarteFidelite()->getValues()[0]->getPoints());
        $mailEnvoi->send($commande->getUser()->getEmail(), "Arsenal Pro", $subject, $this->mailContent($commande), 4130880); //envoi de mail

        // echo $cf->getUser()->getEmail(). " mail envoyé\n";
        return new Response();
    }

    public function mailContent($co){
        $contentArticleCommande = '';
        $URL = "https://arsenal-pro.fr";

        foreach ($co->getOrderDetails()->getValues() as $produit) { 
            $produitBase = $this->entityManager->getRepository(Produit::class)->findOneById($produit->getPid()->getId());  
            if($produitBase){
                $contentArticleCommande .= "<div style='display: flex;'>
                    <div style='margin-right: 10px;'><img width='100' height='100' style='object-fit: contain; height: 100px !important;' src='". $URL ."/uploads/". $produitBase->getIllustration()."' /></div>
                    <div style='margin: auto 10px; width: 100%;'>
                        <b>". $produitBase->getName() ."</b>
                        <p style='font-weight: normal;'>Qté(s) : ". $produit->getQuantity() ."</p>
                    </div>
                    <div style='width: 100%; margin-top: 15px;'>
                        <b>". number_format(($produit->getPrice() * $produit->getQuantity()) / 100, 2) ."€</b>
                    </div>
                </div><hr style='border: solid 1px #00000044'>";
            }
        }

        $content = "<section style='font-family: arial;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='font-weight: normal; color: #5D7F59;'>Bonjour ". $co->getUser()->getFirstname() .", partagez votre avis et consulter vos soldes de points !</h2>
        <h3 style='font-weight: normal; color: #5D7F59;'>Votre commande, ". $co->getReference() . " a été livrée chez vous à l'adresse suivante :</h3>
        <div>
            <p style='font-weight: normal;'>". $co->getDelivry()->getAdress() ."</p>
            <p style='font-weight: normal;'>". $co->getDelivry()->getPostal() . ", " . $co->getDelivry()->getCity() .", ". $co->getDelivry()->getCountry() ."</p>
        </div><br/>
        <div>
            <h2 style='font-weight: normal; color: #5D7F59; text-align: center;'>Récapitulatif de votre commande</h2>
            " . $contentArticleCommande . "
        </div>
        <div>
             <h2 style='text-align: center; font-weight: normal; color: #5D7F59;'>Êtes-vous satisfait ? Votre avis compte pour nous.<br>Partagez votre expérience en mettant un avis !</h2><br/>
             <a href='https://g.page/r/Ce7KJAXTDyZ9EA0/review'>
                <img style='margin: auto; display: block;' width='200' src='" .$URL."/assets/image/avis_exemple.png'>
             </a>
        </div><br/>
        <div>
            <p style='font-weight: normal; text-align: center;'>Vous avez en tout " . $co->getUser()->getCarteFidelite()->getValues()[0]->getPoints()  ." points !</p>
        </div>
        </div>
        </section></section>";

        return $content;
    }
}