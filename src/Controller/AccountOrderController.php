<?php

namespace App\Controller;

use App\Entity\CodePromo;
use App\Entity\HistoriqueReservation;
use App\Entity\Order;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountOrderController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function verifSiImage($prod){
        foreach($prod as $unProduit){
          if($unProduit->getPid() != null){
            if(!file_exists("./../public/uploads/" . $unProduit->getPid()->getIllustration())){ //il faut set perm fichier 755
                $unProduit->getPid()->setIllustration("error/img-error.jpg");
                //no entityManager->flush() pas de MAJ BDD, juste remplacer pendant une session client
            }
          }
        }
    }  
    
    #[Route('/compte/mes-commandes', name: 'app_account_order')]
    public function index()
    {
        $orders= $this->entityManager->getRepository(Order::class)->findSuccessOrders($this->getUser());
        
        
        return $this->render('account/order.html.twig', [
          'orders' => $orders
        ]);
    }

    
    #[Route('/compte/mes-commandes/{reference}', name: 'app_account_order_show')]
      public function show($reference){

        $order = $this->entityManager->getRepository(Order::class)->findOneByReference($reference);
        $promo = $this->entityManager->getRepository(CodePromo::class)->findOneByCode($order->getPromo());
        $produits = $order->getOrderDetails()->getValues();
        $this->verifSiImage($produits);
        // dd($produits[0]->getPid()->getIllustration());
        if (!$order || $order->getUser() != $this->getUser()) {
                return $this->redirectToRoute('account_order');
        }

      return $this->render('account/order_show.html.twig', [
        'order' => $order,
        'produits' => $produits,
        'promo' => $promo
      ]); 
    }

    #[Route('/compte/mes-reservations', name: 'app_account_reservation')]
    public function reservation_index()
    {
        $orders = $this->entityManager->getRepository(HistoriqueReservation::class)->findSuccessReservations($this->getUser());
        
        return $this->render('account/order_reservation.html.twig', [
          'orders' => $orders
        ]);
    }
      
}
