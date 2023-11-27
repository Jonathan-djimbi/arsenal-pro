<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\BourseArmes;
use App\Entity\ComptesDocuments;
use App\Entity\Produit;
use App\Entity\ProfessionnelAssociationCompte;
use App\Entity\VenteFlash;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Spipu\Html2Pdf\Tag\Html\Del;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\CheckImage;
class CartController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface  $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mon-panier', name:'cart')]
    public function index(Cart $cart, RequestStack $stack): Response
    {
        // dd($cart->get());
        $dateNow = new DateTimeImmutable('now +1 hours');
        $siVenteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
        $documentClientCheck = $this->entityManager->getRepository(ComptesDocuments::class)->findOneByUser($this->getUser());
        $siProPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());
        $venteFlashPrix = 0;
        $cartComplete = [];
        $enleverNonAffiche = [];

        if ($cart->get()) {
            foreach ($cart->get() as $id => $quantity) {
                $bourseArmes = $this->entityManager->getRepository(BourseArmes::class)->findOneBy(['pid' => $id]);
                $venteFlashPrix = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(['pid' => $id]);
                if($venteFlashPrix !== null && $venteFlashPrix->getTemps() > $dateNow){
                    $cartComplete[] = [
                        'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
                        'quantity' => $quantity,
                        'priceVenteFlash' =>  $venteFlashPrix->getNewPrice(),
                        'dateVenteFlash' => $venteFlashPrix->getTemps(),
                        'bourseArme' => false,
                    ];

                } else if ($bourseArmes !== null && $bourseArmes->getDateLimite() > $dateNow) {
                    $cartComplete[] = [
                        'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
                        'quantity' => $quantity,
                        'priceVenteFlash' =>  0,
                        'bourseArme' => $bourseArmes->isAffiche()
                    ];
                } else {
                    $cartComplete[] = [
                        'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
                        'quantity' => $quantity,
                        'priceVenteFlash' => 0,
                        'bourseArme' => false,
                    ];
                }
            }
            foreach($cartComplete as $key => $lesproduits){ //verif s'il y a des produits qui sont devenus indisponibles
                if($lesproduits['produit'] == null){ //si n'existe PLUS (car effacé dans la BDD ?)
                    // dd($cartComplete[$key]);
                    unset($cartComplete[$key]);
                } else { //si existe
                    if($lesproduits['produit']->getIsAffiche() === false){ //si pas affiche alors INDISPONIBLE
                        $enleverNonAffiche[] = $lesproduits; //on garde le produit pour le tableau des indisponible
                        unset($cartComplete[$key]); //on le vide du tableau cart
                    }
                }
                
            }

            // dd($cartComplete);
            // dd($enleverNonAffiche);
            $checkImage = new CheckImage();
            $checkImage->verifSiImage($cartComplete);
            $checkImage->verifSiImage($enleverNonAffiche);

        }
        // dd($cartComplete);
        // if(count($cart->getFull()) == count($panier_article_quantite_verif)){
        //     $panier_article_precommande_uniquement = true;
        // } 
        return $this->render('cart/index.html.twig', [

            'cart'=>$cartComplete,
            'indisponible' => $enleverNonAffiche,
            'date' => $dateNow,
            'siProPolice' => $siProPolice,
            'documentClient' => $documentClientCheck,
        ]);

    }


    #[Route('/cart/add/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, $id): Response
    {
        #dd($id); 
        $cart->add($id);


        return $this->redirectToRoute('cart');
    }

    #[Route('/nos-produits/cart/add', name: 'add_to_cart_from_panier')]
    public function addFromProduitPage(Request $req, Cart $cart)
    {
        $id = $req->request->get("id");
        // $produit = $this->entityManager->getRepository(Produit::class)->findOneBy(['id'=> $id]);
        $cart->add($id);
        return new Response();
    }

    #[Route('/cart/add/{id}/{quantite}', name: 'add_to_cart_cart')]
    public function addFromBoursePage(Request $req, Cart $cart, $id, $quantite) : Response
    {
        $quantitePrise = $quantite;
        $produit = $this->entityManager->getRepository(Produit::class)->findOneById($id);

        //$cart->add($id);
        
        if($quantitePrise > 0){ //pas de quantite negative
    
            $cart->addToPanier($id, $quantitePrise);
        }
        
        return $this->redirectToRoute('cart');  
    }

    #[Route('/produit-en-bourse/cart/add/{id}/quantite={quantite}', name: 'add_to_cart_bourse')]
    public function addCartInput(Request $req, Cart $cart, $id, $quantite) : Response
    {
        $quantitePrise = $quantite;
        $produit = $this->entityManager->getRepository(Produit::class)->findOneById($id);
        $quantiteCheck = $this->entityManager->getRepository(BourseArmes::class)->findOneByPid($produit)->getQuantiteMax();

        if($quantitePrise >= $quantiteCheck){ //si quantite prise égale ou supérieur à la quantité minimale autorisée
            $cart->addToPanierBourse($id, $quantitePrise);
            return $this->redirectToRoute('cart');
        } else { //sinon
            $this->addFlash('warning',"Vous n'avez pas sélectionné assez de quantité à commander.");
            return $this->redirectToRoute('app_produit_bourse', ['slug' => $produit->getSlug()]);        
        }
    }

    #[Route('/cart/remove', name: 'remove_my_cart')]
    public function remove(Cart $cart): Response
    {

        #dd($id);
        $cart->remove();

        return $this->redirectToRoute('app_products');
    }

    #[Route('/cart/delete/{id}', name: 'delete_to_cart')]
    public function delete(Cart $cart, $id): Response
    {

        #dd($id);
        $cart->delete($id);

      return $this->redirectToRoute('cart');
    }

    #[Route('/cart/decrease/{id}', name: 'decrease_to_cart')]
    public function decrease(Cart $cart, $id): Response
    {
        #dd($id);
        $cart->decrease($id);
        return $this->redirectToRoute('cart');
    }

    public function iconePanierQuantiteShow(Cart $cart, Request $req){ //pour l'icone du panier du header, pour afficher le nombre de produits dans le panier
        $count = 0;
        if($cart->get() !== null){
            foreach($cart->get() as $counting){ //à chaque itineration on compte combien de produit a cart
                $count = $count + $counting;
            }
        }
        // dd($count);
        return $this->render('cart/icone_cart.html.twig', [
            'quantite' => $count
        ]);
    }
}
