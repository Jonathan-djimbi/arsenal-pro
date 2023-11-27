<?php

namespace App\Controller;

use App\Classe\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RechercheController extends AbstractController
{
    private ProductController $produitController;

    public function __construct(ProductController $produitController) {
        $this->produitController = $produitController;
    }
    public function barreDeRecherche(Request $req, $nomfiltre, $nomBarreRecherche, $rendu){

        $this->produitController->saveString(null); //reset quand on essaie d'executer la barre de recherche en externe

        $search = new Search();
        
        $filtre = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
            'action' => $this->generateUrl('app_products'), //route vers page produits
            'method' => 'GET',
        ]);

        
        $filtre2 = $this->createForm(\App\Form\RechercheOnlyType::class, $search, [
            'action' => $this->generateUrl('app_products'), //route vers page produits
            'method' => 'GET',
        ]);

        $filtre->handleRequest($req);
        $filtre2->handleRequest($req);

        return $this->render($rendu, [
            $nomfiltre => $filtre->createView(),
            $nomBarreRecherche => $filtre2->createView(),
        ]);
            
    }
}
