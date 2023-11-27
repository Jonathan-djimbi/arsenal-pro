<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\BourseArmes;
use App\Entity\CartDatabase;
use App\Entity\CarteFidelite;
use App\Entity\CodePromo;
use App\Entity\HistoriqueCodePromo;
use App\Entity\HistoriqueReservation;
use App\Entity\Order;
use App\Entity\PointFidelite;
use App\Entity\Produit;
use App\Entity\ReservationActivite;
use App\Entity\User;
use App\Service\FluctuationBourseArmesService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options as PDFOptions;

class OrderValidateController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FluctuationBourseArmesService $bourseArmes;
    private CarteCadeauController $carteCadeau;
    
    public function __construct(EntityManagerInterface $entityManager, FluctuationBourseArmesService $bourseArmes, CarteCadeauController $carteCadeau)
    {
        $this->entityManager = $entityManager;
        $this->bourseArmes = $bourseArmes;
        $this->carteCadeau = $carteCadeau;
    }

    public function checkHash($data, $key)
    {
        $supported_sign_algos = array('sha256_hmac');
        if (!in_array($data['kr-hash-algorithm'], $supported_sign_algos)) {
            return false;
        }
        $kr_answer = str_replace('\/', '/', $data['kr-answer']);
        $hash = hash_hmac('sha256', $kr_answer, $key);
        return ($hash == $data['kr-hash']);
    }


    public function checkOrderStatus($reference){
        $URL = "https://arsenal-pro.fr";
        $valid = true;
        if (!$this->checkHash($_POST, $_ENV['EPAY_SHA256KEY'])){ //verif si clé sha256 corresponde à la requête de paiement
            // echo 'Invalid signature. <br />';
            // echo '<pre>' . print_r($_POST, true) . '</pre>';
            $valid = false;
            // die(); //si clé non correspondante alors on print un log
        }
        //sinon
        $answer = array();
        $answer['kr-hash'] = $_POST['kr-hash'];
        $answer['kr-hash-algorithm'] = $_POST['kr-hash-algorithm'];
        $answer['kr-answer-type'] = $_POST['kr-answer-type'];
        $answer['kr-answer'] = json_decode($_POST['kr-answer'], true);
        // dd($answer['kr-answer']);
        if(!$valid || $answer['kr-answer']['orderStatus'] == "UNPAID" || $answer['kr-answer']['orderStatus'] == "ABANDONED"){ //SI CLE NON VALIDE OU COMMANDE NON PAYEE OU ABANDONNEE
            //envoie mail pour alerter
            $mail = new Mail();
            $subject = "Paiement NON PAYÉ : " . $reference;
            $content = "<div>
                <strong style='color: red;'>Le paiement pour " . $reference . " n'est pas passé.</strong><br/>
                <p>Ce paiement sera enregistré depuis ce lien <a href='" . $URL."/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5COrder2CrudController&menuIndex=&submenuIndex=&page=1&query=" . $reference ."'>" . $reference ."</a></p>
            </div>";
            $mail->send("arsenalpro74@gmail.com", "Arsenal Pro Armurerie", $subject, $content, 4639500);
            $mail->send("armurerie@arsenal-pro.com", "Arsenal Pro Armurerie", $subject, $content, 4639500);
            //retourne vers page refuse
            return $this->redirectToRoute('app_systempay_refused', ['reference' => $reference]); 
        }
    }

    #[Route('/systempay/merci/{reference}', name: 'app_order_validate')]
    public function index(Cart $cart, $reference): Response
    {     
        $this->checkOrderStatus($reference);

        $contentproduct = '';
        $contentArticleCommande = '';
        $htmlPrixTotal = '';
        $URL = "https://arsenal-pro.fr";
        $prixTotalPourMail = 0;
        $prix_total_pour_promo = 0;
        $prix_total_pour_degressif = 0; //uniquement utilisé pour les points fidélité
        $catB_detection = [];
        $carteCadeau_detection = [];
        $masse = 1;
        $dateeffectue = new \DateTimeImmutable();

        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($reference);
        $acheteur = $this->entityManager->getRepository(Adress::class)->findBy(["id" => $order->getDelivry()]); //adresse livraison
        $adresseBase = $this->entityManager->getRepository(Adress::class)->findBy(["user" => $this->getUser()]); //adresse de base (la première adresse du compte)
        // dd($adresseBase[0]->getCountry(), $acheteur[0]);
        $promo = $this->entityManager->getRepository(CodePromo::class)->findOneBy(['code' => $order->getPromo()]);
        if(!$order|| $order->getUser() != $this->getUser()){
            return $this->redirectToRoute('home');
        }
        $nbAchatFidelite = $this->entityManager->getRepository(CarteFidelite::class)->findOneBy(['user' => $order->getUser()]); //uniquement pour trouver nombreAchat effectué durant fidelite
        $fidelite = $this->entityManager->getRepository(CarteFidelite::class)->findBy(["user" => $order->getUser()]); //on pouvait faire un findOneBy mais les conditions n'auront pas fonctionné (if)
        $point_fidelite = $this->entityManager->getRepository(PointFidelite::class)->findAll(); //parametre en point 

        //numero commande et numero facturation
        $facturecount = 1; //on commence par 1001 pour la facture
        $countMonth = intval(date('m'));
        $annee = intval(date('Y'));
        if(!is_dir("./../factures/". date("m-Y"). "")){ //dossier mois-année
            mkdir("./../factures/". date("m-Y"). "");
        } else {
            for($i = 1; $i < $countMonth+1; $i++){
                if($i < 10){
                    $facturecount = $facturecount + count(glob("./../factures/" .'0'. $i . '-' . $annee . "/*")); //compte combien de factures sont dans le dossier
                    // dd(glob("./../factures/" .'0'. $i . '-' . $annee . "/*"));
                } else {
                    $facturecount = $facturecount + count(glob("./../factures/". $i . '-' . $annee . "/*")); //compte combien de factures sont dans le dossier
                }
            }
            $facturecount = $facturecount + 1; //final count
        }
        if($facturecount < 999){
            $nofacture = "S" . substr(date("Y"),2,4) ."-". date("m-d"). "-1" .  str_pad($facturecount,3,0,STR_PAD_LEFT);
        } else { //si supérieur à 999, commme à 1000, 1001, etc.
            $nofacture = "S" . substr(date("Y"),2,4) ."-". date("m-d"). "-" .  (1000 + $facturecount);
        }
        //


        $orderquantite = $this->entityManager->getRepository(Order::class)->findOneByReference($order->getReference());
        $isVenteFlash = false;
            foreach ($orderquantite->getOrderDetails()->getValues() as $produit) { // mise à jour du nombre de stock à la fin du paiement
                // $product_object = $this->entityManager->getRepository(Produit::class)->findOneByName($produit->getProduct()); //FAILLE SI UN PRODUIT QUI A LE MEME QUE L'AUTRE ALORS PROBLEME
                $product_object = $this->entityManager->getRepository(Produit::class)->findOneById($produit->getPid()->getId()); //chercher le produit du orderDetails par produit ID

                $siBourseArmes = $this->entityManager->getRepository(BourseArmes::class)->findOneByPid($product_object); //check si produit bourse en armes
                if($siBourseArmes){ //si bourse aux armes alors on met à jour le prix du produit pour le faire évoluer
                    $this->bourseArmes->updatePrixUneArme($siBourseArmes, $produit->getQuantity()); 
                }
                if(!$isVenteFlash && $product_object->isVenteFlash()){
                    $isVenteFlash = true;
                }
                $masse = $masse + ($product_object->getMasse() * $produit->getQuantity()); //poids produit
                $prixTotalPourMail = $prixTotalPourMail + ($produit->getPrice() * $produit->getQuantity());
                $contentArticleCommande .= "<div style='display: flex;'>
                    <div style='margin-right: 10px;'><img width='100' height='100' style='object-fit: contain; height: 100px !important;' src='". $URL ."/uploads/". $product_object->getIllustration()."' /></div>
                    <div style='margin: auto 10px; width: 100%;'>
                        <b>". $product_object->getName() ."</b>
                        <p style='font-weight: normal;'>Qté(s) : ". $produit->getQuantity() ."</p>
                    </div>
                    <div style='width: 100%; margin-top: 15px;'>
                        <b>". number_format(($produit->getPrice() * $produit->getQuantity()) / 100, 2) ."€</b>
                    </div>
                </div><hr style='border: solid 1px #00000044'>";

                if($promo){ //si code promo on affecte la valeur prix_total_pour_promo, grace a ça on peut stocker les prix non remisé pour la facture, vu que prix_total prend les prix remises
                    if($produit->getTotalOriginIfPromo() > 0 && $produit->getTotalOriginIfPromo()){
                        $prix_total_pour_promo = $prix_total_pour_promo + ($produit->getTotalOriginIfPromo());
                    } else {
                        $prix_total_pour_promo = $prix_total_pour_promo + ($produit->getTotal());
                    }
                }
                if($product_object->getIsDegressif()){ //compte prix pour produit degressif
                    $prix_total_pour_degressif = $prix_total_pour_degressif + ($produit->getTotal()); //variable uniquement utilisé pour déduire les points de fidélité à gagner
                }
                if($product_object->getCategory()->getId() === 2){ //si arme de cat B
                    $catB_detection[] = ["id" => $product_object->getId(), "masse" => ($product_object->getMasse() * $produit->getQuantity()), "prix" => ($produit->getPrice() * $produit->getQuantity())]; //produit->getPrice()! !!!!
                }
                if($product_object->isIsCarteCadeau()){ //si carte cadeau
                    $carteCadeau_detection[] = ["id" => $product_object->getId(), "prix" => ($produit->getPrice() * $produit->getQuantity())]; 
                }
                // dd($product_object);  
                if($product_object->getQuantite() > 0 && $product_object->getCategory()->getId() !== 7){ //Maj quantité stock (à part pour les prestation)
                    if($produit->getQuantity() < $product_object->getQuantite()){
                        $product_object->setQuantite($product_object->getQuantite() - $produit->getQuantity()); //quantity = quantité ajouté du panier
                    } else {
                        if($produit->getQuantity() > $product_object->getQuantite() || $produit->getQuantity() == $product_object->getQuantite()){ //si quantite dispo inférieur aux quantité acheté OU si les deux sont égales
                            $product_object->setQuantite($product_object->getQuantite() - $product_object->getQuantite()); //quantité maximal disponible

                            $contentproduct .= "<a style='font-weight: normal;' href='" . $URL. "/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CProduitCrudController&menuIndex=6&query= " . $product_object->getId() . "'>" . $product_object->getName() . "</a>
                            <p style='font-weight: normal;'>Référence produit : ". $product_object->getId() . "</p><br/>"; //récup liste de produit en pré-commande
                        }
                    }
                }
                // dd($product_object);
        }
        // dd($masse);
        if (count($fidelite) > 0 && $promo == null){ //si fidele et pas DE CODE PROMO | si code promo alors on n'attribue pas de points, ni en dépenser
            if($order->getPointFideliteUtilise() !== null && $order->getPointFideliteUtilise() > 0){ //si une valeur PointFidelite existe, ça veut dire on a utilisé nos points fideles
                if($fidelite[0]->getPoints() >= ($order->getPointFideliteUtilise()/10)){ //s'il y a assez de points alors on peut enlever des points sur notre compte
                    $fidelite[0]->setPoints($fidelite[0]->getPoints() - ($order->getPointFideliteUtilise()/10)); //dépenser
                }
                $this->entityManager->flush();
            }
            else if($order->getTotal() >= $point_fidelite[0]->getMontantPanier() && !$isVenteFlash){ //si total de la commande est superieure ou egale au montant panier fixe de la fidelite (et pas de points de fidélité utilisés)
                $iterationMontantPanier = number_format(($order->getTotal() - $prix_total_pour_degressif) / ($point_fidelite[0]->getMontantPanier()),0,",",""); //floor = prendre le integer le plus petit pour eviter les nombre comme 1.33
                $fidelite[0]->setPoints($fidelite[0]->getPoints() + ($iterationMontantPanier * $point_fidelite[0]->getPoint()));

                if ($nbAchatFidelite->getNombreAchat() >= 2 && $nbAchatFidelite->getNombreAchat() < 6){ //Niveau 1 fidelite
                    $fidelite[0]->setPoints($fidelite[0]->getPoints() + 5); //+5 points bonus
                }
                if ($nbAchatFidelite->getNombreAchat()  >= 6 && $nbAchatFidelite->getNombreAchat() < 12){ //Niveau 2 fidelite
                    $fidelite[0]->setPoints($fidelite[0]->getPoints() + 10); //+10 points bonus
                } 
                if ($nbAchatFidelite->getNombreAchat()  >= 12){ //Niveau 3 fidelite
                    $fidelite[0]->setPoints($fidelite[0]->getPoints() + 15); //+15 points bonus
                }
            }
        }
        if(count($fidelite) > 0){ //si utilisateur fidele (tous les comptes récents sont fidele)
            $fidelite[0]->setNombreAchat($fidelite[0]->getNombreAchat() + 1); //nombre d'achat
            $fidelite[0]->setDernierAchat($dateeffectue); //pour compte client/fidelite, on recupère la dernière fois que le client à fait sa commande
            if($order->getMontantCompteUtilise() !== null && $order->getMontantCompteUtilise() > 0){
                //ici CHECK SI PAS 0 MONTANT bientôt
                $fidelite[0]->setSommeCompte($fidelite[0]->getSommeCompte() - $order->getMontantCompteUtilise());
            }
        }

        if($promo){
            $codehistoriqueCheck = $this->entityManager->getRepository(HistoriqueCodePromo::class)->findOneBy(['client' => $this->getUser(), 'codePromo' => $promo]);
            $codeutilise = new HistoriqueCodePromo(); //création de données pour savoir qui a utilisé le code promo et quand
            $promo->setUtilisation($promo->getUtilisation() + 1); //màj nombre utilisation code
            if($codehistoriqueCheck){ //si code promo détecté est déjà été utilisé par le user
                $codehistoriqueCheck->setNbUtilisationUser($codehistoriqueCheck->getNbUtilisationUser() + 1); //nombre utilisation du code pour user
            } else { //si jamais utilisé
                $codeutilise->setNbUtilisationUser(1);
                $codeutilise->setClient($this->getUser());
                $codeutilise->setCodePromo($promo);
                $codeutilise->setDate($dateeffectue);
                $this->entityManager->persist($codeutilise);
            }
            
        }
        // dd($catB_detection);
        if ($order->getState() == 0) {
            // Vider la session "cart"
            $cart->remove();

            // Modifier le statut de notre commande en mettant 1 pour dire que c'est payée
            $order->setState(1);

            $this->entityManager->flush();

            $occasion = ($product_object->getIsOccassion() ) ? 1 : 0;
            // dd($occasion);
            $mail = new Mail();  // Envoyer un email à notre client pour lui confirmer sa commande
            if($prix_total_pour_promo > 0 && $promo){ //modifier la mise en forme sous-total du mail

                //o
                $htmlPrixTotal =  "<p>Sous-total : " . str_replace(",","'", number_format($prix_total_pour_promo / 100, 2)) . "€</p>
                <p>Remise code promo : ". $promo->getCode() . " -" . $promo->getPourcentage() . "%</p>
                <p>Nouveau sous-total : " . str_replace(",","'", number_format($prixTotalPourMail / 100, 2)) . "€</p>
                <p>Livraison : " . number_format($order->getCarrierPrice() /100, 2) . "€</p>
                <p>TVA : " . str_replace(",","'", number_format((($prixTotalPourMail) - ($prixTotalPourMail / 1.2)) / 100, 3)) . "€</p>
                <b>Total TTC : ". str_replace(",","'", number_format(($prixTotalPourMail + $order->getCarrierPrice()) / 100, 2)) ."€</b>";
            } else {
                $htmlPrixTotal = "<p>Sous-total : " . str_replace(",","'", number_format($prixTotalPourMail / 100, 2)) . "€</p>
                <p>Livraison : " . number_format($order->getCarrierPrice() /100, 2) . "€</p>
                <p>TVA : " . str_replace(",","'", number_format((($prixTotalPourMail) - ($prixTotalPourMail / 1.2)) / 100, 3)) . "€</p>
                <b>Total TTC : ". str_replace(",","'", number_format(($prixTotalPourMail + $order->getCarrierPrice()) / 100, 2)) ."€</b>";
            }

            $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
            <div>
            <h2 style='text-align: center; font-weight: normal;'>ARSENAL PRO vous remercie de votre achat</h2>
            <h4 style='font-weight: normal; font-size: 1.04em;'>Bonjour ". $order->getUser()->getFirstname() ." ". $order->getUser()->getLastname() ." !<br><br>Votre commande n°" . $order->getReference() . " a été confirmée et en cours de préparation.<br>Si vous avez acheté plus d'un produit, votre commande sera expédiée lorsque tous les articles seront disponibles.</h4>
            <div style='text-align: center; background-color: #07af15; width: 200px; padding: 12px; font-size: 1.1em; margin: auto;'><a href='https://arsenal-pro.fr/compte/mes-commandes/" . $order->getReference() . "' style='color: white; text-decoration: none;'>VÉRIFIEZ LE STATUT DE VOTRE COMMANDE</a></div> 
            </div>
            <br/>
            <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Details de la commande</h2>
            <div style='display: flex; justify-content: center;'>
                <div style='width: 100%;'>
                    <p style='font-weight: normal;'>Numéro de commande :<br>" . $order->getReference() . "</p>
                    <p style='font-weight: normal;'>Date de commande : " . date("d/m/Y") . "</p>
                    <p style='font-weight: normal;'>Méthode de paiement : CB</p>
                </div>
                <div style='width: 100%; line-height: 0.5;'>
                    <h3 style='font-weight: bold; line-height: 1;'>Adresse de facturation</h3>
                    <p style='font-weight: normal;'>".$order->getUser()->getFirstname()." ". $order->getUser()->getLastname() . "</p>
                    <p style='font-weight: normal;'>". $adresseBase[0]->getAdress() ."</p>
                    <p style='font-weight: normal;'>". $adresseBase[0]->getPostal() . " " . $adresseBase[0]->getCity() ."</p>
                    <p style='font-weight: normal;'>Tél : ". $adresseBase[0]->getPhone() ."</p>
                    <br/>
                    <h3 style='font-weight: bold; line-height: 1;'>Adresse de livraison</h3>
                    <p style='font-weight: normal;'>". $order->getUser()->getFirstname()." ". $order->getUser()->getLastname() . "</p>
                    <p style='font-weight: normal;'>". $acheteur[0]->getAdress() ."</p>
                    <p style='font-weight: normal;'>". $acheteur[0]->getPostal() . " " . $acheteur[0]->getCity() ."</p>
                    <p style='font-weight: normal;'>Tél : ". $acheteur[0]->getPhone() ."</p>
                </div>
            </div>
            <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Article(s) commandé(s)</h2>
            <div>" . $contentArticleCommande ."
            <div style='text-align: right; right: 0; float: right; font-weight: normal;'>"
                . $htmlPrixTotal .
            "</div>
            </div>
            </section></section>";

            $content_vendeur = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
            <div>
            <h2 style='text-align: center; font-weight: normal; color: #5D7F59;'>Achat effectué dans le site !</h2>
            <h4 style='font-weight: normal; font-size: 1.04em;'>Bonjour ". $order->getUser()->getFirstname() ." ". $order->getUser()->getLastname() ." !<br><br>La commande n°" . $order->getReference() . " a été confirmée et en cours de préparation.</h4>
            <div style='text-align: center; background-color: #07af15; width: 200px; padding: 12px; font-size: 1.1em; margin: auto;'><a style='color: white; text-decoration: none;' href='https://arsenal-pro.fr/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5COrderCrudController&submenuIndex=-1&page=1&query=" . $order->getReference() ."'>Consulter la commande du client</a></div> 
            </div>
            <br/>
            <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Details de la commande</h2>
            <div style='display: flex; justify-content: center;'>
                <div style='width: 100%;'>
                    <p style='font-weight: normal;'>Numéro de commande :<br>" . $order->getReference() . "</p>
                    <p style='font-weight: normal;'>Date de commande : " . date("d/m/Y") . "</p>
                    <p style='font-weight: normal;'>Méthode de paiement : CB</p>
                </div>
                <div style='width: 100%; line-height: 0.5;'>
                    <h3 style='font-weight: bold; line-height: 1;'>Adresse de facturation</h3>
                    <p style='font-weight: normal;'>".$order->getUser()->getFirstname()." ". $order->getUser()->getLastname() . "</p>
                    <p style='font-weight: normal;'>". $adresseBase[0]->getAdress() ."</p>
                    <p style='font-weight: normal;'>". $adresseBase[0]->getPostal() . " " . $adresseBase[0]->getCity() ."</p>
                    <p style='font-weight: normal;'>Tél : ". $adresseBase[0]->getPhone() ."</p>
                    <br/>
                    <h3 style='font-weight: bold; line-height: 1;'>Adresse de livraison</h3>
                    <p style='font-weight: normal;'>". $order->getUser()->getFirstname()." ". $order->getUser()->getLastname() . "</p>
                    <p style='font-weight: normal;'>". $acheteur[0]->getAdress() ."</p>
                    <p style='font-weight: normal;'>". $acheteur[0]->getPostal() . " " . $acheteur[0]->getCity() ."</p>
                    <p style='font-weight: normal;'>Tél : ". $acheteur[0]->getPhone() ."</p>
                </div>
            </div>
            <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Article(s) commandé(s)</h2>
            <div>" . $contentArticleCommande ."
            <div style='text-align: right; right: 0; float: right; font-weight: normal;'>"
                . $htmlPrixTotal .
            "</div>
            </div>
            </section></section>";

            $this->factureCommande($order, $acheteur[0], $mail, $content, $content_vendeur, $nofacture);
            // dd($masse);
            if($order->getCarrierName() == "COLISSIMO"){
                //
                if(count($catB_detection) > 0){ //si produit(s) catégorie B détecté(s)
                    $prixTotalCatB = 0;
                    $masseTotalCatB = 0;
                    foreach($catB_detection as $product){
                        $masseTotalCatB = $masseTotalCatB + $product["masse"]; //$masseTotalCatB++ $product->getMasse();
                        $prixTotalCatB = $prixTotalCatB + $product["prix"]; //$masseTotalCatB++ $product->getMasse();

                    }
                    $masseTotalCatB = ($masseTotalCatB * 0.15); //15% de la masse cat B;
                    if($masseTotalCatB > 0){
                        $this->generateTicketColissimo($order, $acheteur[0], $mail, $masseTotalCatB, $nofacture . "-CATB", $prixTotalCatB);
                        // -
                    }
                }
                $this->generateTicketColissimo($order, $acheteur[0], $mail, $masse, $nofacture, $prixTotalPourMail);
            }
            if(count($carteCadeau_detection) > 0 && $carteCadeau_detection !== null){
                $this->carteCadeau->generator($carteCadeau_detection, $nofacture, $order);
            }

            //envoi de mél à nous même, admin, pour alerter
            if(!empty($contentproduct)){ //si produit(s) en précommande
                $contenttwo = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                <div>
                <strong>Vous avez un ou plusieurs produits qui doivent être approvisionnés, voici la liste : </strong>
                <h3 style='font-weight: normal;'>". $contentproduct ."</h3>
                </div>
                </section></section>";

                    $mail->send("arsenalpro74@gmail.com", "ARSENAL PRO", "Approvisionnement à prévoir", $contenttwo, 4639500);
                    $mail->send("armurerie@arsenal-pro.com", "ARSENAL PRO", "Approvisionnement à prévoir", $contenttwo, 4639500);
                }    
        }

        // $cartbase = $this->entityManager->getRepository(CartDatabase::class)->findOneByUser($this->getUser());
        // if($cartbase){
        //     if(count($cartbase->getCart()) > 0){
        //         $cartbase->setCart([]); //on vide quand c'est acheté
        //         $cartbase->setRelance(true); //pour pas relancer l'utilisateur s'il a déjà acheté
        //         $this->entityManager->flush();
        //     }
        // }
        return $this->redirectToRoute('app_order_confirmation', ['reference' => $reference]);
    }

    #[Route('/commande-confirmee/merci/{reference}', name: 'app_order_confirmation')]
    public function pageValidationOrder($reference){ //affichage de la page de confirmation de la commande après avoir payée
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($reference); //SessionId = commande payée
        if($order){
            if($order->getUser() == $this->getUser()){
                $acheteur = $this->entityManager->getRepository(Adress::class)->findOneById($order->getDelivry()); //adresse livraison

                return $this->render('order_validate/index.html.twig',[
                    'order' => $order,
                    'acheteur' => $acheteur
                ]);
            } else { //si order n'appartient pas au client alors il est redirigé vers la page : compte liste des commandes
                return $this->redirectToRoute('app_account_order');
            }
        } else { //si inexisante alors on redirige
            return $this->redirectToRoute('app_account');
        }

    }
    
    public function generateTicketColissimo($order, $acheteur, $mail, $masse, $nofacture, $prixCommandeTotal){ //pour générer un ticket de livraison vers le service colissimo
        // $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId("22-12-07-Dilmac-5-63909f22d0bd4-R");
        // $rey = $this->entityManager->getRepository(Adress::class)->findBy(["id" => $order->getDelivry()]);
        // $acheteur = $rey[0];
        $httpClient = HttpClient::create();
        $insuranceValue = 0;
        //pallier prix assurance
        if($prixCommandeTotal >= 15000 && $prixCommandeTotal < 25000){ //supérieur ou égal à 150 et inférieur à 250
            $insuranceValue = 15000;
        }
        if($prixCommandeTotal >= 25000 && $prixCommandeTotal < 40000){ //supérieur ou égal à 250 et inférieur à 400
            $insuranceValue = 30000;
        }
        if($prixCommandeTotal >= 40000 && $prixCommandeTotal < 75000){
            $insuranceValue = 50000;
        }
        if($prixCommandeTotal >= 75000 && $prixCommandeTotal < 175000){
            $insuranceValue = 100000;
        }
        if($prixCommandeTotal >= 175000 && $prixCommandeTotal < 400000){
            $insuranceValue = 200000;
        }
        if($prixCommandeTotal >= 400000) {
            $insuranceValue = 500000;
        }
        $ticket = [
          "contractNumber" => "443747", 
          "password" => "ArsenalPro23+", 
          "outputFormat" => [
                "x" => 0, 
                "y" => 0, 
                "outputPrintingType" => "PDF_A4_300dpi"
             ], 
          "letter" => [
                   "service" => [
                      "productCode" => "DOS", //A2P = point relais
                      "depositDate" => date("Y-m-d"), 
                      "orderNumber" => $order->getReference(), 
                      "commercialName" => "ARSENAL PRO" 
                   ], 
                   "parcel" => [
                         "weight" => $masse, 
                         "insuranceValue" => $insuranceValue,
                        //  "pickupLocationId" => "001055" //point relais
                      ], 
                   "sender" => [
                            "senderParcelRef" => "senderParcelRef", 
                            "address" => [
                               "companyName" => "ARSENAL Pro", 
                               "line0" => "", 
                               "line1" => "", 
                               "line2" => "710 Rue du Léman, C2a", 
                               "line3" => "", 
                               "countryCode" => "FR", 
                               "city" => "Chens-sur-Léman", 
                               "zipCode" => "74140" ,
                               "email" => "armurerie@arsenal-pro.com"
                            ] 
                         ], 
                   "addressee" => [
                                  "addresseeParcelRef" => "addresseeParcelRef", 
                                  "address" => [
                                     "lastName" => $order->getUser()->getLastname(), 
                                     "firstName" => $order->getUser()->getFirstname(), 
                                     "line0" => "", 
                                     "line1" => "", 
                                     "line2" => $acheteur->getAdress(), 
                                     "line3" => "", 
                                     "countryCode" => $acheteur->getCountry(), 
                                     "city" => $acheteur->getCity(), 
                                     "zipCode" => $acheteur->getPostal(), 
                                     "phoneNumber" => $acheteur->getPhone(), 
                                     "email" => $order->getUser()->getEmail() 
                                  ] 
                        ] 
                ] 
       ]; 
        
        $response = $httpClient->request('POST','https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0/generateLabel', [
            'headers' => [
                "Content-Type" => "application/json;charset=UTF-8",
            ],
            'body' => json_encode($ticket, true),
        ]);
        if(!is_dir("./../colissimo/". date("d-m-Y"). "")){ //si dossier date non existante
            mkdir("./../colissimo/". date("d-m-Y"). ""); //création dossier date pour factures/
        }
        header("Content-type: application/octet-stream"); //conversion de la réponse en application/octet-stream
        header("Content-Type: application/pdf"); //puis conversion forcé en pdf
        $emplac_fichier = "/var/www/arsenal/colissimo/". date("d-m-Y"). "/colissimo-" . $nofacture . ".pdf"; //emplacement fichier dans le serveur UNIX
        $nomfichier = "colissimo-" .$nofacture . ".pdf";
        file_put_contents("./../colissimo/". date("d-m-Y"). "/colissimo-" . $nofacture . ".pdf", $response->getContent()); //nouveau pdf généré

        $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='text-align: center; font-weight: normal;'>NOUVEAU! <b>LABEL COLISSIMO</b> pour la commande n°" . $order->getReference() . " effectuée chez Arsenal Pro !</h2>
        <h3 style='font-weight: normal;'>Ce colissimo est destiné pour le client ". $order->getUser()->getFirstname() ." ". $order->getUser()->getLastname() ." !</h3>
        </div>
        </section></section>";

        $mail->sendAvecFichierPDF("armurerie@arsenal-pro.com", "ARSENAL PRO", "Label colissimo : " . $order->getReference() ."", $content, $emplac_fichier, $nomfichier, 4639500);
        $mail->sendAvecFichierPDF("arsenalpro74@gmail.com", "ARSENAL PRO", "Label colissimo : " . $order->getReference() ."", $content, $emplac_fichier, $nomfichier, 4639500);

        return new Response();
    } 

    ///COLISSIMO POUR TEST UNIQUEMENT QUAND EN PHASE DEV///


    // #[Route('/livraison/test-colis', name: 'colissimo')]
    // public function generateTicketColissimo(){ //pour générer un ticket de livraison vers le service colissimo
    //     $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId("S23-03-20-1008-6991-L");
    //     $rey = $this->entityManager->getRepository(Adress::class)->findBy(["id" => $order->getDelivry()]);
    //     $acheteur = $rey[0];
    //     $httpClient = HttpClient::create();
    //     $prixCommandeTotal = 800*100;
    //     $insuranceValue = 0;
        
    //     if($prixCommandeTotal >= 15000 && $prixCommandeTotal < 25000){
    //         $insuranceValue = 15000;
    //     }
    //     if($prixCommandeTotal >= 25000 && $prixCommandeTotal < 40000){
    //         $insuranceValue = 30000;
    //     }
    //     if($prixCommandeTotal >= 40000 && $prixCommandeTotal < 75000){
    //         $insuranceValue = 50000;
    //     }
    //     if($prixCommandeTotal >= 75000 && $prixCommandeTotal < 175000){
    //         $insuranceValue = 100000;
    //     }
    //     if($prixCommandeTotal >= 175000 && $prixCommandeTotal < 400000){
    //         $insuranceValue = 200000;
    //     }
    //     if($prixCommandeTotal >= 400000) {
    //         $insuranceValue = 500000;
    //     }
    //     // dd($insuranceValue/100);
    //     $ticket = [
    //       "contractNumber" => "443747", 
    //       "password" => "ArsenalPro23+", 
    //       "outputFormat" => [
    //             "x" => 0, 
    //             "y" => 0, 
    //             "outputPrintingType" => "PDF_A4_300dpi"
    //          ], 
    //       "letter" => [
    //                "service" => [
    //                   "productCode" => "DOS", //A2P = point relais
    //                   "depositDate" => date("Y-m-d"), 
    //                   "orderNumber" => $order->getReference(), 
    //                 //   "commercialName" => "ARSENAL PRO" 
    //                ], 
    //                "parcel" => [
    //                      "weight" => 0.5, 
    //                      "insuranceValue" => $insuranceValue,
    //                     //  "pickupLocationId" => "001055" //point relais
    //                   ], 
    //                "sender" => [
    //                         "senderParcelRef" => $order->getReference(), 
    //                         "address" => [
    //                            "lastName" => "Arsenalus", 
    //                            "firstName" => "", 
    //                            "line0" => "", 
    //                            "line1" => "", 
    //                            "line2" => "2 Rue Charles de Gaulle", 
    //                            "line3" => "", 
    //                            "countryCode" => "FR", 
    //                            "city" => "Riom", 
    //                            "zipCode" => rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . "0",
    //                            "email" => ""
    //                         ] 
    //                      ], 
    //                "addressee" => [
    //                               "addresseeParcelRef" => $order->getReference(), 
    //                               "address" => [
    //                                  "lastName" => $order->getUser()->getLastname(), 
    //                                  "firstName" => $order->getUser()->getFirstname(), 
    //                                  "line0" => "", 
    //                                  "line1" => "", 
    //                                  "line2" => $acheteur->getAdress(), 
    //                                  "line3" => "", 
    //                                  "countryCode" => "FR", 
    //                                  "city" => $acheteur->getCity(), 
    //                                  "zipCode" => $acheteur->getPostal(), 
    //                                  "mobileNumber" => $acheteur->getPhone(), 
    //                                  "email" => $order->getUser()->getEmail() 
    //                               ] 
    //                     ] 
    //             ] 
    //    ]; 
        
    //     $response = $httpClient->request('POST','https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0/generateLabel', [
    //         'headers' => [
    //             "Content-Type" => "application/json;charset=UTF-8",
    //         ],
    //         'body' => json_encode($ticket, true),
    //     ]);
    //     if(!is_dir("./../colissimo/". date("d-m-Y"). "")){ //si dossier date non existante
    //         mkdir("./../colissimo/". date("d-m-Y"). ""); //création dossier date pour factures/
    //     }
    //     header("Content-type: application/octet-stream"); //conversion de la réponse en application/octet-stream
    //     header("Content-Type: application/pdf"); //puis conversion forcé en pdf
    //     return new Response();
    // } 


    #[Route('/factur111/test', name: 'facture')]
    public function factureCommande($order,$adresse, $mail, $content, $content_vendeur, $nofacture){ //pour générer une facture. Pour produits et prestations
    // public function factureCommande(){ //pour générer une facture
    //     // $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId("23-01-09-Dilmac-5-63bc2f4f8061f-R"); //voir FLOOR pour 23-01-09-Zemmour-Gitton-8-63bc308c47353-R
    //     $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId("S23-05-05-1078-OCULY-R-142d");
    //     $rey = $this->entityManager->getRepository(Adress::class)->findBy(["id" => $order->getDelivry()]);
    //     $adresse = $rey[0];
    //     $nofacture = "S23-05-05-1078-142d";
        $lesproduits = "";
        $totaux = "";
        $livraison = "";
        $promoCode = "";
        // $sommeCompte = "";
        $prix_total = 0;
        $prix_total_pour_promo = 0;
        $promo = $this->entityManager->getRepository(CodePromo::class)->findOneBy(['code' => $order->getPromo()]);
        foreach ($order->getOrderDetails()->getValues() as $liste){
            $textPrecommande = "";
            if($liste->getPid()->getQuantite() === 0){ //si produit avec 0 stock dispo BDD
                $textPrecommande = "<br/>(Précommande avec délai de livraison)";
            }
            $lesproduits .= "<tr>
                <td>". $liste->getPid()->getId() ."</td>
                <td>". $liste->getProduct(). " " . $textPrecommande ."</td>
                <td>". $liste->getQuantity() ."</td>
                <td>". str_replace(",","'", number_format(($liste->getPrice() / 100) / 1.2 ,3)) ." €</td>
                <td>". str_replace(",","'",number_format(($liste->getTotal() / 100) - (($liste->getTotal() / 100) / 1.2) ,3)) ." €</td>
                <td>". str_replace(",","'",number_format(($liste->getTotal() / 100) ,2)) ." €</td>
            </tr>";
            $prix_total = $prix_total + ($liste->getTotal() / 100);
            if($promo){ //si code promo on affecte la valeur prix_total_pour_promo, grace a ça on peut stocker les prix non remisé pour la facture, vu que prix_total prend les prix remises
                if($liste->getTotalOriginIfPromo() > 0 && $liste->getTotalOriginIfPromo()){
                    $prix_total_pour_promo = $prix_total_pour_promo + ($liste->getTotalOriginIfPromo() /100);
                } else {
                    $prix_total_pour_promo = $prix_total_pour_promo + ($liste->getTotal() /100);
                }
            }
        };
        if($order->getCarrierPrice() > 0){
            $livraison = "<p>Livraison : ". number_format($order->getCarrierPrice() / 100,2)."€</p>";
            $prix_total = $prix_total + ($order->getCarrierPrice() /100);
        }

        if($promo){
            $prix_ancien = $prix_total_pour_promo;
            if($promo->getMontantRemise() == null && $promo->getPourcentage() !== null && $order->getRemisePromoEuros() == null){ //si pourcentage en promo
                $promoCode = "<p>Remise code promo : ". $promo->getCode() . " -" . $promo->getPourcentage() . "%</p>
                <p>Nouveau Total HT : ". str_replace(",","'",number_format($prix_total /1.2 ,3))."€</p>";
            } 
            if($promo->getMontantRemise() !== null && $promo->getPourcentage() == null && $order->getRemisePromoEuros() !== null){ //si remise euros en promo
                $promoCode = "<p>Remise code promo : ". $promo->getCode() . " -" . number_format($promo->getMontantRemise()/100, 2) . "€</p>
                <p>Nouveau Total HT : ". str_replace(",","'",number_format(($prix_total - ($promo->getMontantRemise() /100) ) /1.2 ,3))."€</p>";
                $prix_total = $prix_total - intval($order->getRemisePromoEuros()/100); //moins remise en euros promo
            } 
        } else {
            $prix_ancien = $prix_total;
        }

        if($order->getPointFideliteUtilise() && $order->getPointFideliteUtilise() > 0 || $order->getMontantCompteUtilise() && $order->getMontantCompteUtilise() > 0){ //si fidele et point fidelite OU argent montant compte utilisé

            $prix_total = $prix_total - ($order->getPointFideliteUtilise()/100) - ($order->getMontantCompteUtilise()/100);
            $totaux = "<div id='prixtotal_remise'>
            <p>Total HT : " .  str_replace(",","'",number_format($prix_ancien / 1.2 ,3)) ."€</p>
            <p>Remise fidélité/compte : -" . str_replace(",","'",number_format(($prix_ancien / 1.2) - ($prix_total / 1.2),3)) . "€</p>
            <p>Total HT remisé : " .  str_replace(",","'",number_format($prix_total / 1.2 ,3)) ."€</p>
            <p>TVA (20%) : " . str_replace(",","'",number_format(($prix_total) - ($prix_total / 1.2) ,3)) . "€</p>
            ". $livraison ."
            <b>Total TTC : ". str_replace(",","'",number_format($prix_total,2)). " €</b></div>";
        } else {
            $totaux = "<div id='prixtotal_nonremise'>
            <p>Total HT : " .  str_replace(",","'",number_format($prix_ancien / 1.2 ,3)) ."€</p>
            " . $promoCode . " 
            <p>TVA (20%) : " . str_replace(",","'",number_format(($prix_total) - ($prix_total / 1.2) ,3)) . "€</p>
            ". $livraison ."
            <b>Total TTC : ". str_replace(",","'",number_format($prix_total,2)). " €</b></div>";
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
                width: 200px;
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
                height : 240px;
                position: relative;
              }
              #prixtotal_nonremise{
                height : 150px;
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
                    <h2>Facture " . $nofacture ."</h2>
                    <p>Recapitulatif pour la commande ". $order->getReference() ." effectuée le ". $order->getCreateAt()->format("d/m/Y") .".</p>
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
        file_put_contents("./../factures/".date("m-Y"). "/facture-". $nofacture. ".pdf", $fichier); //sauvegarde du fichier
        $emplac_fichier = "/var/www/arsenal/factures/". date("m-Y"). "/facture-". $nofacture. ".pdf"; //emplacement fichier dans le serveur UNIX
        $nomfichier = "facture-". $nofacture. ".pdf";
        
        $mail->sendAvecFichierPDF("arsenalpro74@gmail.com", "ARSENAL PRO", "COMMANDE : " . $order->getReference(), $content_vendeur, $emplac_fichier, $nomfichier,4639500);
        $mail->sendAvecFichierPDF("armurerie@arsenal-pro.com", "ARSENAL PRO", "COMMANDE : " . $order->getReference(), $content_vendeur, $emplac_fichier, $nomfichier,4639500);
        $mail->sendAvecFichierPDF($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre commande ARSENAL PRO", $content, $emplac_fichier, $nomfichier, 4639822);

        // $mail->send("arsenalpro74@gmail.com", "ARSENAL PRO", "COMMANDE : " . $order->getReference(), $content_vendeur, 4639500);
        // $mail->send($order->getUser()->getEmail(), "ARSENAL PRO", "COMMANDE : " . $order->getReference(), $content, 4639822);

        return new Response();
    }


    #[Route('/factur-reserv/test', name: 'facture_reserv')]
    public function factureReservation($order,$adresse, $mail, $content, $nofacture){ //pour générer une facture pour locations et formations
        $totaux = "";
        $prix_total = $order->getTotal();
       
        $totaux = "<div id='prixtotal_nonremise'>
        <p>Total HT : " .  str_replace(",","'",number_format(($prix_total/100) / 1.2 ,3)) ."€</p>
        <p>TVA (20%) : " . str_replace(",","'",number_format(($prix_total/100) - (($prix_total/100) / 1.2) ,3)) . "€</p>
        <b>Total TTC : ". str_replace(",","'",number_format($prix_total/100,2)). " €</b></div>";


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
                width: 175px;
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
                height : 220px;
                position: relative;
              }
              #prixtotal_nonremise{
                height : 160px;
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
                    <h2>Facture " . $nofacture ."</h2>
                    <p>Recapitulatif pour la commande ". $order->getReference() ." effectuée le ". date("d/m/Y") .".</p>
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
                        <tbody>
                        <tr>
                            <td>A-". $order->getActivite()->getId() ."</td>
                            <td>". $order->getActiviteName()."</td>
                            <td>". $order->getReservationPourLe()->format('d/m/Y') ."</td>
                            <td>". str_replace(",","'", number_format(($prix_total / 100) / 1.2 ,3)) ." €</td>
                            <td>". str_replace(",","'",number_format(($prix_total / 100) - (($prix_total / 100) / 1.2) ,3)) ." €</td>
                            <td>". str_replace(",","'",number_format(($prix_total / 100) ,2)) ." €</td>
                        </tr>
                        </tbody>
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
        file_put_contents("./../factures/".date("m-Y"). "/facture-". $nofacture. ".pdf", $fichier); //sauvegarde du fichier
        $emplac_fichier = "/var/www/arsenal/factures/". date("m-Y"). "/facture-". $nofacture. ".pdf"; //emplacement fichier dans le serveur UNIX
        $nomfichier = "facture-". $nofacture. ".pdf";
        
        // $mail->sendAvecFichierPDF("arsenalpro74@gmail.com", "ARSENAL PRO", "RESERVATION : " . $order->getReference(), $content, $emplac_fichier, $nomfichier,4639500);
        // $mail->sendAvecFichierPDF("armurerie@arsenal-pro.com", "ARSENAL PRO", "RESERVATION : " . $order->getReference(), $content, $emplac_fichier, $nomfichier,4639500);
        // $mail->sendAvecFichierPDF($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre reservation ARSENAL PRO", $content, $emplac_fichier, $nomfichier, 4639822);


        return new Response();
    }


    #[Route('/systempay/merci-reservation/{reference}', name: 'app_reservation_order_validate')]
    public function reservation($reference): Response
    {     
        $this->checkOrderStatus($reference);
        $URL = "https://arsenal-pro.fr";
        $dateeffectue = new \DateTimeImmutable();

        $order = $this->entityManager->getRepository(HistoriqueReservation::class)->findOneByReference($reference);
        $adresseBase = $this->entityManager->getRepository(Adress::class)->findBy(["user" => $this->getUser()]); //adresse
        if ($order->getState() == 0) {
            $activite = $this->entityManager->getRepository(ReservationActivite::class)->findOneById($order->getActivite());
            $order->setCreateAt($dateeffectue);
            $order->setState(1); //payée
            if($activite->getType() === 0){
                $activite->setIsOccupe(1); //location occupe
            }
            $this->entityManager->flush();
        }
        $mail = new Mail();

        $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='text-align: center; font-weight: normal;'>ARSENAL PRO vous remercie de votre réservation</h2>
        <h4 style='font-weight: normal; font-size: 1.04em;'>Bonjour ". $order->getUser()->getFirstname() ." ". $order->getUser()->getLastname() ." !<br><br>Votre réservation n°" . $order->getReference() . " a été confirmée et en cours de vérification.</h4>
        </div>
        <br/>
        <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Details de la commande</h2>
        <div style='display: flex; justify-content: center;'>
            <div style='width: 100%;'>
                <p style='font-weight: normal;'>Numéro de commande :<br>" . $order->getReference() . "</p>
                <p style='font-weight: normal;'>Date de commande : " . date("d/m/Y") . "</p>
                <p style='font-weight: normal;'>Méthode de paiement : CB</p>
            </div>
            <div style='width: 100%; line-height: 0.5;'>
                <h3 style='font-weight: bold; line-height: 1;'>Adresse de facturation</h3>
                <p style='font-weight: normal;'>".$order->getUser()->getFirstname()." ". $order->getUser()->getLastname() . "</p>
                <p style='font-weight: normal;'>". $adresseBase[0]->getAdress() ."</p>
                <p style='font-weight: normal;'>". $adresseBase[0]->getPostal() . " " . $adresseBase[0]->getCity() ."</p>
                <p style='font-weight: normal;'>Tél : ". $adresseBase[0]->getPhone() ."</p>
                <br/>
            </div>
        </div>
        <h2 style='font-weight: normal; text-align: center; color: #5D7F59;'>Vous avez réservé</h2>
        <div>
        <div style='display: flex;'>
            <div style='margin-right: 10px;'><img width='100' height='100' style='object-fit: contain; height: 100px !important;' src='". $URL ."/uploads/". $order->getActivite()->getImage()."' /></div>
            <div style='margin: auto 10px; width: 100%;'>
                <b>". $order->getActiviteName() ."</b>
                <p style='font-weight: normal;'>Réservé pour le : ". $order->getReservationPourLe()->format('d/m/Y') ."</p>
            </div>
        </div><hr style='border: solid 1px #00000044'>

        <div style='text-align: right; right: 0; float: right; font-weight: normal;'>
            <p>Total HT : " .  str_replace(",","'",number_format(($order->getTotal()/100) / 1.2 ,3)) ."€</p>
            <p>TVA (20%) : " . str_replace(",","'",number_format(($order->getTotal()/100) - (($order->getTotal()/100) / 1.2) ,3)) . "€</p>
            <b>Total TTC : ". str_replace(",","'",number_format(($order->getTotal()/100),2)). " €</b></div>
        </div>
        </div>
        </section></section>";

        $this->factureReservation($order, $adresseBase[0], $mail, $content, $reference);

        // $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre réservation ARSENAL PRO", $content, 4639822);

        return $this->render('order_validate/reservation/index.html.twig',[
            'order' => $order,
            'acheteur' => $adresseBase
        ]);
    }
}
