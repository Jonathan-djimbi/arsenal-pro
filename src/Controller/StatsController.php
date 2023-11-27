<?php

namespace App\Controller;

use App\Entity\CodePromo;
use App\Entity\ComptesDocuments;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/admin/stats', name: 'app_stats')]
    public function index(EntityManagerInterface $em): Response
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){ //verifie si connecté et admin 
            return $this->redirectToRoute('app_login');
        }

        $date = new \DateTimeImmutable();
        $dateMoisDernier = new \DateTimeImmutable('now -1 months');
        $utilisateurs = $em->getRepository(User::class)->findAll();
        $topClient = $em->getRepository(User::class)->topClient(5); //par rapport au nombre d'achat
        $dernierAchatClient = $em->getRepository(User::class)->dernierAchatClient();
        $topProduitsCommandes = $em->getRepository(OrderDetails::class)->topProduitsCommandes(5);
        $topProduitsEuros = $em->getRepository(Produit::class)->topProduitsEuros(5, 'DESC');
        $bottomProduitsEuros = $em->getRepository(Produit::class)->topProduitsEuros(5, 'ASC');
        $topProduitsQuantite = $em->getRepository(Produit::class)->topProduitsQuantite(5);
        $topPrestations = $em->getRepository(OrderDetails::class)->topPrestationsCommandees(5);
        $topCodePromo = $em->getRepository(CodePromo::class)->topCodePromo(5);
        $topMontant = $em->getRepository(User::class)->topMontant(5);
        // dd($dernierAchatClient);
        // dd($topClient[0]->getCarteFidelite()->getValues());
        $compteDocuments = $em->getRepository(ComptesDocuments::class)->findAll();
        $orders = $em->getRepository(Order::class)->findAll();
        $ordersPrixMoyenne = $em->getRepository(Order::class)->prixCommandeManip(1); // uniquement commandes payées
        $ordersPanierMoyen = $em->getRepository(Order::class)->prixCommandeManip(0); //toutes les "orders" dans la BDD (les payées et non payées)
        $quantiteMoyenne = $em->getRepository(Order::class)->quantiteCommandeMoyenne();
        $ordersNow = $ordersSuccess = $ordersFailed = $ordersLastMonth = 0;
        $chiffreAffairesNow = $chiffreAffairesLastMonth = 0;
        $utilisateursCommande = [];
        $chiffreAffaireArray = [];

        foreach($orders as $order){ 

            if($order->getCreateAt()->format('m-Y') === $date->format('m-Y') && $order->getState() >= 1){ //on regarde combien de commandes ont été faite ce mois-ci
                $ordersNow++;
                $chiffreAffairesNow = $chiffreAffairesNow + $order->getTotalFinal();
            }
            //pour mois dernier nombre commande
            
            if($order->getCreateAt()->format('m-Y') === $dateMoisDernier->format('m-Y') && $order->getState() >= 1){ //on regarde combien de commandes ont été faite ce mois-ci
                $ordersLastMonth++;
                $chiffreAffairesLastMonth = $chiffreAffairesLastMonth + $order->getTotalFinal();
            }

            //global
            if($order->getState() >= 1){ //on regarde combien de vente total ont été faite
                $ordersSuccess++;
                $chiffreAffaireArray[] = ['periode' => $order->getCreateAt()->format('m-Y'), 'prix' => $order->getTotal() / 100]; //on ne prend pas en compte la livraison
            } else {
                $ordersFailed++;
            }

            foreach($utilisateurs as $user){ 
                if($order->getState() >= 1 && $order->getUser() == $user){ //si utilisateur ayant fait une commande alors
                    $utilisateursCommande[] = $user->getId();
                }
            }
        }
        // dd($chiffreAffaireArray);
       
        $caGlobal = $this->triCA($chiffreAffaireArray, $this->triCADate($chiffreAffaireArray));
        $ratioDocuments = round((count($compteDocuments)) / count($utilisateurs) * 100); //combien d'utilisateurs ont mis leurs documents
        $ratioUtilisateursCommande = round(count(array_unique($utilisateursCommande)) / count($utilisateurs) * 100); //combien utilisateur ayant fait une/des commandes
        $dernierAchatClientDate = strtotime(date_format($date,"Y-m-d H:i:s")) - strtotime(date_format($dernierAchatClient[0]['dernier_achat'],'Y-m-d H:i:s')); //dernier achat effectué du site
        // dd($dernierAchatClientDate);

        return $this->render('stats/index.html.twig', [
            'nombreCommandeNow' => $ordersNow,
            'nombreCommandeLastMonth' => $ordersLastMonth,
            'caNow' => $chiffreAffairesNow,
            'caLastMonth' => $chiffreAffairesLastMonth,
            'caGlobal' => $caGlobal,
            'prixCommande' => [
                'prixMoyenCommande' => $ordersPrixMoyenne[0]["prixCommande"],
                'prixMoyenPanierGlobal' => $ordersPanierMoyen[0]["prixCommande"],
            ],
            'quantiteMoyennePanier' => $quantiteMoyenne[0]["quantiteMoyenne"],
            'ratioVente' => [
                'success' => $ordersSuccess,
                'failed' => $ordersFailed,
            ],
            'utilisateurs' => [
                'nombreTotal' => count($utilisateurs),
                'topClient' => $topClient,
                'ratioDocuments' =>  $ratioDocuments,
                'utilisateursCommande' => $ratioUtilisateursCommande,
                'dernierAchatClientSite' => [
                    'dernierAchatClientDate' => $dernierAchatClientDate,
                    'nom' => $dernierAchatClient[0]['lastname'],
                    'prenom' => $dernierAchatClient[0]['firstname'],
                    'id' => $dernierAchatClient[0]['id'],
                ]
            ],
            'produits' => [
                'topProduitsCommandes' => $topProduitsCommandes,
                'topProduitsEuros' => $topProduitsEuros,
                'bottomProduitsEuros' => $bottomProduitsEuros,
                'topProduitsQuantite' => $topProduitsQuantite,
                'topPrestations' => $topPrestations,
            ],
            'codepromo' => [
                'topCodePromo' => $topCodePromo,
            ],
            'montant' => [
                'topMontant' => $topMontant,
            ],
        ]);
    }

    public function triCADate($array){ //tri des périodes
        $datum = [];
        foreach($array as $tab){
            $datum[] = $tab['periode'];
        }
        return $datum;
    }
    public function triCA($array, $datum){
        $datesTri = array_unique($datum); //date uniques, pas de doublons
        $final = [];
        $caMois = 0;
        foreach($datesTri as $date){ //pour chaque périodes
            $caMois = 0; //reset
            foreach($array as $tab){ //pour chaque commandes effectuées
                if($tab['periode'] == $date){ //si date m-Y égale à la date de la commande m-Y alors 
                    $caMois = $caMois + $tab['prix']; //on calcul le total de CA
                }
            }
            $final[] = [
                'date' => $date,
                'prix' =>  $caMois
            ];
        }
        return $final;
    }
}
