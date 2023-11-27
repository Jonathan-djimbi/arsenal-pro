<?php

namespace App\Service;

use App\Entity\DepotVente;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionFichierServerService{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supprimerFichierDepotVente(){
        $effacement = false;
        if(is_dir('./public/uploads/depot-ventearmes/')){
            $sousDossiers = glob('./public/uploads/depot-ventearmes/*');
                foreach($sousDossiers as $sd){
                    $fichiers = glob($sd . "/*");
                    if($fichiers){ //s'il y a des fichiers
                        foreach($fichiers as $fichier){ //boucle fichiers pour les effacer
                            unlink($fichier);
                        }
                    }
                    rmdir($sd); //effacement du dossier utilisateur depot vente
                    $effacement = true;
                    echo "Effacement : " . $effacement . "\n";
                }
            $depotVente = $this->entityManager->getRepository(DepotVente::class)->findBy(['type' => 'depot-vente']);
            if($depotVente && $effacement){
                foreach($depotVente as $dp){ //BDD photos en NULL
                    $dp->setPhotoUn(null);
                    $dp->setPhotoDeux(null);
                    $dp->setPhotoTrois(null);
                    $dp->setPhotoQuatre(null);
                }
                $this->entityManager->flush();
                echo "BDD photos en NULL\n";
            }
        }
        return new Response();
    }

    public function imageCompressor($source, $destination) {

        $fichier = getimagesize($source);
    
        if ($fichier['mime'] == 'image/jpeg'){
            $image = imagecreatefromjpeg($source);
        }
        elseif ($fichier['mime'] == 'image/png'){ 
            $image = imagecreatefrompng($source);
        }
    
        imagejpeg($image, $destination, 35); //35% pour une bonne compression
    
        return $destination;
    }

}

?>