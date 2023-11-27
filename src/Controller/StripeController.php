<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    /**
     * @throws ApiErrorException
     */
    #[Route("/commande/create-session/{reference}", name: 'app_stripe_create_session')]

    public function index(EntityManagerInterface $entityManager,Cart $cart, $reference)
    {
        $product_for_stripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000'; //https://arsenal-pro.fr en serveur 
        // $YOUR_DOMAIN = 'https://arsenal-pro.fr'; //https://arsenal-pro.fr/

        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);
        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }
        foreach ($order->getOrderDetails()->getValues() as $produit) {
            $product_object = $entityManager->getRepository(Produit::class)->findOneByName($produit->getProduct());    
                $product_for_stripe[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $produit->getPrice(),
                        'product_data' => [
                            'name' => $produit->getProduct(),
                            'images' => [!filter_var($product_object->getIllustration(), FILTER_VALIDATE_URL) ? $YOUR_DOMAIN."/uploads/".$product_object->getIllustration() : $product_object->getIllustration() ],
                        ],
                    ],
                    'quantity' => $produit->getQuantity(),
                ];
            // }  
        }

        $product_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ],
            ],
            'quantity' => 1,
        ];

    //Stripe::setApiKey('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'); // sk_test_51J1SugKxIPZEQCG7364jzfyD2LV9uVhrix2lJa9CRNbGuAuuMJOV3clfccAYWF7OexHztsUVz0uKYa3m0XOUMi1z00ho7GYio4
	Stripe::setApiKey('sk_test_51J1SugKxIPZEQCG7364jzfyD2LV9uVhrix2lJa9CRNbGuAuuMJOV3clfccAYWF7OexHztsUVz0uKYa3m0XOUMi1z00ho7GYio4');
        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $product_for_stripe
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id) ;
        $entityManager->flush();
       // $response = new JsonResponse(['id' => $checkout_session->id]);
       // return $response;



        return $this->redirect($checkout_session->url);
    }
}
