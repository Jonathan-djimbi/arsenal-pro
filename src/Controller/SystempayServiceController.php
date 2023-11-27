<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\CodePromo;
use App\Entity\HistoriqueReservation;
use App\Entity\Order;
use App\Entity\Produit;
use App\Service\SystempayService as ServicesSystempayService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SystempayServiceController extends AbstractController
{
    #[Route("/systempay/{reference}", name: 'app_systempay')]
    public function epay(ServicesSystempayService $systempay,EntityManagerInterface $entityManager, $reference)
    {
        $prix_total = 0;
        $remisePromoEuros = 0;
        $produit_list = [];
        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);
        if (!$order) { //si commande référencé non existante
            die("Pas de commande trouvée à cette référence. Veuillez retourner à la page d'accueil et refaire votre panier.");
        }
        else {
            if($order->getStripeSessionId() == $order->getReference() && $order->getState() !== 0){ //si une commande référencé est déjà effectuée, on ne pourra plus le refaire
                return $this->redirectToRoute('app_order_recap');
            }
            $promo = $entityManager->getRepository(CodePromo::class)->findOneByCode($order->getPromo());
            if($promo){ //si commande possedant un code promo
                if($promo->getMontantRemise() > 0){
                    $remisePromoEuros = $promo->getMontantRemise(); //remise code promo en euros
                }
            }
            foreach ($order->getOrderDetails()->getValues() as $produit) {
                $produit_list[] = ["name" => $produit->getProduct(),"price" => $produit->getPrice(), "illustration" => $produit->getPid()->getIllustration(), "quantite" => $produit->getQuantity()];
                $prix_total = $prix_total + ($produit->getPrice() * $produit->getQuantity());
            }
            // dd($prix_total);
            // dd($produit_list);
            $order->setStripeSessionId($reference);
            // dd($order);
            $entityManager->flush();
        }

        return $this->render('order/systempay.html.twig', [
            'controller_name' => 'EpayServiceController',
            'publicKey' => $systempay->getPublicKey(),
            'endpoint' => $systempay->getEndpoint(),
            'response' => $systempay->createPayment([
                "amount" => $prix_total + $order->getCarrierPrice() - $remisePromoEuros - ($order->getPointFideliteUtilise()) - ($order->getMontantCompteUtilise()),
                "currency" => "EUR",
                "orderId" => $reference,
                "customer" => ["email" =>  $this->getUser()->getEmail(), //doit être adresse mail valide $this->getUser()->getEmail() | test@gmail.fr 
                        "billingDetails" => ["lastName" => $order->getUser()->getLastname(), "firstName" => $order->getUser()->getFirstname(), "city" => $order->getDelivry()->getCity(), "country" => $order->getDelivry()->getCountry(), "zipCode" => $order->getDelivry()->getPostal(), "phoneNumber" => $order->getDelivry()->getPhone(), "address" => $order->getDelivry()->getAdress()]
                    ], 
                ]),
            "referenceId" => $reference, 
            "produits" => $produit_list,
            "livraison" => ["name" =>  $order->getCarrierName(), "price" => $order->getCarrierPrice()],
            'remiseFidelite' => ["name" => "Remise fidélité", "remise" => $order->getPointFideliteUtilise()],
            'remiseMontantCompte' => ["name" => "Remise montant compte", "remise" => $order->getMontantCompteUtilise()],
            'remisePromoEuros' => $remisePromoEuros,
        ]);
    }

    #[Route("/reservation/systempay/{reference}", name: 'app_systempay_reservation')]
    public function epayReservation(ServicesSystempayService $systempay,EntityManagerInterface $entityManager, $reference)
    {
        $prix_total = 0;
        $order = $entityManager->getRepository(HistoriqueReservation::class)->findOneByReference($reference);
        $adresse = $entityManager->getRepository(Adress::class)->findByUser($this->getUser())[0];
        if (!$order) { //si commande référencé non existante
            die('Pas de commande trouvée à cette référence.');
        }
        if($order->getState() !== 0){ //si une commande référencé est déjà effectuée, on ne pourra plus le refaire
            die('Réservation déjà effectué.');
        }
        $prix_total = $order->getTotal();
        $activite = ["name" => $order->getActiviteName(), "price" => $prix_total, "illustration" => $order->getActivite()->getImage()];
        

        return $this->render('order/reservation/systempay.html.twig', [
            'controller_name' => 'EpayServiceController',
            'publicKey' => $systempay->getPublicKey(),
            'endpoint' => $systempay->getEndpoint(),
            'response' => $systempay->createPayment([
                "amount" => $prix_total,
                "currency" => "EUR",
                "orderId" => $reference,
                "customer" => ["email" =>  $this->getUser()->getEmail(), //doit être adresse mail valide $this->getUser()->getEmail() | test@gmail.fr 
                        "billingDetails" => ["lastName" => $order->getUser()->getLastname(), "firstName" => $order->getUser()->getFirstname(), "city" => $adresse->getCity(), "country" => $adresse->getCountry(), "zipCode" => $adresse->getPostal(), "phoneNumber" => $adresse->getPhone(), "address" => $adresse->getAdress()]
                    ], 
                ]),
            "referenceId" => $reference, 
            "activite" => $activite,
        ]);
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

    #[Route("/systempay/success-test", name: 'app_systempay_success_test')]
    public function successepay()
    {
        // STEP 1 : check the signature with the password
        if (!$this->checkHash($_POST, $_ENV['EPAY_SHA256KEY'])) {
            echo 'Invalid signature. <br />';
            echo '<pre>' . print_r($_POST, true) . '</pre>';
            die();
        }

        $answer = array();
        $answer['kr-hash'] = $_POST['kr-hash'];
        $answer['kr-hash-algorithm'] = $_POST['kr-hash-algorithm'];
        $answer['kr-answer-type'] = $_POST['kr-answer-type'];
        $answer['kr-answer'] = json_decode($_POST['kr-answer'], true);
        dd($answer['kr-answer']);
        $mail = new Mail();
        $subject = "Payment /systempay/success-test TEST OK";
        $mail->send("arsenalpro74@gmail.com", "TEST PAIEMENT", $subject, "Test URL notif", 4639500);
        dd("OK");
        // STEP 2 : function to check the signature        

        return new Response();
    }

    #[Route("/systempay/paiement-refuse/{reference}", name: 'app_systempay_refused')]
    public function failedepay(EntityManagerInterface $entityManager, $reference)
    {
        $orderFail = $entityManager->getRepository(Order::class)->findOneByReference($reference);
        if($orderFail){
            return $this->render('order_cancel/index.html.twig', [
                'order' => $orderFail,
            ]);
        } else {
            return new Response();
        }

    }
}