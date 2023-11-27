<?php

namespace App\Controller;

use App\Entity\Famille;
use App\Entity\Marque;
use App\Entity\MenuCategories;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuCategorieManagerController extends AbstractController
{
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('test/menu-manager', name: 'app_menu_categorie_manager')]
    public function rechercheParCategorie(){
        $i = 0;
        $categories9 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => "Forces de l'ordre"]);
        $categories1 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Armes de chasse']);
        $categories2 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Armes règlementées']);
        $categories3 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Armes de loisirs']);
        $categories4 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Armes de défense']);
        $categories5 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Accessoires']);
        $categories6 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Optique et électronique']);
        $categories7 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Munitions et rechargement']);
        $categories8 = $this->entityManager->getRepository(MenuCategories::class)->findBy(['type' => 'Pièces détachées']);
        
        // $categoriesEtabli1 = ['categories' => $categories1, 'subCategories' => $this->entityManager->getRepository(Famille::class)->findByName($categories1->getFamille())];
        // dd($categories1);
        $menus = [
            $categories1,
            $categories2,
            $categories3,
            $categories4,
            $categories5,
            $categories6,
            $categories7,
            $categories8,
            $categories9,
        ];
        // dd($menus);
        foreach($menus as $menu){ //CHECK POUR ENLEVER UNIQUEMENT LES FAMILLES QUI N'ONT AUCUN PRODUIT AFFICHE
            // dd($menu);
            if($i <= count($menus)){
                foreach($menu as $uneCategorie){
                    // dd($uneCategorie->getSubCategory()->getProduits());
                    if($uneCategorie->getFamille()){
                        $famille = [];
                        $checkIfProduits = $this->entityManager->getRepository(Famille::class)->findByName($uneCategorie->getFamille());
                        foreach($checkIfProduits as $produits){
                            if(count($produits->getProduits()) > 0){
                                $produitDetecte = false;
                                if(!$produitDetecte){ //tant qu'on n'a pas trouvé de produit disponible, on recheck
                                    foreach($produits->getProduits() as $unProduit){
                                        if($unProduit->getIsAffiche() === true && $unProduit->getSubCategory() == $uneCategorie->getSubCategory()){
                                            $produitDetecte = true;
                                        }
                                    }
                                }
                                if($produitDetecte){
                                    $famille[] = $produits;
                                }
                                // dd($uneCategorie);
                            } else {
                                // dd('STOP', $produits);
                            }
                            $uneCategorie->setFamille($famille);
                        }
                    }
                }
                $i++;
            }
        }
        // dd($menus);
        return $this->render('rechercheparcategorie.html.twig', [
            'menus' => $menus,
        ]);
    }

    #[Route('liste-marques', name: 'app_menu_marque')]
    public function pageMarques(){
        $marques = $this->entityManager->getRepository(Marque::class)->findAll();
        $marquesTri = [];
        foreach($marques as $uneMarque){
            $produitDetecte = false;
            if(!$produitDetecte){
                foreach($uneMarque->getProduits() as $produits){
                    if($produits->getIsAffiche()){
                        $produitDetecte = true;
                    }
                }
            }
            if($produitDetecte){
                $marquesTri[] = $uneMarque;
            }
            
        }
        return $this->render('product/marques/index.html.twig', [
            'menus' => $marquesTri,
        ]);
    }

}

    
