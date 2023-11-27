<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\CarteFidelite;
use App\Entity\Order;
use App\Entity\PointFidelite;
use App\Entity\Produit;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options as PDFOptions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RemboursementController extends AbstractController{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/remboursement/commande/{id}/{reference}', name: 'app_order_refund')]
    public function remboursement(Request $request, $id, $reference){
        $date = new \DateTimeImmutable('now +1 hours');
        $order = $this->entityManager->getRepository(Order::class)->findOneById($id);
        $acheteur = $this->entityManager->getRepository(Adress::class)->findBy(["id" => $order->getDelivry()]); //adresse livraison
        $adresse = $this->entityManager->getRepository(Adress::class)->findByUser($order->getUser());

        $point_fidelite = $this->entityManager->getRepository(PointFidelite::class)->findAll()[0]; //règle et chiffrage pour point fidelite
        $utilisateurFidele = $this->entityManager->getRepository(CarteFidelite::class)->findOneByUser($order->getUser()); //compte fidelite utilisateur

        $form = $this->createForm(\App\Form\RefundType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $montantRembourse = floatval(preg_replace('/[^\d.]/', '', number_format($form->get('prix')->getData() * 100))); //donnée du formulaire
            $order->setState(-1);
            $order->setRefundAmount($montantRembourse);
            $order->setRefundedAt($date);
            if(!$order->getPromo() && !$order->getPointFideliteUtilise() && $order->getTotalFinal() >= $point_fidelite->getMontantPanier()){ //si pas code promo utilisé et pas points utilisé et commande supérieur ou égale à 20€
                //enlèvement points de fidélité si pas de code promo utilisé et pas de point fidelite utilise
                $pointsAEnlever = (number_format($montantRembourse / ($point_fidelite->getMontantPanier()),0,",","") * $point_fidelite->getPoint());
                if($pointsAEnlever > $utilisateurFidele->getPoints() || $pointsAEnlever == $utilisateurFidele->getPoints()){ //si points à enlever est supérieur aux points que le client a alors,
                    $utilisateurFidele->setPoints(0); //permet d'éviter d'avoir des points négatifs
                } else { //si tout est correcte niveau point alors
                    $utilisateurFidele->setPoints($utilisateurFidele->getPoints() - (number_format($montantRembourse / ($point_fidelite->getMontantPanier()),0,",","") * $point_fidelite->getPoint()));
                }

            }
            // if($order->getMontantCompteUtilise() && $order->getMontantCompteUtilise() > 0){
            //     //remboursement du compte montant
            // }
            $this->entityManager->flush();
            $this->factureCommande($order,$adresse[0], $acheteur[0], $reference, $montantRembourse);
            $this->addFlash('notice','La commande ' . $reference . " a été remboursée.");
        }
        return $this->render('admin/commande/index.html.twig', [
            'form' => $form->createView(),
            'reference' => $reference,
            'order' => $order,
        ]);
    }

    public function factureCommande($order,$adresse,$acheteur,$nofacture,$montantRm){ //pour générer une facture remboursée
        $mail = new Mail();

        $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
            <h2 style='text-align: center; font-weight: normal;'>ARSENAL PRO vous confirme le remboursement de votre commande</h2>
            <h4 style='font-weight: normal; font-size: 1.04em;'>Bonjour ". $order->getUser()->getFirstname() ." ". $order->getUser()->getLastname() ." !<br><br>Votre commande n°" . $order->getReference() . " a été remboursé.<br></h4>
            <div style='text-align: center; background-color: #07af15; width: 200px; padding: 12px; font-size: 1.1em; margin: auto;'><a href='https://arsenal-pro.fr/compte/mes-commandes/" . $order->getReference() . "' style='color: white; text-decoration: none;'>VÉRIFIEZ LE STATUT DE VOTRE COMMANDE</a></div> 
        </div>
        <br/>
        </section></section>";

        $lesproduits = "";
        $totaux = "";
        $livraison = "";
        $prix_total = 0;
        // dd($montantRm, $adresse);
        foreach ($order->getOrderDetails()->getValues() as $liste){
            
            if($liste->getPid()){ //si relation PID existe (si produit non effacé de la BDD)
               $pid = $liste->getPid()->getId();
            } else {
                $pid = "####";
            }
            $lesproduits .= "<tr>
                <td>". $pid ."</td>
                <td>". $liste->getProduct()."</td>
                <td>". $liste->getQuantity() ."</td>
                <td>". str_replace(",","'", number_format(($liste->getPrice() / 100) / 1.2 ,3)) ." €</td>
                <td>". str_replace(",","'",number_format(($liste->getTotal() / 100) - (($liste->getTotal() / 100) / 1.2) ,3)) ." €</td>
                <td>". str_replace(",","'",number_format(($liste->getTotal() / 100) ,2)) ." €</td>
            </tr>";
            $prix_total = $prix_total + ($liste->getTotal() / 100);
        };
        if($order->getCarrierPrice() > 0){
            $livraison = "<p>Livraison : ". number_format($order->getCarrierPrice() / 100,2)."€</p>";
            $prix_total = $prix_total + ($order->getCarrierPrice() /100);
        }
        if($order->getPointFideliteUtilise() && $order->getPointFideliteUtilise() > 0){ //si fidele et point fidelite utilisé
            $prix_ancien = $prix_total;
            $prix_total = $prix_total - ($order->getPointFideliteUtilise()/100);
            $totaux = "<div id='prixtotal_remise'>
            <p>Total HT : " .  str_replace(",","'",number_format($prix_ancien / 1.2 ,3)) ."€</p>
            <p>Remise fidélité : -" . str_replace(",","'",number_format(($prix_ancien / 1.2) - ($prix_total / 1.2),3)) . "€</p>
            <p>Total HT remisé : " .  str_replace(",","'",number_format($prix_total / 1.2 ,3)) ."€</p>
            <p>TVA (20%) : " . str_replace(",","'",number_format(($prix_total) - ($prix_total / 1.2) ,3)) . "€</p>
            ". $livraison ."
            <b>Total TTC : ". str_replace(",","'",number_format($prix_total,2)). " €</b></div>
            <b>Remboursé TTC : ". str_replace(",","'", number_format($montantRm/100,2)) ." €</b>";
        } else {
            $totaux = "<div id='prixtotal_nonremise'>
            <p>Total HT : " .  str_replace(",","'",number_format($prix_total / 1.2 ,3)) ."€</p>
            <p>TVA (20%) : " . str_replace(",","'",number_format(($prix_total) - ($prix_total / 1.2) ,3)) . "€</p>
            ". $livraison ."
            <b>Total TTC : ". str_replace(",","'",number_format($prix_total,2)). " €</b></div>
            <b>Remboursé TTC : ". str_replace(",","'", number_format($montantRm/100,2)) ." €</b>";
        }

        $html = "<body>
        <style type='text/css'>
            *{
                font-family: Arial, Helvetica, sans-serif;
            }
            #header h2{
                text-align: center;
            }
            #footer{
                bottom: 0;
                position: absolute;
                display: block;
                line-height: 0.2px;
                font-size: 1em;
            }
            .ligne_une{
                display: inline-block;
            }
            .ligne_une, .ligne_deux{
                line-height: 8px;
                font-size: 0.8em;
            }
            .ligne_une div img{
                left: 30px;
                position: relative;
            }
            #info_client{
                position: absolute;
                right: 5px;
                text-align: right;
            }
            td, th {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
                font-size: 0.8em;
              }
              table {
                border-collapse: collapse;
                width: 100%;
              }
              th, #prixtotal {
                background-color: #dddddd;
              }
              .info_entreprise, .info_entreprise p{
                position: relative;
                font-size: 0.8em;
              }
              #prixtotal{
                position: relative;
                text-align: right;
                width: 180px;
                padding: 10px;
                font-size: 0.8em;
                right: 0;
                float: right;
                display: block;
              }
              #prixtotal div{
                margin-right: 10px;
                text-align: right;
              }
              #prixtotal div p{
                position: relative;
              }
              #prixtotal_remise{
                height : 190px;
                position: relative;
              }
              #prixtotal_nonremise{
                height : 120px;
                position: relative;
              }

              #remiseFidele{
                position: relative;
                text-align: right;
                font-size: 1em;
              }
        </style>
            <div id='header'><br/>
                <div class='ligne_une'>
                    <div>
                        <img width='100' src='https://arsenal-pro.fr/assets/image/icon-facture.jpg'/>
                    </div><br/>
                    <div id='info_client'>
                        <p>".$order->getUser()->getFirstname()." ". $order->getUser()->getLastname() . "</p>
                        <p>". $adresse->getAdress() ."</p>
                        <p>". $adresse->getPostal() . " " . $adresse->getCity() ."</p>
                        <p>Tél : ". $adresse->getPhone() ."</p>
                        <p>Email : ". $order->getUser()->getEmail() . "</p>
                        <p>N° Client : ". $order->getUser()->getId() ."</p>
                    </div>
                </div>
                <div class='ligne_deux'><br/>
                    <b>ARMURERIE ARSENAL PRO</b>
                    <p>710 Rue du Léman, C2a</p>
                    <p>74140 Chens-sur-Léman</p>
                    <p>FRANCE</p>
                    <p>armurerie@arsenal-pro.com</p>
                    <p>N° TVA Intracommunautaire : FRZC908677180</p>
                    <p>N° SIRET : 90867718000014</p>
                    <p>Code NAF : 4778C</p>
                    <p>Capital : 10000€</p>
                </div>
            </div><br/><br/>
            
            <div id='produits'>
                <div>
                    <h2>REMBOURSEMENT - Facture " . $nofacture ."</h2>
                    <p>Remboursement de la commande ". $order->getReference() ." effectuée le ". date("d/m/Y") .".</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Référence ID</th>
                                <th>Désignation</th>
                                <th>Qté(s)</th>
                                <th>PU HT</th>
                                <th>TVA</th>
                                <th>Prix TTC</th>
                            </tr>
                        </thead>
                        <tbody>". $lesproduits ."</tbody>
                    </table><br/>
                <div id='prixtotal'>
                    <div>
                    </div>
                    " . $totaux . "
                </div>
                </div>
            </div>
            <div id='footer'>
                <div>
                    <div class='info_entreprise'>
                        <p>Coordonnées bancaires : </p>
                        <p>IBAN : FR76 1680 7000 4437 0966 8521 214 <i>&nbsp;&nbsp;&nbsp;</i>BIC/SWIFT : CCBPFRPPGRE</p>
                    </div>
                </div>
            </div></body>";
        // echo $html;

        $options = new PDFOptions();
        $options->setIsRemoteEnabled(true); //activer la lecture d'images pour PDFOptions()
        $pdf = new Dompdf($options);
        $pdf->loadHtml($html); //conversion html en pdf
        $pdf->setPaper('A4');
        $pdf->render();

        $fichier = $pdf->output(); //rendu fichier
        file_put_contents("./../factures/".date("m-Y"). "/ticket-de-remboursement-". $nofacture. ".pdf", $fichier); //sauvegarde du fichier
        $emplac_fichier = "/var/www/arsenal/factures/". date("m-Y"). "/ticket-de-remboursement-". $nofacture. ".pdf"; //emplacement fichier dans le serveur UNIX
        $nomfichier = "ticket-de-remboursement-". $nofacture. ".pdf";

        // $mail->sendAvecFichierPDF($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre commande ARSENAL PRO a été remboursée", $content, $emplac_fichier, $nomfichier, 4639822);
        // $mail->sendAvecFichierPDF("arsenalpro74@gmail.com", "ARSENAL PRO", "Remboursement de la commande : " . $order->getReference(), $content, $emplac_fichier, $nomfichier,4639500);
        // $mail->sendAvecFichierPDF("armurerie@arsenal-pro.com", "ARSENAL PRO", "Remboursement de la commande : " . $order->getReference(), $content, $emplac_fichier, $nomfichier,4639500);

        return new Response();
    }
}

?>