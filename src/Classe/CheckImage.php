<?php


namespace App\Classe;

use App\Entity\Produit;


class CheckImage
{
    public function verifSiImage($produits){
        // dd($produits);
        
        foreach ($produits as $produit) {
            // dd($produit);
            if(is_array($produit) && array_key_exists('produit', $produit)){
                $produit = $produit['produit'];
                $illustration = $produit->getIllustration();
                // dd($illustration);
                if (!filter_var($illustration, FILTER_VALIDATE_URL)) {
                    $filePath = "./../public/uploads/" . $illustration;

                    if (!file_exists($filePath)) {
                        $produit->setIllustration("error/img-error.jpg");
                    }
                }
            }else{
                $illustration = $produit->getIllustration();

                if (!filter_var($illustration, FILTER_VALIDATE_URL)) {
                    $filePath = "./../public/uploads/" . $illustration;

                    if (!file_exists($filePath)) {
                        $produit->setIllustration("error/img-error.jpg");
                    }
                }
            }
        }
    }
}