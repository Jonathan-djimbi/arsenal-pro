<?php


namespace App\Classe;

use App\Entity\CartDatabase;
use App\Entity\Produit;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;


class Cart extends AbstractController
{
    private $stack;
    private $entityManager;
    
    public function __construct(RequestStack $stack,EntityManagerInterface  $entityManager )

    {
        $this->entityManager = $entityManager;
        return $this->stack = $stack;
        
    }

    public function add($id)
    {

        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if(!empty($cart[$id])){
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $session->set('cart', $cart);
        $session->getMetadataBag()->getLifetime();
        
    }

    public function addToPanierBourse($id, $quantity) //only for bourse 
    {

        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);
        
        if(!empty($quantity)){
            $cart[$id] = $quantity;
        } 
        $session->set('cart', $cart);
        $session->getMetadataBag()->getLifetime();
        
    }

    public function addToPanier($id, $quantity) //only for cart controller
    {

        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);

        if(!empty($cart[$id]) && !empty($quantity)){
            $cart[$id] = $quantity;
        } else {
            $cart[$id] = 1;
        }
        $session->set('cart', $cart);
        $session->getMetadataBag()->getLifetime();
        
    }

    public function get()
    {
        $methodget = $this->stack->getSession();
        return $methodget->get('cart'); //retourne soit le panier en cookie
    }

    public function remove(){

        $methodremove = $this->stack->getSession();
        return $methodremove->remove('cart');
    }


    public function delete($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);
        unset($cart[$id]);


        return $session->set('cart', $cart);

    }
    public function decrease($id)
    {
        $session = $this->stack->getSession();
        $cart = $session->get('cart', []);
        if ($cart[$id] >1){
            $cart[$id]--;
            //retirer une qunatité
        }else{
            unset($cart[$id]);
            //suprmier produit
        }
        return $session->set('cart', $cart);


    }
    public function getFull()
    {
        $cartComplete=[];
        
        if ($this->get()){
            foreach( $this->get() as $id =>$quantity){
                $produit_object = $this->entityManager->getRepository(Produit::class)->findOneById($id);
                
                if(!$produit_object){
                    $this->delete($id);
                    continue;
                }
                 $cartComplete[]  = [
                     'produit' => $produit_object,
                     'quantity' => $quantity
                 ];
                
            }
        }
         return $cartComplete;
    }
    
}
