<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\CarteFidelite;
use App\Entity\Order;
use App\Entity\Produit;
use App\Entity\OrderDetails;
use App\Entity\CodePromo;
use App\Entity\HistoriqueCodePromo;
use App\Entity\PointFidelite;
use App\Entity\ProfessionnelAssociationCompte;
use App\Entity\SubCategory;
use App\Entity\User;
use App\Entity\VenteFlash;
use App\Form\OrderPrestationType;
use App\Form\OrderType;
use App\Form\CodePromoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\CheckImage;
class OrderController extends AbstractController
{          
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager=$entityManager;    
    }
    public function enleverAccents($str){ //efficace car le lien URL ne doit pas avoir d'accents provenant du nom et prénom
        return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }
    
    #[Route('/commande', name: 'app_order')]
    public function index(Cart $cart, Request $request): Response
    {
        $carriers = false;
        $pointrelais = true;
        $date = new \DateTimeImmutable('now +1 hours');
        $panier = $cart->getFull();
        $checkImage = new CheckImage();
        
        $checkSiFidelite = $this->entityManager->getRepository(CarteFidelite::class)->findBy(['user' => $this->getUser()]);
        $siProPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());

        foreach($panier as $key => $pan){
            if($pan['produit']->getIsAffiche() === false){ //double verification pour pour si le panier a vidé ou non le produit indisponible
                unset($panier[$key]); //enlever de l'affichage du tableau $panier
                $cart->delete($pan['produit']->getId()); //enleve du panier session un produit "non affiché"
            }
            $prod = $pan['produit'];
            $checkImage->verifSiImage($prod);
            
            if (!$carriers && ($pan['produit']->getCategory()->getId() === 7 || $pan['produit']->isIsCarteCadeau()) ){ //verif si produit est une prestation ou une carte cadeau
                $carriers = false;
            } else {
                $carriers = true;
            }
            if($pointrelais && ($pan['produit']->getCategory()->getId() === 1 || $pan['produit']->getCategory()->getId() === 2 || $pan['produit']->getCategory()->getId() === 3 || $pan['produit']->getCategory()->getId() === 4 || $pan['produit']->getCategory()->getId() === 11 || $pan['produit']->getCategory()->getId() === 12 || $pan['produit']->getCategory()->getId() === 13)){
                //1,2,3 = CAT B,C et D //4, 11, 12, 13 = MUNITIONS
                $pointrelais = false; 
            }
        }

        if (!$this->getUser()->getAdresses()->getValues())
        {
            return $this->redirectToRoute('app_account_address');

        } else if (empty($panier)) { //si panier vide, redirection vers la page panier et non de la page commande
            return $this->redirectToRoute('cart');
        }
       
        foreach ($cart->get() as $id => $quantity) {
            $venteFlashPrix = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(['pid' => $id]);
            if($venteFlashPrix !== null){
                $cartComplete[] = [
                    'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
                    'quantity' => $quantity,
                    'priceVenteFlash' =>  $venteFlashPrix->getNewPrice(),
                    'dateVenteFlash' => $venteFlashPrix->getTemps(),
                ];
            } else {
                $cartComplete[] = [
                    'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
                    'quantity' => $quantity,
                    'priceVenteFlash' => 0,
                ];
            }
        }
        if($carriers){
            $form = $this->createForm(OrderType::class, null, [
                'user' => $this->getUser(),
                'pointrelais' => $pointrelais, //valeur check
            ]);  
        } else {
            $form = $this->createForm(OrderPrestationType::class, null, [
                'user' => $this->getUser()
            ]);
        }
        if(count($checkSiFidelite) > 0){
            return $this->render('order/index.html.twig', [
                'livraison' => $carriers,
                'form' => $form->createView(),
                'cart' => $cartComplete,
                'compteFidele' => $checkSiFidelite[0],
                'siProPolice' => $siProPolice,
                'date' => $date
            ]);
        } else {
            return $this->render('order/index.html.twig', [
                'livraison' => $carriers,
                'form' => $form->createView(),
                'cart' => $cartComplete,
                'compteFidele' => [],
                'siProPolice' => $siProPolice,
                'date' => $date,
            ]);
        }
    
    }

    #[Route("/commande/recapitulatif", name:"app_order_recap")]
    public function add(Cart $cart, Request $request): Response
    {
        $panier = $cart->getFull();
        $totalPrice = 0;
        $carrierscheck = false;
        $munitionscheck = true;
        $pointrelais = true;
        $date = new \DateTimeImmutable('now +1 hours');
        $siPro = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());
        $checkImage = new CheckImage();
        foreach($panier as $pan){
            $prod = $pan['produit'];
            $checkImage->verifSiImage($prod);
            
            if (!$carrierscheck && ($pan['produit']->getCategory()->getId() === 7 || $pan['produit']->isIsCarteCadeau()) ){ //si prestation ou carte cadeau uniquement
                $carrierscheck = false; //si que des prestations
            } else {
                $carrierscheck = true; //si un produit qui n'est pas une prestation
            }
            if ($pan['produit']->getCategory()->getId() === 4 || $pan['produit']->getCategory()->getId() === 11 || $pan['produit']->getCategory()->getId() === 12 || $pan['produit']->getCategory()->getId() === 13){
                $munitionscheck = false; //s'il y a munitions dans le panier
            }
            if($pointrelais && ($pan['produit']->getCategory()->getId() === 1 || $pan['produit']->getCategory()->getId() === 2 || $pan['produit']->getCategory()->getId() === 3 || $pan['produit']->getCategory()->getId() === 4)){ //check si armes B,C,D et munitions pour livraison
                $pointrelais = false;
            }
        }
        foreach ($cart->get() as $id => $quantity) {
            $venteFlashPrix = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(['pid' => $id]);
            if($venteFlashPrix !== null && $venteFlashPrix->getTemps() > $date){
                $cartComplete[] = [
                    'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($id),
                    'quantity' => $quantity,
                    'priceVenteFlash' =>  $venteFlashPrix->getNewPrice(),
                    'dateVenteFlash' => $venteFlashPrix->getTemps(),
                ];
                $totalPrice = $totalPrice + ($venteFlashPrix->getNewPrice() * $quantity); //prix total du panier
            } else {
                $leProduit = $this->entityManager->getRepository(Produit::class)->findOneById($id);
                $cartComplete[] = [
                    'produit' => $leProduit,
                    'quantity' => $quantity,
                    'priceVenteFlash' => 0,
                ];

                if($leProduit->getPricePromo() && $leProduit->getPricePromo() < $leProduit->getPrice() && $leProduit->getPricePromo() !== $leProduit->getPrice()){ //si promo FDO
                    $totalPrice = $totalPrice + ($leProduit->getPricePromo() * $quantity); //prix total du panier

                } else if ($siPro && $leProduit->getPriceFDO() && $leProduit->getPriceFDO() > 1 ){ //si produit FDO et prix FDO
                    if($siPro->getNumeroMatricule()){
                        $totalPrice = $totalPrice + ($leProduit->getPriceFDO() * $quantity); //prix total du panier
                    } else { // si pas police
                        if($leProduit->getPricePromo() && $leProduit->getPricePromo() < $leProduit->getPrice() && $leProduit->getPricePromo() !== $leProduit->getPrice()){ //si promo FDO
                            $totalPrice = $totalPrice + ($leProduit->getPricePromo() * $quantity); //prix total du panier
                        } else {
                            $totalPrice = $totalPrice + ($leProduit->getPrice() * $quantity); //prix total du panier
                        }
                    }
                } else { //si le reste
                    $totalPrice = $totalPrice + ($leProduit->getPrice() * $quantity); //prix total du panier
                }
            }
        }
        // dd($totalPrice/100);

        $panier = $cartComplete;

        // dd($panier);
        if($carrierscheck){
            $form = $this->createForm(OrderType::class, null, [ //si dans le panier il y a des produits
                'user' => $this->getUser(),
                'pointrelais' => $pointrelais
            ]);
        } else {
            $form = $this->createForm(OrderPrestationType::class, null, [ //si dans le panier il y a des que prestations
                'user' => $this->getUser()
            ]); 
        }


        $form->handleRequest($request);
        $pourcentage = 0;
        $remisePromoEuros = 0;
        $remiseFideliteEuro = 0;
        $estFidele = [];
        $pointGagner = 0;
        $prixtotal = 0;
        $prixtotalCodePromoAffecte = 0; //permet de faire le total du prixTotal avec produits eligible en promo
        $prixtotalProduitEnDegressif = 0; //permet de faire le total des produits en degressif et ainsi déduire ce montant pour la fidélité par exemple
        $prixmasse = 0;
        $masse = 0;

        //verifie si la commande est existante
        if (!$carrierscheck || $form->isSubmitted() && $form->isValid()) {

            $codeinsere = $form->get('code')->getData();
            $montantinsere = $form->get('sommeCompte')->getData();
            $codepromo = $this->entityManager->getRepository(CodePromo::class)->findOneBy(['code'=> $codeinsere]);
            $historiquepromo = $this->entityManager->getRepository(HistoriqueCodePromo::class)->findBy(['client' => $this->getUser()->getId()]);
            $checkSiFidelite = $this->entityManager->getRepository(CarteFidelite::class)->findBy(['user' => $this->getUser()]);
            $point_fidelite = $this->entityManager->getRepository(PointFidelite::class)->findAll(); //parametre en point 
            $checkerValidite = false;
            $checkerUser = false;
            $checkerCategoriesValidite = false;
            if($historiquepromo){
                $check = $historiquepromo;
            } else {
                $check = null; //remplace si historiquepromo retourne rien
            }
            if($check){ //si historiquepromo EXISTE
                foreach($check as $promo){ //boucle du findBy de historiquepromo
                    if(($promo->getCodePromo()->getCode() !== $codeinsere && $codepromo)){ //si historiquepromo utilisateur n'a pas ce code inséré et si code promo existe
                        if($codepromo->getProduits()){
                            foreach($codepromo->getProduits() as $produitValide){
                                $valide = $this->entityManager->getRepository(Produit::class)->findOneById($produitValide);
                                if($valide){
                                    foreach($panier as $pan){
                                        if($pan['produit']->getId() == $valide->getId()){
                                            $checkerValidite = true;
                                        }
                                    }
                                }
                            }
                            if(!$checkerValidite){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance. Alors
                                $this->addFlash('warning',"Votre code promo n'est pas applicable pour ce panier");
                                return $this->redirectToRoute('app_order');
                            }
                        }
                        if($codepromo->getUsers()){ // si code promo ayant un compte ID approprié
                            foreach($codepromo->getUsers() as $user){
                                $userSearch = $this->entityManager->getRepository(User::class)->findOneById($user);
                                // dd($user, $userSearch);
                                if($userSearch && !$checkerUser){
                                    if($userSearch == $this->getUser()){
                                        $checkerUser = true;
                                    }
                                }
                            } //sinon
                            if(!$checkerUser){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance utilisateur
                                $this->addFlash('warning',"Votre code promo n'est pas valable");
                                return $this->redirectToRoute('app_order');
                            }
                        }
                        if($codepromo->getSubCategories()){ // si code promo ayant un compte ID approprié
                            foreach($codepromo->getSubCategories() as $subCategorie){
                                $subCategorieSelected = $this->entityManager->getRepository(SubCategory::class)->findOneById($subCategorie);
                                $produitSearch = $this->entityManager->getRepository(Produit::class)->findOneBySubCategory($subCategorieSelected);
                                // dd($user, $userSearch);
                                if($produitSearch){
                                    foreach($panier as $pan){
                                        if($pan['produit']->getSubCategory() == $produitSearch->getSubCategory()){
                                            $checkerCategoriesValidite = true;
                                        }
                                    }
                                }
                            } //sinon
                            if(!$checkerCategoriesValidite){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance par sous-categories
                                $this->addFlash('warning',"Votre code promo n'est pas applicable pour ce panier");
                                return $this->redirectToRoute('app_order');
                            }
                        }
                        if($codepromo->getMaxAmount()){
                            if(($codepromo->getMaxAmount()/100) >= ($totalPrice/100)){ //si montant minimum code promo est supérieur au montant panier | si montant mimimum pas atteint
                                $this->addFlash('warning',"Votre code promo n'est pas valable car il vous faut au moins " . number_format($codepromo->getMaxAmount()/100,2,'.','') . " €");
                                return $this->redirectToRoute('app_order');
                            }
                        }
                        if($codepromo->getUtilisationMax()){
                            if($codepromo->getUtilisation() >= $codepromo->getUtilisationMax()){
                                $this->addFlash('warning',"Votre code promo a atteint son nombre de limite");
                                return $this->redirectToRoute('app_order');
                            }
                        }
                        if($codepromo->getTemps()){
                            if($codepromo->getTemps() < $date){ //si code promo avec date de validité
                                $this->addFlash('warning',"Votre code promo a expiré");
                                return $this->redirectToRoute('app_order');
                             } else {
                                if($codepromo->getPourcentage() !== null && !empty($codepromo->getPourcentage())){
                                    $pourcentage = (100 - $codepromo->getPourcentage())/100;
                                }
                                if($codepromo->getMontantRemise() !== null && !empty($codepromo->getMontantRemise())){
                                    $remisePromoEuros = intval($codepromo->getMontantRemise());
                                }
                                $this->addFlash('success','Votre code promo est bien reconnu');
                             }
                        } else { //si code promo sans date de validité ou si tout est bon
                            if($codepromo->getPourcentage() !== null && !empty($codepromo->getPourcentage())){
                                $pourcentage = (100 - $codepromo->getPourcentage())/100;
                            }
                            if($codepromo->getMontantRemise() !== null && !empty($codepromo->getMontantRemise())){
                                $remisePromoEuros = intval($codepromo->getMontantRemise());
                            }
                            $this->addFlash('success','Votre code promo est bien reconnu');
                        }

                    } else { //si codepromo utilisé ou inexistant
                        if ($promo->getCodePromo()->getCode() == $codeinsere){ //si historiquepromo utilisateur a déjà inséré ce code


                            if($codepromo->getNbUtilisationMaxUser()){ //si déjà utilisé mais ayant une limite utilisation par user

                                if($codepromo->getUsers()){ // si code promo ayant un compte ID approprié
                                    foreach($codepromo->getUsers() as $user){
                                        $userSearch = $this->entityManager->getRepository(User::class)->findOneById($user);
                                        // dd($user, $userSearch);
                                        if($userSearch && !$checkerUser){
                                            if($userSearch == $this->getUser()){
                                                $checkerUser = true;
                                            }
                                        }
                                    } //sinon
                                    if(!$checkerUser){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance utilisateur
                                        $this->addFlash('warning',"Votre code promo n'est pas valable");
                                        return $this->redirectToRoute('app_order');
                                    }
                                }
                                // dd($promo->getNbUtilisationUser());
                                if($codepromo->getNbUtilisationMaxUser() <= $promo->getNbUtilisationUser()){
                                    $this->addFlash('warning',"Vous avez atteint le nombre d'utilisation de ce code promo : ". $codeinsere);
                                    return $this->redirectToRoute('app_order');
                                } else {
                                    if($codepromo->getPourcentage() !== null && !empty($codepromo->getPourcentage())){
                                        $pourcentage = (100 - $codepromo->getPourcentage())/100;
                                    }
                                    if($codepromo->getMontantRemise() !== null && !empty($codepromo->getMontantRemise())){
                                        $remisePromoEuros = intval($codepromo->getMontantRemise());
                                    }
                                    $this->addFlash('success','Votre code promo est bien reconnu');
                                }
                            } else { //si déjà utilisé
                                $this->addFlash('warning',"Vous avez déjà utilisé ce code promo : ". $codeinsere);
                                return $this->redirectToRoute('app_order');
                            }

                        } else if(!empty($codeinsere)){ //si mauvais code promo

                            $this->addFlash('warning',"Votre code promo n'est pas valable");
                            return $this->redirectToRoute('app_order');
                        } 
                    }
                }
            } else { //si historiquepromo n'EXISTE PAS
                if ($check === null && $codepromo){ //si pas d'historiquepromo (recheck) OU si historiquepromo utilisateur n'a pas ce code inséré et si code promo existe
                   
                    //répetition
                    if($codepromo->getProduits()){
                        foreach($codepromo->getProduits() as $produitValide){
                            $valide = $this->entityManager->getRepository(Produit::class)->findOneById($produitValide);
                            if($valide){
                                foreach($panier as $pan){
                                    if($pan['produit']->getId() == $valide->getId()){
                                        $checkerValidite = true;
                                    }
                                }
                            }
                        }
                        if(!$checkerValidite){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance. Alors
                            $this->addFlash('warning',"Votre code promo n'est pas applicable pour ce panier");
                            return $this->redirectToRoute('app_order');
                        }
                    }

                    // if($codepromo->getUser()){ // si code promo ayant un compte ID approprié
                    //     if($codepromo->getUser() !== $this->getUser()){ //si pas lié à l'utilisateur connecté
                    //         $this->addFlash('warning',"Votre code promo n'est pas valable");
                    //         return $this->redirectToRoute('app_order');
                    //     } // sinon
                    // }
                    if($codepromo->getUsers()){ // si code promo ayant un compte ID approprié
                        foreach($codepromo->getUsers() as $user){
                            $userSearch = $this->entityManager->getRepository(User::class)->findOneById($user);
                            if($userSearch && !$checkerUser){
                                if($userSearch == $this->getUser()){
                                    $checkerUser = true;
                                }
                            }
                        } //sinon
                        if(!$checkerUser){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance utilisateur
                            $this->addFlash('warning',"Votre code promo n'est pas valable");
                            return $this->redirectToRoute('app_order');
                        }
                    }
                    if($codepromo->getSubCategories()){ // si code promo ayant un compte ID approprié
                        foreach($codepromo->getSubCategories() as $subCategorie){
                            $subCategorieSelected = $this->entityManager->getRepository(SubCategory::class)->findOneById($subCategorie);
                            $produitSearch = $this->entityManager->getRepository(Produit::class)->findOneBySubCategory($subCategorieSelected);
                            // dd($user, $userSearch);
                            if($produitSearch){
                                foreach($panier as $pan){
                                    if($pan['produit']->getSubCategory() == $produitSearch->getSubCategory()){
                                        $checkerCategoriesValidite = true;
                                    }
                                }
                            }
                        } //sinon
                        if(!$checkerCategoriesValidite){ //si checker n'est pas UNE FOIS en true mais qu'en false. Cela veut dire aucune correspondance par sous-categories
                            $this->addFlash('warning',"Votre code promo n'est pas applicable pour ce panier");
                            return $this->redirectToRoute('app_order');
                        }
                    }
                    if($codepromo->getMaxAmount()){
                        if(($codepromo->getMaxAmount()/100) >= ($totalPrice/100)){ //si montant minimum code promo est supérieur au montant panier | si montant mimimum pas atteint
                            $this->addFlash('warning',"Votre code promo n'est pas valable car il vous faut au moins " . number_format($codepromo->getMaxAmount()/100,2,'.','') . " €");
                            return $this->redirectToRoute('app_order');
                        }
                    }
                    if($codepromo->getUtilisationMax()){
                        if($codepromo->getUtilisation() >= $codepromo->getUtilisationMax()){
                            $this->addFlash('warning',"Votre code promo a atteint son nombre de limite");
                            return $this->redirectToRoute('app_order');
                        }
                    }

                    if($codepromo->getTemps()){
                        if($codepromo->getTemps() < $date){ //si code promo avec date de validité
                            $this->addFlash('warning',"Votre code promo a expiré");
                            return $this->redirectToRoute('app_order');
                         } else {
                            if($codepromo->getPourcentage() !== null && !empty($codepromo->getPourcentage())){
                                $pourcentage = (100 - $codepromo->getPourcentage())/100;
                            }
                            if($codepromo->getMontantRemise() !== null && !empty($codepromo->getMontantRemise())){
                                $remisePromoEuros = intval($codepromo->getMontantRemise());
                            }
                            $this->addFlash('success','Votre code promo est bien reconnu');
                         }
                    } else { //si code promo sans date de validité ou si tout est bon
                        if($codepromo->getPourcentage() !== null && !empty($codepromo->getPourcentage())){
                            $pourcentage = (100 - $codepromo->getPourcentage())/100;
                        }
                        if($codepromo->getMontantRemise() !== null && !empty($codepromo->getMontantRemise())){
                            $remisePromoEuros = intval($codepromo->getMontantRemise());
                        }
                        $this->addFlash('success','Votre code promo est bien reconnu');
                    }

                } else {
                    if(!empty($codeinsere)){ //si mauvais code promo

                        $this->addFlash('warning',"Votre code promo n'est pas valable");
                        return $this->redirectToRoute('app_order');
                    }
                }
            }
            //dd($remisePromoEuros, $pourcentage);
            $delivery = $form->get('adresses')->getData();
            $fideleCheck = $form->get('fidele')->getData();

            if ($carrierscheck){
                if($munitionscheck && ($totalPrice/100) >= 200 && $delivery->getCountry() == "FR"){ //si supérieur à 200 euros et FRANCAIS alors livraison offerte
                    $carriersLivraisonOfferte = array("name" => $form->get('carriers')->getData()->getName(), "description" => $form->get('carriers')->getData()->getDescription(), "price" => 0);
                } else {
                    $carriersNormal = $form->get('carriers')->getData();
                }
            } else {
                $carriersPrestations = array("name" => "Atelier à Arsenal Pro", "description" => "", "price" => 0);
            }

            $delivery_content = ["prenom" => $delivery->getFirstname(), "nom" => $delivery->getLastname(),"numero" => $delivery->getPhone(), "adresse" => ["lieu" => $delivery->getAdress(), "postal" => $delivery->getPostal(), "ville" => $delivery->getCity(), "pays" => $delivery->getCountry()]];  

            //enregistrer ma commande Order()
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreateAt($date);
            if (!$carrierscheck){
                $order->setCarrierName($carriersPrestations["name"]);
                $order->setCarrierPrice($carriersPrestations["price"]);
                $carriers = $carriersPrestations;
            } else {
                if($munitionscheck && (($totalPrice/100) >= 200 && $delivery->getCountry() == "FR")){ //si commande supérieure à 200 euros et FRANCE
                    $order->setCarrierName($carriersLivraisonOfferte["name"]);
                    $order->setCarrierPrice($carriersLivraisonOfferte["price"]); //0€ livraison gratuite
                    $carriers = $carriersLivraisonOfferte;
                    
                } else {
                    $order->setCarrierName($carriersNormal->getName());
                    $order->setCarrierPrice($carriersNormal->getPrice()); 
                    $carriers = $carriersNormal;     
                }
            }
            //nomage numero commande
            $facturecount = 1; //on commence par 1001 pour la facture
            $countMonth = intval(date('m'));
            $annee = intval(date('Y'));
            if(!is_dir("./../factures/". date("m-Y"). "")){ //dossier mois-année
                mkdir("./../factures/". date("m-Y"). "");
            } else {
                for($i = 1; $i < $countMonth+1; $i++){
                    if($i < 10){ // mois
                        $facturecount = $facturecount + count(glob("./../factures/" .'0'. $i . '-' . $annee . "/*")); //compte combien de factures sont dans le dossier
                        // dd(glob("./../factures/" .'0'. $i . '-' . $annee . "/*"));
                    } else {
                        $facturecount = $facturecount + count(glob("./../factures/". $i . '-' . $annee . "/*")); //compte combien de factures sont dans le dossier
                    }
                }
                $facturecount = $facturecount + 1; //final count
            }
            // dd($facturecount);
            if($facturecount < 999){
                $nofacture = "S" . substr(date("Y"),2,4) ."-". date("m-d"). "-1" .  str_pad($facturecount,3,0,STR_PAD_LEFT);
            } else {
                $nofacture = "S" . substr(date("Y"),2,4) ."-". date("m-d"). "-" .  (1000 + $facturecount);
            }

            if(($order->getCarrierName() !== "Retrait au dépôt") && ($order->getCarrierName() !== "Atelier à Arsenal Pro")){  //SI retrait ou non pour le numero de reference
                $reference = $nofacture . '-' .  $this->enleverAccents($this->getUser()->getLastname()) . "-L-" . substr(uniqid(),4,4);
            } else {
                $reference = $nofacture . '-' .  $this->enleverAccents($this->getUser()->getLastname())  . "-R-" . substr(uniqid(),4,4);
            }
            //
            $order->setReference($reference);
            $order->setDelivry($delivery);
            $order->setState(0);
            if($codepromo){
                $order->setPromo($codepromo->getCode());
            }

            //enregistrer mes produits OrderDetails()
            foreach ($panier as $produit) {
                $produitprix = 0;
                $key = 1; //pour prix degressif UNIQUEMENT
                $prixFDO = false; //etat check prix FDO

                if($produit['produit']->getMasse() && $order->getCarrierName() == "COLISSIMO"){ //prix masse pour colissimo
                    $prixmasse = $prixmasse + (($produit['produit']->getMasse() * $produit['quantity']) * 0.8); //0.8 = 80 centimes
                    $masse = $masse + (($produit['produit']->getMasse() * $produit['quantity'])); //on stock la masse totale dans ce controlleur 

                    if(($masse) >= 1 &&  ($masse) <= 2 || ($masse) >= 10 && ($masse) <= 11){
                        $prixmasse = $prixmasse + 3; //ajout de 3 euros en plus
                    }
                    // $quantitetotal = ($quantitetotal + $produit['quantity']);
                }

                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);

                if($siPro){ //si pro
                    if($siPro->getNumeroMatricule() !== null && $produit['produit']->getPriceFDO() && $produit['produit']->getPriceFDO() > 1) { //si police et si produit FDO et prix FDO
                        $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format($produit['produit']->getPriceFDO())));
                        $prixFDO = true;
                    }
                } 
                
                if(!$prixFDO){
                    if($produit['produit']->isVenteFlash() && $produit['priceVenteFlash'] > 0 && $produit['dateVenteFlash'] > $date){ //si prix vente flash dispo et n'a pas dépassé la date de limite flash
                        $produitprix = $produit['priceVenteFlash'];
                    //sinon si prix promo
                    } else if($produit['produit']->getPricePromo() && $produit['produit']->getPricePromo() < $produit['produit']->getPrice() && $produit['produit']->getPricePromo() !== $produit['produit']->getPrice()){ //si prix promo
                        $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format($produit['produit']->getPricePromo()))); //on revient au prix de base et on remet le % du code promotion (fix bug dégressif)
                    //sinon
                    } else { //si prix non promo, c'est le prix d'origine || A VOIR
                        $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format($produit['produit']->getPrice()))); //on revient au prix de base et on remet le % du code promotion (fix bug dégressif)
                    }
                }
                
                $orderDetails->setProduct($produit['produit']->getName());
                $orderDetails->setPid($produit['produit']);
                $orderDetails->setQuantity($produit['quantity']);

                if($produit['produit']->getIsDegressif() && $produit['quantity'] >= 10){ //DEGRESSIF TABLEAU : pendant la boucle for si un produit est degressif et la quantité supérieur ou égale à 10 alors on fait une remise par quantité
                    $degressifPrixCheckStop = false;
                    foreach($produit['produit']->getDegressifValues() as $val){
                        if(!$degressifPrixCheckStop){
                            if(empty($produit['produit']->getDegressifValues()[$key+1])){ //si vide, veut dire que c'est le dernier valeur du tableau
                                if($val <= $produit['quantity']){
                                    $orderDetails->setPrice($produitprix - ($key * 50)); //degressif (- key x 50) centimes
                                    $degressifPrixCheckStop = true;
                                }
                            } else {
                                if($produit['quantity'] >= $val && $produit['quantity'] < $produit['produit']->getDegressifValues()[$key+1]){
                                    if($val === 10){ //si quantité prise à 20 | pour que ça fonctionne j'ai dû mettre la valeur précédente qui est de 10
                                        $orderDetails->setPrice($produitprix - ($key * 50) - 20); //degressif (- key x 50) centimes -20 centimes supplémentaire
                                    } else {
                                        $orderDetails->setPrice($produitprix - ($key * 50)); //degressif (- key x 50) centimes
                                    }
                                    // dd($produitprix - ($key * 50));
                                    $degressifPrixCheckStop = true;
                                }
                            }
                            if(!$degressifPrixCheckStop){ //check à l'interieur de la condition pour pas ajouter +1 alors que c'est affecté
                                $key++;
                            }
                        }
                    }
                }
                // dd($pourcentage, $codepromo);
                if($codepromo){ 
                    if(!$produit['produit']->isVenteFlash() && !$produit['produit']->getIsDegressif() && !$produit['produit']->isIsCarteCadeau()){ //si CODE PROMO APPLIQUÉ (pas applicable pour vente flash, degressif et carte cadeau)
                        if($codepromo->getProduits() && $checkerValidite){ //SI CODE PROMO A DES PRODUITS SPECIFIQUE POUR ETRE VALIDE  
                            $orderDetails->setTotalOriginIfPromo($produitprix * $produit['quantity']); //prix d'origine non remise avant promo
                            foreach($codepromo->getProduits() as $produitValideVerification){
                                if($produitValideVerification == $produit['produit']->getId()){
                                    if($pourcentage > 0 && $remisePromoEuros === 0){ //si pourcentage promo alors
                                        $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format((($produitprix) * $pourcentage)))); //pour éviter conflit après les décimales
                                    }
                                } else { //sinon prix pour les autres produits sans remise pourcentage promo
                                    // dd($orderDetails->getTotalOriginIfPromo(), $produitprix);
                                    $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format(($produitprix)))); //pour éviter conflit après les décimales
                                }
                            }
                        } else if($codepromo->getSubCategories() && $checkerCategoriesValidite){ //SI CODE PROMO A DES PRODUITS SPECIFIQUE POUR ETRE VALIDE  
                            $orderDetails->setTotalOriginIfPromo($produitprix * $produit['quantity']); //prix d'origine non remise avant promo
                            foreach($codepromo->getSubCategories() as $produitValideVerification){
                                if($produit['produit']->getSubCategory() !== null){
                                    if($produitValideVerification == $produit['produit']->getSubCategory()->getId()){
                                        if($pourcentage > 0 && $remisePromoEuros === 0){ //si pourcentage promo alors
                                            $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format((($produitprix) * $pourcentage)))); //pour éviter conflit après les décimales
                                        }                                 
                                    } else { //sinon prix pour les autres produits sans remise pourcentage promo
                                        // dd($orderDetails->getTotalOriginIfPromo(), $produitprix);
                                        $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format(($produitprix)))); //pour éviter conflit après les décimales
                                    }
                                }
                            }
                            //update subcategory
                        } else {
                            $orderDetails->setTotalOriginIfPromo($produitprix * $produit['quantity']); //prix d'origine non remise avant promo
                            if($pourcentage > 0 && $remisePromoEuros === 0){ //si pourcentage promo alors
                                $produitprix = floatval(preg_replace('/[^\d.]/', '', number_format((($produitprix) * $pourcentage)))); //pour éviter conflit après les décimales
                            }                   
                        }
                        $prixtotalCodePromoAffecte = $prixtotalCodePromoAffecte + floatval(preg_replace('/[^\d.]/', '', number_format((($produitprix) * $produit['quantity']))));
                    } else {
                        if($produit['produit']->getIsDegressif() && $produit['quantity'] >= 10){ //sinon pas promo mais on accumule prixtotalCodePromoAffecte pour avoir le total de la commande avec certain produits en promo

                            $prixtotalCodePromoAffecte = $prixtotalCodePromoAffecte + ($orderDetails->getPrice() * $produit['quantity']); //on a déjà set un prix avec setPrice dans la boucle de dégressif d'avant

                        } else {
                            $prixtotalCodePromoAffecte = $prixtotalCodePromoAffecte + ($produitprix * $produit['quantity']);
                        }
                    }
                    $order->setRemisePromoEuros($remisePromoEuros);
                }
                if($produit['produit']->getIsDegressif()){ //calculer combien en euros de produits sont en degressifs 
                    if($produit['quantity'] >= 10){
                        $prixtotalProduitEnDegressif = $prixtotalProduitEnDegressif + ($produitprix * $produit['quantity']) - ($produit['quantity'] * ($key * 50));
                    } else { //si quantité inférieur à 10
                        $prixtotalProduitEnDegressif = $prixtotalProduitEnDegressif + ($produitprix * $produit['quantity']);
                    }
                }

                if($produit['produit']->getIsDegressif() && $produit['quantity'] >= 10){ //pendant la boucle for si un produit est degressif et la quantité supérieur ou égale à 10 alors on fait une remise par quantité
                    // $orderDetails->setTotal(($produitprix * $produit['quantity']) - ($produit['quantity'] * ($key * 50))); 
                    $orderDetails->setTotal($orderDetails->getPrice() * $produit['quantity']); //on a déjà set un prix avec setPrice dans la boucle de dégressif d'avant
                } else {
                    $orderDetails->setPrice($produitprix);
                    $orderDetails->setTotal($produitprix * $produit['quantity']);
                }

                $prixtotal = floatval(preg_replace('/[^\d.]/', '', number_format($prixtotal + $orderDetails->getTotal()))); //pour éviter d'avoir des nombre à . dans la BDD sinon erreurs
                $this->entityManager->persist($orderDetails); //quand nouvelle ligne dans bdd
            }
            //remise fidelite
            if(!$codepromo && $prixtotalCodePromoAffecte === 0){ //si commande n'ayant pas de code promo. fidelite seulement applicable sans code promo
                if($fideleCheck && count($checkSiFidelite) > 0 && ($prixtotal/100 > 200)){ //si cocher pour utiliser les points
                    $remiseFidelite = ($prixtotal /100) * ($point_fidelite[0]->getRemise()/100); //prix total * remise fidelite
                    if($checkSiFidelite[0]->getNombreAchat() >= 2 && ($remiseFidelite * 10) <= $checkSiFidelite[0]->getPoints()){ //si compte eligible ou pas et a au moins 2 achats
                        $remiseFideliteEuro = number_format($remiseFidelite * 10,0,",",""); //point de fidelite a utiliser en euro pour remise
                        //dd($remiseFideliteEuro);
                        $order->setPointFideliteUtilise($remiseFideliteEuro*10);
                    } 
                    $estFidele = [ "compte" => $checkSiFidelite[0], "check" => $fideleCheck ];
                } else if (!$fideleCheck && count($checkSiFidelite) > 0 ){ //si pas cocher pour utiliser les points
                    if($prixtotal >= $point_fidelite[0]->getMontantPanier()){ //si total de la commande superieur ou egale au montant panier fixe de la fidelite
                        //$prixtotalProduitEnDegressif permet de déduire le prix pour les points des produits en degressif
                        $pointGagner = (($prixtotal - $prixtotalProduitEnDegressif) / 100) / ($point_fidelite[0]->getMontantPanier()/100); //floor = prendre le integer le plus petit pour eviter les nombre comme 1.33
                    }
                    $estFidele = [ "compte" => $checkSiFidelite[0], "check" => $fideleCheck ];
                } else {
                    $estFidele = ["compte" => false, "check" => false]; //on force tout à false si pas fidele
                }
            } else {
                $estFidele = ["compte" => false, "check" => false]; //on force tout à false si pas fidele
            }
            //remise montant compte
            if($montantinsere > 0 && !empty($montantinsere)){
                if($checkSiFidelite[0]->getSommeCompte() >= intval($montantinsere*100)){
                    if($prixtotal < intval($montantinsere*100) + 100){ //si montant inséré superieur au montant de la commande
                        $order->setMontantCompteUtilise(intval($prixtotal - 100)); //Montant utilisé = montant de la commande pour pas que le prix de la commande soit négatif
                        if($order->getPointFideliteUtilise() > 0  && $order->getPointFideliteUtilise() !== null){
                            $order->setPointFideliteUtilise(0); //Pour ce cas : SI POINT FIDELITE UTILISE ALORS 0 pour éviter commande avec valeur négative
                            $remiseFideliteEuro = 0;
                            $this->addFlash('notice',"Remise fidélité annulée. Montant inséré du montant compte proche du prix total de la commande.");
                        }
                    } else {
                        $order->setMontantCompteUtilise(intval($montantinsere * 100));
                    }
                } else {
                    $this->addFlash('warning',"Vous ne pouvez pas utiliser " . number_format($montantinsere,2) ." € alors que vous avez en tout " . number_format($checkSiFidelite[0]->getSommeCompte()/100) . "€ dans votre compte.");
                    return $this->redirectToRoute('app_order');
                }
            }
            if($order->getMontantCompteUtilise() > 0 && $order->getPointFideliteUtilise() > 0){
                if($prixtotal < ($order->getMontantCompteUtilise() + $order->getPointFideliteUtilise()) ){ //si montant compte + remise fidélité superieure au montant de la commande
                    $order->setPointFideliteUtilise(0); //Il faut éviter que la commande ait une valeur négative en euros
                    $remiseFideliteEuro = 0;
                    $this->addFlash('notice',"Remise fidélité annulée. Montant inséré du montant compte proche du prix total de la commande.");
                }
            }

            if($prixmasse > 0 && $order->getCarrierName() == "COLISSIMO"){
                if((($prixtotal/100) >= 200 && $delivery->getCountry() == "FR") === false || (($prixtotal/100) >= 200 && $delivery->getCountry() == "FR" && !$munitionscheck) === true){ //si ce n'est pas à plus de 200 euros ET si c'est francais | on evite d'ajouter des frais à une livraison gratuite
                    // dd($order->getCarrierPrice(), $prixmasse *100);
                    $order->setCarrierPrice(floatval(preg_replace('/[^\d.]/', '', number_format($order->getCarrierPrice() + ($prixmasse*100)))));
                }
            }
            $this->entityManager->persist($order);
            $this->entityManager->flush(); //MAJ BDD

            return $this->render('order/add.html.twig', [
                'cart' => $panier,
                'carrier' => $carriers,
                'carrierscheck' => $carrierscheck,
                'munitionscheck' => $munitionscheck,
                'delivery' => $delivery_content,
                'prixmasse' => $prixmasse,
                'reference'=>$order->getReference(),
                'pourcentage_promo' => $pourcentage,
                'montant_promo' => $order->getRemisePromoEuros(),
                'totalpromo' => $prixtotalCodePromoAffecte,
                'siProPolice' => $siPro,
                'compteFidelite' => $estFidele,
                'remiseFidelitePourcent' => $point_fidelite[0]->getRemise(),
                'remiseFideliteEuro' => $remiseFideliteEuro,
                'remiseMontantCompte' => $order->getMontantCompteUtilise(),
                'pointGagner' => number_format($pointGagner * 10,0,",",""),
                'date' => $date
            ]);
        }

        //sinon renvoi vers le panier
        return $this->redirectToRoute('cart');
    }
}

