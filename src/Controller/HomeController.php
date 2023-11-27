<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Classe\Search;
use App\Entity\Annonce;
use App\Entity\Header;
use App\Entity\PointFidelite;
use App\Entity\Produit;
use App\Entity\Pub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\CheckImage;
class HomeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/', name: 'app_home')]
    public function index(Request $request)
    {
        // dd($_SESSION["email_newsletter"]);
        $products = $this->entityManager->getRepository(Produit::class)->findIsBest(1,1); //isBest,isAffiche
        $headers = $this->entityManager->getRepository(Header::class)->findAll();
        $allProduits = $this->entityManager->getRepository(Produit::class)->findProduit();
        $promoProduits = $this->entityManager->getRepository(Produit::class)->findisPromo(1); //isAffiche
        $fidelite = $this->entityManager->getRepository(PointFidelite::class)->findOneById(1);
        // dd($promoProduits);
        $checkImage = new CheckImage();
        $checkImage->verifSiImage($products);
        $checkImage->verifSiImage($allProduits);
        $checkImage->verifSiImage($promoProduits);
        // unset($_COOKIE["inscrit_newsletter"]);

        if(isset($_COOKIE["inscrit_newsletter"])){ //verifie si un client s'est déjà inscrit/ne veut pas de newsletter ou non
            $newsletter = true;
        } else {
            $newsletter = false;
        }
        //dd($newsletter);
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'allproduits' => $allProduits,
            'promoproduits' => $promoProduits,
            'headers' => $headers,
            'inscrit_newsletter' => $newsletter,
            'fidelite' => $fidelite
        ]);
    }

    public function banniereShow(Request $req, $titre, $section, $multi){ //numero 1 ou 2 pour selectionner une image
        $pubsDisplay = [];
        if($multi === true){
            $pubSelect = $this->entityManager->getRepository(Pub::class)->findBySection($section);

            foreach($pubSelect as $pubs){
                $titrePub = null;
                $url = null;
                $titrePubCouleur = "#ffffff"; //blanc defaut
                if($pubs->isImageAffiche()){ //si affiche
                    if($pubs->getImage()){
                        $image = "/uploads/nos-pubs/" . $pubs->getImage();
                    } 
                    if($pubs->getUrl()){
                        $url = $pubs->getUrl();
                    }
    
                    if($pubs->getTexte()){
                        $titrePub = $pubs->getTexte();
                        $titrePubCouleur = $pubs->getTexteCouleur();
                    }
                    if($pubs->getSection() === 2){ //si pour carrousel
                        
                        $pubsDisplay[] = ["titre" => $titrePub, "image" => $image, "couleur" => $titrePubCouleur, "url" => $url];
          
                    } 
                } 
                
            } 
            if(count($pubsDisplay) === 0){ //si aucune pub est affiché alors

                $image = "/assets/image/banniere_arsenal_pro.png";
                $pubsDisplay = ["titre" => $titre, "image" => $image, "couleur" => '#ffffff', "url" => $url];

                return $this->render('/banniere.html.twig',['pubs' => $pubsDisplay, 'multi' => false]);

            } else {
                return $this->render('/banniere.html.twig',['pubs' => $pubsDisplay, "multi" => true]);
            }


        } else {

            $titrePub = $titre;
    
            $image = "/assets/image/banniere_arsenal_pro.png";

            $pubsDisplay = ["titre" => $titrePub, "image" => $image, "couleur" => "#ffffff", "url" => null];
            
            return $this->render('/banniere.html.twig',['pubs' => $pubsDisplay, 'multi' => false]);
        }
                
    }

    public function afficherAnnonce($home){
        $annonce = $this->entityManager->getRepository(Annonce::class)->findOneBy(['id' => 1, 'isAffiche' => true]); //annonce toujours statique, seulement modification de contenu texte
        return $this->render('bandeau_annonce.html.twig', [
            'uneAnnonce' => $annonce,
            'home' => $home
        ]);
    }

    public function pubRechercheParCategorie() : Response{ //OUTDATED ?
        $pub = $this->entityManager->getRepository(Pub::class)->findOneBy(['section' => 0]); //pour pub categories
        if($pub && $pub !== null){
            return $this->render('pubRechercheParCategorie.html.twig',[
                'image' => $pub->getImage(),
                'url' => $pub->getUrl(),
            ]);
        } 
        return new Response();
    }

}
