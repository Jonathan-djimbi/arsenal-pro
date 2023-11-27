<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\CartDatabase;
use App\Entity\MailRetourStock;
use App\Entity\Produit;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MailRestockService{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // #[Route('/sauvegarde_mailer', name: 'sauvegarde_alerte_restock_mailer')]
    public function envoiMail(){ //ENVOI DE MAIL AUTOMATIQUE
        $array = [];
        $mailing = [];
        $mailRestock = $this->entityManager->getRepository(MailRetourStock::class)->findAllDistinct(0);
        $mailDejaEnvoye = $this->entityManager->getRepository(MailRetourStock::class)->findAllDistinct(1);
        // dd($mailRestock);
        foreach($mailDejaEnvoye as $fini){ //on regarde s'il y a des mails déjà envoyés ou non
            if($fini->isMailEtat()){
                $this->entityManager->remove($fini);
                $this->entityManager->flush(); //on efface ce qui ont été déjà remis en stock
            } 
        }

        foreach($mailRestock as $stock){ //on récupère les ID des produits qui sont en cours de précommande
                $array[] = $stock->getProduit()->getId(); 
        }
        $newArray = array_unique($array); //pour enlever les duplications d'ID produits
        // dd($newArray);
        $produit = $this->entityManager->getRepository(Produit::class)->findBy(["id" => $newArray]);
        $mail = $this->entityManager->getRepository(MailRetourStock::class)->findBy(["produit" => $newArray]); //tous les mails du $newArray

        foreach($produit as $prod){
            if($prod->getQuantite() > 0){ //si le nombre de quantite est supérieur à 0 (quantite disponible)
                foreach($mail as $ma){
                    if($prod->getId() === $ma->getProduit()->getId() && !$ma->isMailEtat()){
                        // $mailing[] = ['pid' => $ma->getProduit()->getId(),'mail' => $ma->getEmail(), "status" => $ma->IsMailEtat()];
                        $mailing[] = $ma->getProduit();   //collecte de produits pour le mail         
                    }
                }
            }
        }
        // dd($mailing);
        $mailEnvoi = new Mail();
        $subject = "Un de vos produits que vous avez regardé est de retour à l'Arsenal";
        $update = $this->entityManager->getRepository(MailRetourStock::class)->findBy(["produit" => $mailing]); //mail à envoyer pour
        // dd($update);
        if($update && count($update) > 0){
            foreach($update as $up){
                $up->setMailEtat(1); //maj BDD pour tout le monde
                $this->entityManager->flush();
                $mailEnvoi->send($up->getEmail(), "Utilisateur Arsenal Pro", $subject, $this->mailContent($up), 4640369); //envoi de mail
                echo $up->getEmail(). " mail envoyé\n";
            }
        } else {
            echo "Rien a envoyer pour le moment\n";
        }
        return new Response();
    }

    public function renvoieMail(){
        $dateNow = new DateTimeImmutable('now +1 hours');
        $delaiRelance = 86400 * 2; //deux jours si date pas changé 
        $historiqueProduits = $this->entityManager->getRepository(CartDatabase::class)->findAll();
        // dd($historiqueProduits);
        foreach($historiqueProduits as $histo){
            if(!$histo->isRelance()){
                $temps = strtotime($dateNow->format('Y-m-d H:i:s')) - strtotime($histo->getTimestamps()->format('Y-m-d H:i:s'));
                if($temps > $delaiRelance){
                    // dd($histo->getUser()->getEmail(), $histo->getCart());
                    $this->relanceMailContent($histo->getUser()->getEmail(), $histo->getUser()->getFullname(), array_slice(array_reverse($histo->getCart()),-5));
                    $histo->setRelance(true);
                    $this->entityManager->flush();
                }
            }  
        }
    }

    public function mailContent($produit){
        $URL = "https://arsenal-pro.fr";
        $prixProduit = 0;
        if($produit->getProduit()->getPricePromo() !== null && $produit->getProduit()->getPricePromo() < $produit->getProduit()->getPrice()){ //verif si le produit a un prix en promo
            $prixProduit = $produit->getProduit()->getPricePromo();
        } else {
            $prixProduit = $produit->getProduit()->getPrice();
        }
        $content = "<section style='font-family: arial;'> <section style='width: 95%; padding: 15px 0;'>
        <div>
        <h2 style='font-weight: normal;'>Le produit " . $produit->getProduit()->getName() . " est de retour à l'Arsenal</h2><br/>
        <div style='display: flex;'>
                <div style='margin-right: 10px;'><img width='100' height='100' style='object-fit: contain; height: 100px !important;' src='". $URL ."/uploads/". $produit->getProduit()->getIllustration()."' /></div>
                <div style='margin: auto 10px; width: 100%;'>
                    <b>". $produit->getProduit()->getName() ."</b>
                </div>
                <div style='width: 100%; margin-top: 15px;'>
                    <b>". number_format($prixProduit / 100) ."€</b>
                </div>
            </div>
            <h3 style='text-align: center; font-weight: normal;'><a href='". $URL ."/produit/". $produit->getProduit()->getSlug() ."'>Achetez-le dans l'armurerie</a></h3>
            <hr style='border: solid 1px #00000044'>
        </div>
        </section></section>";

        return $content;
    }

    public function relanceMailContent($email,$fullname, $produitsConsulte){
        // dd($produitsConsulte);
        $htmlProduit = "";
        $URL = "https://arsenal-pro.fr";
        $mail = new Mail();
        foreach($produitsConsulte as $produit){
            $leProduit = $this->entityManager->getRepository(Produit::class)->findOneById($produit);
            if($leProduit->getIsAffiche() && $leProduit !== null){ //si produit disponible et existe
                $htmlProduit .= "<div style='display: flex;'>
                <div style='margin-right: 10px;'><a href='". $URL . "/produit/" . $leProduit->getSlug() ."'><img width='100' height='100' style='object-fit: contain; height: 100px !important;' src='". $URL ."/uploads/". $leProduit->getIllustration()."' /></a></div>
                    <div style='margin: auto 10px; width: 100%;'>
                        <b>". $leProduit->getName() ."</b>
                        <p style='font-weight: normal;'>Qté restante(s) : ". $leProduit->getQuantite() ."</p>
                    </div>
                    <div style='width: 100%; margin-top: 15px;'>
                        <b>". number_format($leProduit->getPrice()/ 100, 2) ."€</b>
                    </div>
                </div><hr style='border: solid 1px #00000044'>";
            }
        }
        $content = "<section style='font-family: arial; color: black;'><section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='text-align: center; font-weight: normal;'>Produits ARSENAL PRO</h2>
        <h4 style='font-weight: normal; font-size: 1.04em;'>Bonjour ". $fullname ." !<br><br>Vous avez visité le site mais vous n'avez toujours pas fait d'achat. Êtes-vous intéréssé sur ces produits que vous avez consulté récemment ?</h4>
        <br/>
        <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Article(s)</h2>
        " . $htmlProduit ."
        <div>
            <h3 style='font-weight: normal;'>Profitez dès à présent une remise de 5% pour votre prochaine commande !</h3><br/>
            <div style='text-align: center; background-color: #07af15; width: auto; padding: 10px; margin: auto; width: 200px; color: white;'><p style='font-weight: bold;'>CODE : ARSENAL5</p></div>
        </div>
        </section></section>";

        $mail->send($email,$fullname,$fullname . " ne laissez pas vos produits ARSENAL PRO !",$content, 4640369);
        echo "Mail envoyé à " . $email . "\n";
        return new Response();
    }
}
?>
