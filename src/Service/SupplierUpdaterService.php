<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\Calibre;
use App\Entity\Category;
use App\Entity\Famille;
use App\Entity\Fournisseurs;
use App\Entity\MailRetourStock;
use App\Entity\Marque;
use App\Entity\OrderDetails;
use App\Entity\Produit;
use App\Entity\ProduitListeAssociation;
use App\Entity\RemiseGroupe;
use App\Entity\SubCategory;
use App\Entity\Taille;
use App\Entity\VenteFlash;
use DateTimeImmutable;
use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use Throwable;

class SupplierUpdaterService extends AbstractController {

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function enleverCaracSpecial($str){ //
        return str_replace('-',' ',preg_replace('/[^A-Za-z0-9\s]/', '', $str)); //si ça ne match pas le regex alors caractère vide
    }

    public function insertBgmWinfield($array){

        $httpClient = new \GuzzleHttp\Client();
        $lesCategories = [ //valeurs qui correspondent à la BDD
            ['categorie' => 'B', 'id' => 2],
            ['categorie' => 'B1', 'id' => 2],
            ['categorie' => 'B2', 'id' => 2],
            ['categorie' => 'B3', 'id' => 2],
            ['categorie' => 'B4', 'id' => 2],
            ['categorie' => 'B5', 'id' => 2],
            ['categorie' => 'C', 'id' => 1],
            ['categorie' => 'C1', 'id' => 1],
            ['categorie' => 'C2', 'id' => 1],
            ['categorie' => 'C3', 'id' => 1],
            ['categorie' => 'C4', 'id' => 1],
        ];
        // dd($array);
        foreach($array as $arr){
            // $insertionError = false;
            try {
                $response = $httpClient->get($arr['url']); //try and catch response later
            } catch (Throwable $e){
                echo $arr['url'] . "\nCe lien de produit n'existe plus chez le fournisseur BGM !\n";
                // $insertionError = true;
            }
            $htmlString = (string) $response->getBody();
            libxml_use_internal_errors(true); //Enlever les erreurs XML/HTML
            $doc = new DOMDocument();
            $doc->loadHTML($htmlString);
            // dd($doc);
            $xpath = new DOMXPath($doc);
            $title = $xpath->evaluate('//h1')[0]->textContent;

            if($xpath->evaluate('//div[@class="product-reference"]/span')[0] !== null){ //REFERENCE CHECK DOM
                $reference = $xpath->evaluate('//div[@class="product-reference"]/span')[0]->textContent;
            } else {
                $reference = null;
            }

            $checkProduitURLExists = $this->entityManager->getRepository(Produit::class)->findOneBySlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title))));
            $checkProduitExists = $this->entityManager->getRepository(Produit::class)->findOneByReference($reference);
            if(!$checkProduitURLExists || $checkProduitExists == null){
                echo "Insertion : " . $arr['url'] . "\n";
                //WEB SCRAPING
                if($xpath->evaluate('//div[@class="product-short-description"]/p')[0] !== null){ //SOUS-TITRE CHECK DOM
                    $subtitle = $xpath->evaluate('//div[@class="product-short-description"]/p')[0]->textContent;
                } else {
                    $subtitle = $title;
                }
                $image = $xpath->evaluate('//div[@class="product-cover"]/img/@src')[0]->value;


                $prix = $xpath->evaluate('//span[@class="current-price-value"]/@content')[0]->value;
                if($xpath->evaluate('//div[@class="product-description"]/p')[0] !== null){ //DESCRIPTION CHECK DOM
                    $description = $xpath->evaluate('//div[@class="product-description"]/p')[0]->textContent;
                } else {
                    $description = "Descriptif complet à venir";
                }
                if($xpath->evaluate('//div[@class="product-manufacturer"]//a/img/@alt')[0] !== null){ //MARQUE CHECK DOM
                    $marque = $xpath->evaluate('//div[@class="product-manufacturer"]//a/img/@alt')[0]->value;
                } else {
                    $marque = null;
                }
                // dd($marque);
                $calibre = null;
                $categorie = null;
                // foreach($xpath->evaluate('//div[@class="container"]//nav/ol/li') as $key => $div){
                //     if(preg_match("/Modérateurs de son/i", $div->textContent)){
                //         $categorie = "Modérateurs de son";
                //     }
                    
                // }
                foreach($xpath->evaluate('//div[@class="cartouche"]//div/div/p') as $key => $div){
                    if($div->textContent !== null){
                        if(preg_match("/Calibre/i", $div->textContent)){ //CALIBRE DOM
                            $calibre = explode("Calibre", $div->textContent)[1];
                        }
                        if(preg_match("/Classé en catégorie/i", $div->textContent)){ //CATEGORIE DOM
                            $categorie = explode("Classé en catégorie", $div->textContent)[1];
                        }
                    }
                }


                $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName($marque);
                $leCalibre = $this->entityManager->getRepository(Calibre::class)->findOneByCalibre($calibre);
                $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName("BGM-Winfield"); //OBLIGATOIREMENT DANS LA BDD
                
                $entity = new Produit();  
                $entity->setName($title);
                if($laMarque !== null){
                    $entity->setMarque($laMarque);
                } else {
                    if($marque !== null){
                        $marqueNew = new Marque(); //sinon création de la marque
                        $marqueNew->setName($marque);
                        $this->entityManager->persist($marqueNew);
                        $entity->setMarque($marqueNew);
                    } else {
                        $entity->setMarque($this->entityManager->getRepository(Marque::class)->findOneById(40)); //Sans marque
                    }
             
                }
                if($leCalibre !== null){
                    $entity->setCalibres($leCalibre);
                } else {
                    if($calibre !== null){
                        $calibreNew = new Calibre(); //sinon création du calibre
                        $calibreNew->setCalibre($calibre);
                        $this->entityManager->persist($calibreNew);
                        $entity->setCalibres($calibreNew);
                    } else {
                        $entity->setCalibres(null);
                    }
                }

                $entity->setSubtitle($subtitle);
                $entity->setReference($reference);
                $entity->setReferenceAssociation($reference);
                $entity->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title)))); //lien unique pour pas que ça fasse des conflits
                $nomImage = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title)));
            
                // file_get_contents($image);
                file_put_contents('./public/uploads/' . $nomImage,  file_get_contents($image) .".jpg");
        
                $entity->setIllustration($nomImage); //obligatoire

                $entity->setDescription($description);
                $entity->setIsAffiche(false);
                $entity->setIsBest(false);
                $entity->setIsOccassion(false);
                $entity->setIsForcesOrdre(false);
                $entity->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($prix) * 100))));
                $entity->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval($prix) * 100)) * 0.97)); //prix promo à 3%
                $masse = 0.5;
                $entity->setCodeRga(null);
                $familleId = null;
                $subCategorieId = null;
                $categorieId = null;

                foreach($lesCategories as $uneCategorie){
                    if($categorie == $uneCategorie['categorie']){ //verifie si categorie B ou C correspond
                        $categorieId = $uneCategorie['id'];
                        if($categorieId === 1){ //SI CAT C
                            // $categorieId = 12; //alors catégorie = Munition CAT. C
                            // if(preg_match("/Carabine/i", $title) || preg_match("/Carabine/i", $subtitle)){
                            //     //
                            // }
                            $entity->setMasse(3); //3kg avg
                        }
                        if($categorieId === 2){ //SI CAT B
                            // $categorieId = 11; //alors catégorie = Munition CAT. B
                            // $categorieId = 2; // Catégorie B
                            if(preg_match("/Carabine/i", $title) || preg_match("/Carabine/i", $subtitle)){
                                $subCategorieId = 137; //carabines semi-auto
                                $masse = 3; //3kg avg
                                $categorieId = 2;
                            }
                            if(preg_match("/Pistolet/i", $title) || preg_match("/Pistolet/i", $subtitle)){
                                $subCategorieId = 247; //pistolet semi-auto
                                $familleId = 331; //pistolet cat B
                                $categorieId = 2;
                                $masse = 1; //1kg avg
                            }
                        }
                        $laSubCategorieId = $this->entityManager->getRepository(SubCategory::class)->findOneById($subCategorieId);
                        $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById($categorieId);
                        $entity->setCategory($laCategorie);
                        $entity->setSubCategory($laSubCategorieId);
                    } 
                }    
                
               
                if($categorieId == null){ // si pas de categories, m'en fout comment j'ai géré cette partie, c'est du spaghetti...
                     $entity->setCategory($this->entityManager->getRepository(Category::class)->findOneById(6)); //defaut accessoire
                     if(preg_match("/Canon/i", $title)){
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(112)); //canons
                     }
                     if(preg_match("/nettoyage/i", $title) || preg_match("/cleaning/i", $title) || preg_match("/ripcord/i", $title)){
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(29)); //set de nettoyage
                     }
                     if(preg_match("/Ecouvillons/i", $title) || preg_match("/Ecouvillons/i", $subtitle)){
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(29)); //set de nettoyage
                        $familleId = 152; //ecouvillons
                    }
                     if(preg_match("/Carabine/i", $title) || preg_match("/Carabine/i", $subtitle)){
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(137)); //carabines semi auto
                        $entity->setCategory($this->entityManager->getRepository(Category::class)->findOneById(2)); //cat B
                        $masse = 3; //3kg avg
                    }
                    if(preg_match("/Pistolet semi-automatique/i", $subtitle) || preg_match("/Pistolet/i", $title)){
                        $entity->setCategory($this->entityManager->getRepository(Category::class)->findOneById(2)); //cat B
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(247)); //pistolet cat b
                        $masse = 1;
                    }
                    if(preg_match("/Lampe tactique/i", $subtitle) || preg_match("/Lampe/i", $title)){
                        //
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(80)); //Lampes torches
                        $familleId = 470;
                    }
                    if(preg_match("/viseur/i", $subtitle) || preg_match("/viseur/i", $title) || preg_match("/red dot/i", $title)){
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(37)); //viseur point rouge
                        $familleId = 97;
                    }
                    if(preg_match("/Culasse/i", $subtitle)){
                        $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(123)); //accessoire de tir
                        $familleId = 30;
                    }
                    if(preg_match("/Collier/i", $title) || preg_match("/Collier/i", $subtitle)){
                        $familleId = 588; //collier de montage
                    }
    
                    // if(preg_match("/Garde-main/i", $title) || preg_match("/Garde main/i", $title)){
                    //     // $entity->setSubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById(123)); //accessoire de tir
                    // }
                }

                if(preg_match("/Silencieux/i", $title) || preg_match("/Compensateur/i", $title) || preg_match("/Compensateur/i", $description)){
                    $laFamille = $this->entityManager->getRepository(Famille::class)->findOneByName("Silencieux & frein de bouche");
                    $entity->setCategory($this->entityManager->getRepository(Category::class)->findOneById(6)); //accessoire
                } else {
                    $laFamille = $this->entityManager->getRepository(Famille::class)->findOneById($familleId);
                }
                $entity->setFamille($laFamille);
                $entity->setMasse($masse);
                $entity->setQuantite(0);
                $entity->setCaracteristique("-");
                $entity->setFournisseurs($fournisseur);
                // dd($entity);
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                echo ($entity->getName()) . " a été inséré.\n";
                // dd("sus");
            }
        }
    }    

    public function insertHumbert($fichier,$fournisseurNom){ //insertion automatique de produits europarm
            $httpClient = new \GuzzleHttp\Client();
            $lesCategories = [ //valeurs qui correspondent à la BDD
                ['categorie' => 'D', 'id' => 3, 'sousCategorie' => null],
                ['categorie' => 'C', 'id' => 1, 'sousCategorie' => null],
                ['categorie' => 'Armes de poing', 'id' => 2, 'sousCategorie' => 'Armes de poing réglementées'],
                ['categorie' => 'Optique', 'id' => 5, 'sousCategorie' => null], //equipement
                ['categorie' => 'Munitions rayées réglementées', 'id' => 11],
                ['categorie' => 'Munitions 22 lr', 'id' => 11, 'sousCategorie' => null],
                ['categorie' => 'A', 'id' => 9, 'sousCategorie' => null],
                ['categorie' => 'Vente libre', 'id' => 8, 'sousCategorie' => null],
                ['categorie' => 'Accessoires', 'id' => 6, 'sousCategorie' => null],
                ['categorie' => 'Accessoire de tir', 'id' => 6, 'sousCategorie' => null],
            ];
            $lesCatArmes = [
                ['categorie' => 'B10', 'id' => 2, 'sousCategorie' => null],
                ['categorie' => 'B1', 'id' => 2, 'sousCategorie' => null],
                ['categorie' => 'B4b', 'id' => 2, 'sousCategorie' => null],
                ['categorie' => 'C8', 'id' => 1, 'sousCategorie' => null],
                ['categorie' => 'C1b', 'id' => 1, 'sousCategorie' => null],

            ];
            $reader = new ReaderXlsx();
            
            $reader->setReadDataOnly(true); //en lecture seulement
            
            $spreadsheet = $reader->load($fichier); //chargement du xls
            $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
            $data = $sheet->toArray(); //conversion excel en tableau
            $calibre = null;
            $rga = null;
            $poids = null;
            $couleur = null;
            $description = $illustration = $illustrationUn = $illustrationDeux = null;
            foreach($data[0] as $key => $index){
                // echo $key, $index;
                switch($index){
                    case 'marque':
                        $marque = $key;
                    break;
                    case 'libellemodele':
                        $subtitle = $key;
                    break;
                    case 'commentairemodele':
                        $description = $key;
                    break;
                    case 'codearticle':
                        $reference = $key;
                    break;
                    case 'libelleartequa':
                        $name = $key;
                    break;
                    case 'prixttc':
                        $price = $key;
                    break;
                    case 'calibre':
                        $calibre = $key;
                    break;
                    case 'poids':
                        $poids = $key;
                    break;
                    case 'coderga':
                        $rga = $key;
                    break;
                    case 'visuel':
                        $illustration = $key;
                    break;
                    case 'visuelcomp1':
                        $illustrationUn = $key;
                    break;
                    case 'visuelcomp2':
                        $illustrationDeux = $key;
                    break;
                    case 'famille':
                        $subCategory = $key;
                    break;
                    case 'catarme':
                        $categories = $key;
                    break;
                    case 'couleur':
                        $couleur = $key;
                    break;
                }
            }
            for($j = 1; $j < count($data) -1; $j++){ // -1 car une cellule de l'excel ne sont pas utilisés pour le produits
                $produitExists = $this->entityManager->getRepository(Produit::class)->findOneByReference($data[$j][$reference]);
                if(!$produitExists){

                    $categorieCheckStepOne = false;
                    $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName($data[$j][$marque]);
                    if($calibre){
                        $leCalibre = $this->entityManager->getRepository(Calibre::class)->findOneByCalibre($data[$j][$calibre]);
                    } else {
                        $leCalibre = null;
                    }
                    $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName($fournisseurNom); //OBLIGATOIREMENT DANS LA BDD
                    
                    $entity = new Produit();  
                    if($couleur){
                        $entity->setName($data[$j][$name] . " | " . $data[$j][$couleur]);
                    } else {
                        $entity->setName($data[$j][$name]);
                    }
                    if($laMarque){
                        $entity->setMarque($laMarque);
                    } else {
                        $marqueNew = new Marque(); //sinon création de la marque
                        $marqueNew->setName($data[$j][$marque]);
                        $this->entityManager->persist($marqueNew);
                        $entity->setMarque($marqueNew);
                    }
                    if($leCalibre){
                        $entity->setCalibres($leCalibre);
                    } else {
                        if($calibre !== null && !empty($data[$j][$calibre])){
                            $calibreNew = new Calibre(); //sinon création du calibre
                            $calibreNew->setCalibre($data[$j][$calibre]);
                            $this->entityManager->persist($calibreNew);
                            $entity->setCalibres($calibreNew);
                        } else {
                            $entity->setCalibres(null);
                        }
                    }
                    if($data[$j][$subtitle] !== null){
                    $entity->setSubtitle($data[$j][$subtitle]);
                    } else {
                        $entity->setSubtitle($data[$j][$name]);
                    }
                    $entity->setReference($data[$j][$reference]);
                    
                    $entity->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$j][$name])) .'-'.  substr(uniqid(),6,3))); //lien unique pour pas que ça fasse des conflits
                    
                    if($illustration){
                        // if($data[$j][$illustration] !== null){
                            //web scraping pour avoir toutes les premières images d'un produit humbert
                            $response = $httpClient->get('https://www.humbert.com/fr/search?q=' . $data[$j][$reference]); //fetch le lien comme une API | try and catch response later
                            $htmlString = (string) $response->getBody();
                            libxml_use_internal_errors(true); //Enlever les erreurs XML/HTML
                            $doc = new DOMDocument();
                            $doc->loadHTML($htmlString);
                            $xpath = new DOMXPath($doc);
                            if($xpath->evaluate('//img[@class="img-fluid"]/@src')[0]){
                                $imageUrl = $xpath->evaluate('//img[@class="img-fluid"]/@src')[0]->value;
                            } else {
                                $imageUrl = null;
                            }
                            if($imageUrl){
                                $curl = curl_init();
                                //permet de télécharger l'image comme si c'était un utilisateur normal, pour obtenir plus simplement l'image en le transformant
                                curl_setopt($curl, CURLOPT_URL, $imageUrl);
                                curl_setopt($curl, CURLOPT_HEADER, 0);
                                curl_setopt($curl, CURLOPT_TIMEOUT, 300);
                                curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 8.0; Trident/4.0)');
                                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        
                                $imgData = curl_exec($curl);
                                curl_close($curl); //fermer

                                //file_get_contents($imageUrl); ne fonctionne pas car le lien ne contient pas d'extension d'image si non transformé.
                                $illustrationNew = $entity->getSlug();
                                file_put_contents('./public/uploads/' . $illustrationNew . '.jpg',  $imgData);

                                $entity->setIllustration($illustrationNew . ".jpg"); //obligatoire
                            }
                        //}
                    }

                    if($imageUrl !== null){ //si pas de image principale, on saute l'insertion
                        if($description){ //si index description existe
                            if($data[$j][$description] !== null){ //si description vide
                                $entity->setDescription($data[$j][$description]);
                            } else {
                                $entity->setDescription("Descriptif complet à venir pour " . $data[$j][$name] . ".");
                            }
                        } else {
                            $entity->setDescription("Descriptif complet à venir pour " . $data[$j][$name] . "."); //pour pas avoir d'erreur de NULL
                        }
                        $entity->setIsAffiche(false);
                        $entity->setIsBest(false);
                        $entity->setIsOccassion(false);
                        $entity->setIsForcesOrdre(false);
                        $entity->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($data[$j][$price]) * 100))));
                        $entity->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval($data[$j][$price]) * 100)) * 0.97));
                        if($poids){
                            $entity->setMasse(floatval($data[$j][$poids])/1000); //HUMBERT en gramme, nous en kg
                        } else {
                            $entity->setMasse(0.5);
                        }
                        if($rga !== null){
                            $entity->setCodeRga($data[$j][$rga]);
                        } else {
                            $entity->setCodeRga(null);
                        }

                        foreach($lesCatArmes as $uneCatArme){
                            if($data[$j][$categories] == $uneCatArme['categorie']){
                                $categorieId = $uneCatArme['id'];
                                if(preg_match("/Munition/i", $data[$j][$subCategory]) || preg_match("/Cartouche/i", $data[$j][$subCategory])){
                                    if($categorieId === 1){
                                        $categorieId = 12;
                                    }
                                    if($categorieId === 2){
                                        $categorieId = 11;
                                    }
                                    if($categorieId === 3){
                                        $categorieId = 13;
                                    }
                                }
                                $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById($categorieId);
                                $entity->setCategory($laCategorie);
                                $categorieCheckStepOne = true;
                            } else {
                                $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById(8); //vente libre
                                $entity->setCategory($laCategorie);
                            }
                        }

                        if(!$categorieCheckStepOne){   
                            foreach($lesCategories as $uneCategorie){
                                if($data[$j][$subCategory] == $uneCategorie['categorie']){ //verifie si categorie correspond
                                    $categorieId = $uneCategorie['id'];
                                    $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById($categorieId);
                                    $laSubCategory = $this->entityManager->getRepository(SubCategory::class)->findOneByName($data[$j][$subCategory]);
                                    if($laSubCategory){
                                        $entity->setSubCategory($laSubCategory);
                                    } else {
                                        if($uneCategorie['sousCategorie']){
                                            $laSubCategoryTry = $this->entityManager->getRepository(SubCategory::class)->findOneByName($uneCategorie['sousCategorie']);
                                            if(!$laSubCategoryTry){
                                                $subCategorieNew = new SubCategory(); //sinon création de la marque
                                                $subCategorieNew->setName($data[$j][$subCategory]);
                                                $this->entityManager->persist($subCategorieNew);
                                                $entity->setSubCategory($subCategorieNew);
                                            }
                                        }
                                    }
                                    $entity->setCategory($laCategorie);
                                } else {
                                    $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById(8); //vente libre
                                    $entity->setCategory($laCategorie);
                                }
                            } 
                        }

                        $entity->setQuantite(0);
                        $entity->setCaracteristique("-");
                        $entity->setFournisseurs($fournisseur);

                        if($illustrationUn !== null){
                            if($data[$j][$illustrationUn] !== null){
                                $entity->setIllustrationUn("Medium_" . $data[$j][$illustrationUn] .".jpg");
                            }
                        }
                        if($illustrationDeux !== null){
                            if($data[$j][$illustrationDeux] !== null){
                                $entity->setIllustrationDeux("Medium_" . $data[$j][$illustrationDeux] .".jpg");
                            }
                        }
                        
                        // dd($illustrationUn, $illustrationDeux, $laMarque);
                        // dd($entity);
                        $this->entityManager->persist($entity);
                        $this->entityManager->flush();
                        echo ($entity->getName()) . " a été inséré.\n";
                    } else {
                        echo "Ce produit n'a pas d'image ou n'a pas été trouvé depuis le site humbert.com\n";
                    }
                } else {
                    echo "Ce produit existe déjà !\n";
                }
            }

            return new Response();
        }

        public function insertA10(){
            $url = "http://phototheque.toe-concept.com/flux/export/revendeur.csv";
            file_put_contents('./suppliers/a10/revendeur-a10.csv',  file_get_contents($url)); //téléchargement du CSV

            $lesSubCategories = [ //valeurs qui correspondent à la BDD
                ['targetCategorie' => 'Chaussures', 'id' => 151], //Chaussures & gants
                ['targetCategorie' => 'Gants', 'id' => 151], //Chaussures & gants
                ['targetCategorie' => 'Ceinturons / Pochettes Cordura', 'id' => 207], //Ceinturon/Ceinture
                ['targetCategorie' => 'Holsters / Porte-chargeurs', 'id' => 95], //Holsters & équipement
                ['targetCategorie' => 'Accessoires', 'id' => 191], //Holsters & accessoires
                ['targetCategorie' => 'Lampes', 'id' => 81], //Lampes tactiques
                ['targetCategorie' => 'Optiques', 'id' => 240], //Montage optiques
                ['targetCategorie' => "Entretien de l'arme", 'id' => 162], //Montage optiques
                ['targetCategorie' => "Vêtements", 'id' => 227], //Vêtements et bottes
                ['targetCategorie' => "Couteaux", 'id' => 114], //Couteaux tactiques
                ['targetCategorie' => "Equipement défense / Menottes", 'id' => 49], //Matraques, nunchaku & étoiles
                ['targetCategorie' => "Protections individuelles", 'id' => 61], //Protection auditives, masques & lunetterie
            ];

            $reader = new ReaderCsv();
            $reader->setReadDataOnly(true); //en lecture seulement
        
            $spreadsheet = $reader->load('./suppliers/a10/revendeur-a10.csv'); //chargement du csv
            $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
            $data = $sheet->toArray(); //conversion excel en tableau

            $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName("A10 Equipements"); //OBLIGATOIREMENT DANS LA BDD
            
            for($i = 1; $i < count($data); $i++){
                $subCategoryChecker = false;
                $imagePasse = true;
                $leProduitSlugCheck = $this->entityManager->getRepository(Produit::class)->findOneBySlug( strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$i][2] . "-" . $data[$i][0]))) ); //check si slug existe pour eviter doublons
                $produitExists = $this->entityManager->getRepository(Produit::class)->findOneByReference($data[$i][0]);
                $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById(5); //équipements
                try{
                    file_get_contents($data[$i][13]);
                } catch (Throwable $e){
                    echo "Image inexistante \n";
                    $imagePasse = false; //saute l'insertion du produit sans image afin d'éviter un blocage (quelques images a10 n'existent pas);
                }
                if(!$produitExists && !$leProduitSlugCheck && $imagePasse){
                    $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName($data[$i][3]);
                    $laFamille = $this->entityManager->getRepository(Famille::class)->findOneByName($data[$i][11]);
                    $laTaille = $this->entityManager->getRepository(Taille::class)->findOneByTaille($data[$i][4]);
                    $entity = new Produit();  
                    $entity->setName($data[$i][2]); //nom
                    $entity->setSubtitle($data[$i][3]. " " . $data[$i][10]); //marque + type objet
                    $entity->setReference($data[$i][0]); //ref
                    $entity->setFournisseurs($fournisseur);
                    $entity->setReferenceAssociation(explode('-', $data[$i][0])[0]); 
                    if($data[$i][7] !== null || !empty($data[$i][7])){ //descriptif
                        $entity->setDescription($data[$i][7]);
                    } else {
                        $entity->setDescription("Descriptif complet à venir pour " . $entity->getName());
                    }
                    $entity->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$i][2] . "-" . $data[$i][0])))); //lien unique pour pas que ça fasse des conflits

                    $entity->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($data[$i][14]) * 100)))); //prix
                    $entity->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval(($data[$i][14]) * 100) * 0.97)))); //prix promo -3%

                    if($data[$i][19] == "En stock"){
                        $entity->setQuantite(1);
                    } else {
                        $entity->setQuantite(0);
                    }
                    $entity->setIsAffiche(true);
                    if($data[$i][6] > 0){
                        $entity->setMasse($data[$i][6]);
                    } else {
                        $entity->setMasse(1);
                    }
                    $nomImage = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$i][2])));
                    file_put_contents('./public/uploads/' . $nomImage,  file_get_contents($data[$i][13]));
                    $entity->setIllustration($nomImage); //obligatoire

                    if($laMarque){
                        $entity->setMarque($laMarque);
                    } else {
                        if($data[$i][3] !== null || !empty($data[$i][3])){ //si nom de la marque pas vide
                            $marque = new Marque(); //sinon création de la marque
                            $marque->setName($data[$i][3]);
                            $this->entityManager->persist($marque);
                            $this->entityManager->flush(); //maj BDD
                        } else {
                            $marque = $this->entityManager->getRepository(Marque::class)->findOneById(261); //si pas de nom au marque du produit alors on met une marque générique
                        }
                        $entity->setMarque($marque);
                    }
                    if($laTaille){
                        $entity->setTaille($laTaille);
                    } else {
                        if($data[$i][4] !== null || !empty($data[$i][4])){ //si nom de la marque pas vide
                            $taille = new Taille(); //sinon création de la taille
                            $taille->setTaille($data[$i][4]);
                            $this->entityManager->persist($taille);
                            $this->entityManager->flush(); //maj BDD
                            if($taille){
                                $entity->setTaille($taille);
                            }
                        } 
                    }
                    $entity->setCategory($laCategorie);
                    if($data[$i][10] !== null){
                        foreach($lesSubCategories as $subCategorie){
                            if($subCategorie["targetCategorie"] == $data[$i][10]){
                                $laCategorie = $this->entityManager->getRepository(SubCategory::class)->findOneById($subCategorie["id"]);
                                $entity->setSubCategory($laCategorie);
                                $subCategoryChecker = true;
                            }
                        }
                    }
                    if(!$subCategoryChecker && $data[$i][10] !== null){ //sinon création sous-catégorie
                        $newSubCategory = new SubCategory();
                        $newSubCategory->setName($data[$i][10]);
                        $this->entityManager->persist($newSubCategory);
                        $this->entityManager->flush();
                        if($newSubCategory){
                            $entity->setSubCategory($newSubCategory);
                        }
                    }

                    if(!$laFamille && $data[$i][11] !== null){
                        $subCategorieTwo = new Famille();
                        $subCategorieTwo->setName($data[$i][11]);
                        $this->entityManager->persist($subCategorieTwo);
                        $this->entityManager->flush();
                        // echo $subCategorieTwo->getName() . " a été inséré\n";
                        if($subCategorieTwo){
                        $entity->setFamille($subCategorieTwo);
                        }
                    } 
                    $entity->setIsAffiche(true); //est affiche sur le site
                    $entity->setIsBest(false);
                    $entity->setIsOccassion(false);
                    $entity->setIsForcesOrdre(false);
                    $entity->setCodeRga(null);
                    $entity->setCaracteristique("-");
                    $entity->setIsCarteCadeau(false);
                    $entity->setIsForcesOrdre(false);
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    echo ($entity->getName()) . " à été inséré.\n";
                    //  dd($data[$i]);
                }
            } 
            return new Response();
        }
        public function updateProduitsA10(){

            $reader = new ReaderCsv();
            $reader->setReadDataOnly(true); //en lecture seulement
        
            $spreadsheet = $reader->load('./suppliers/a10/revendeur-a10.csv'); //chargement du csv
            $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
            $data = $sheet->toArray(); //conversion excel en tableau
            for($i = 1; $i < count($data); $i++){
                $produit = $this->entityManager->getRepository(Produit::class)->findOneByReference($data[$i][0]);

                if($produit){
                    $produit->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($data[$i][14]) * 100)))); //prix
                    $produit->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval(($data[$i][14]) * 100) * 0.97)))); //prix promo -3%

                    if($data[$i][19] == "En stock"){
                        $produit->setQuantite(1);
                    } else {
                        $produit->setQuantite(0);
                    }
                    $this->entityManager->flush();
                    echo ($produit->getName()) . " - " . $produit->getId() ." a été mise à jour.\n";

                }
            }
            return new Response();
        }

        public function insertProduitDimatex($array){

            $httpClient = new \GuzzleHttp\Client();

            $laListeFamilles = [ //valeurs qui correspondent à la BDD
                ['targetCategorie' => 'Sacs', 'id' => 427], 
                ['targetCategorie' => 'Paquetage', 'id' => 427], 
                ['targetCategorie' => 'Ceinturons / Pochettes Cordura', 'id' => 279], //Ceinturon et brelages
            ];
            foreach($array as $arr){
                $response = $httpClient->get($arr['url']); //try and catch response later
                $htmlString = (string) $response->getBody();
                libxml_use_internal_errors(true); //Enlever les erreurs XML/HTML
                $doc = new DOMDocument();
                $doc->loadHTML($htmlString);
                // dd($doc);
                $xpath = new DOMXPath($doc);
                $title = $xpath->evaluate('//h1')[0]->textContent;
                $images = [];
                $subCategoryChecker = false;
                $checkProduitURLExists = $this->entityManager->getRepository(Produit::class)->findOneBySlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title))));
                if(!$checkProduitURLExists){
                    //WEB SCRAPING
                    if($xpath->evaluate('//div[@class="product-short-description"]/p')[0] !== null){ //SOUS-TITRE CHECK DOM
                        $subtitle = $xpath->evaluate('//div[@class="product-short-description"]/p')[0]->textContent;
                    } else {
                        $subtitle = $title;
                    }
                    // dd($xpath->evaluate('//div[@class="thumb-container"]/img/@src')[0]);
                    for($y = 0; $y < 4; $y++){ //images
                        if($xpath->evaluate('//div[@class="thumb-container"]/img/@src')[$y] !== null){
                            $images[] = $xpath->evaluate('//div[@class="thumb-container"]/img/@src')[$y]->value;
                        }
                    }
    
                    if($xpath->evaluate('//div[@id="refence-product-description"]')[0] !== null){ //REFERENCE CHECK DOM
                        $reference = explode("Référence : ", $xpath->evaluate('//div[@id="refence-product-description"]')[0]->textContent)[1];
                    } else {
                        $reference = null;
                    }
                    $prix = $xpath->evaluate('//div[@class="current-price"]/span/@content')[0]->value;
                    if($xpath->evaluate('//div[@class="product-description"]/p')[0] !== null){ //DESCRIPTION CHECK DOM
                        $description = $xpath->evaluate('//div[@class="product-description"]/p')[0]->textContent;
                    } else {
                        $description = "Descriptif complet à venir";
                    }
                    if($xpath->evaluate('//div[@class="product-manufacturer"]//a/img/@alt')[0] !== null){ //MARQUE CHECK DOM
                        $marque = $xpath->evaluate('//div[@class="product-manufacturer"]//a/img/@alt')[0]->value;
                    } else {
                        $marque ="Dimatex";
                    }
                    $stock = null;
                    if($xpath->evaluate('//span[@id="product-availability"]')[0] !== null){ //DESCRIPTION CHECK DOM
                        $stock = str_replace('\n', '', $xpath->evaluate('//span[@id="product-availability"]')[0]->textContent);
                        if(empty($stock)){
                            $stock = null;
                        }
                        // dd($stock);
                    }
                    // dd($marque);
                    $famille = null;
                    $masse = null;
                    if($xpath->evaluate('//a[@class="gamme"]/span')[0] !== null){ 
                        $famille = str_replace('"', '', $xpath->evaluate('//a[@class="gamme"]/span')[0]->textContent);
                    }
                    // dd($xpath->evaluate('//dl[@class="data-sheet"]/dd'));
                    foreach($xpath->evaluate('//dl[@class="data-sheet"]/dd') as $key => $div){
                        // dd($div);
                        if($div->textContent !== null){
                            if(preg_match("/kg/i", $div->textContent)){ //masse
                                $masse = floatval(explode(" kg", $div->textContent)[0]);
                                // dd($masse);
                            }
                        }
                    }
    
    
                    $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName($marque);
                    // $laFamille = $this->entityManager->getRepository(Famille::class)->findOneByName($famille);
                    $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName("Dimatex"); //OBLIGATOIREMENT DANS LA BDD
                    $subCategorie = $this->entityManager->getRepository(SubCategory::class)->findOneByName("Dimatex"); //OBLIGATOIREMENT DANS LA BDD
                    $categorie = $this->entityManager->getRepository(Category::class)->findOneById(5);
                    $entity = new Produit();  
                    $entity->setName($title);

                    if($laMarque !== null){
                        $entity->setMarque($laMarque);
                    }
    
                    $entity->setSubtitle($subtitle);
                    $entity->setReference($reference);
                    $entity->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title)))); //lien unique pour pas que ça fasse des conflits
                    $nomImage = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title)));
                    // dd($images);
                    foreach($images as $key => $image){ //insertion images
                        if($image){
                            switch($key){
                                case 0 :
                                    file_put_contents('./public/uploads/' . "1-" . $nomImage . ".jpg",  file_get_contents($image));
                                    $entity->setIllustration("1-" . $nomImage . ".jpg"); //obligatoire 
                                break;
                                case 1 :
                                    file_put_contents('./public/uploads/' . "2-" . $nomImage . ".jpg",  file_get_contents($image));
                                    $entity->setIllustrationUn("2-" . $nomImage . ".jpg"); //obligatoire 
                                break;
                                case 2 :
                                    file_put_contents('./public/uploads/' . "3-" . $nomImage . ".jpg",  file_get_contents($image));
                                    $entity->setIllustrationDeux("3-" . $nomImage . ".jpg"); //obligatoire 
                                break;
                                case 3 :
                                    file_put_contents('./public/uploads/' . "4-" . $nomImage . ".jpg",  file_get_contents($image));
                                    $entity->setIllustrationTrois("4-" . $nomImage . ".jpg"); //obligatoire 
                                break;
                                case 4 :
                                    file_put_contents('./public/uploads/' . "5-" . $nomImage . ".jpg",  file_get_contents($image));
                                    $entity->setIllustrationQuatre("5-" . $nomImage . ".jpg"); //obligatoire 
                                break;
                            }
                        }
                    }
                    // dd($images[0]);
                    $entity->setDescription($description);
                    $entity->setIsAffiche(false);
                    $entity->setIsBest(false);
                    $entity->setIsOccassion(false);
                    $entity->setIsForcesOrdre(false);
                    $entity->setIsCarteCadeau(false);
                    $entity->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($prix) * 100))));
                    // $entity->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval($prix) * 100)) * 0.97)); //prix promo à 3%

                    if($masse){
                        $entity->setMasse($masse);
                    } else {
                        $entity->setMasse(0.5);
                    }
                    
                    if($famille !== null){
                        foreach($laListeFamilles as $sb){
                            if($sb["targetCategorie"] == $famille){
                                $laFamille = $this->entityManager->getRepository(Famille::class)->findOneById($sb["id"]);
                                $entity->setSubCategory($laFamille);
                                $subCategoryChecker = true;
                            }
                        }
                    }
                    if(!$subCategoryChecker && $famille !== null){ //sinon création sous-catégorie
                        $newFamille = new Famille();
                        $newFamille->setName($famille);
                        $this->entityManager->persist($newFamille);
                        $this->entityManager->flush();
                        if($newFamille){
                            $entity->setFamille($newFamille);
                        }
                    }
                    $entity->setCategory($categorie);
                    $entity->setSubCategory($subCategorie);
                    $entity->setCodeRga(null);
                    $familleId = null;
                    
                    if(preg_match("/Silencieux/i", $title)){
                        $laFamille = $this->entityManager->getRepository(Famille::class)->findOneByName("Silencieux & frein de bouche");
                    } else {
                        $laFamille = $this->entityManager->getRepository(Famille::class)->findOneById($familleId);
                    }
                    $entity->setFamille($laFamille);
                    if($stock){
                        $entity->setQuantite(0);
                    } else {
                        $entity->setQuantite(1);
                    }
                    $entity->setCaracteristique("-");
                    $entity->setFournisseurs($fournisseur);
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    echo ($entity->getName()) . " a été inséré.\n";
                    // dd("sus");
                }
            }
        }   
        
        public function updateProduitsDimatex($array){

            $httpClient = new \GuzzleHttp\Client();

            foreach($array as $arr){
                $response = $httpClient->get($arr['url']); //try and catch response later
                $htmlString = (string) $response->getBody();
                libxml_use_internal_errors(true); //Enlever les erreurs XML/HTML
                $doc = new DOMDocument();
                $doc->loadHTML($htmlString);
                // dd($doc);
                $xpath = new DOMXPath($doc);
                $title = $xpath->evaluate('//h1')[0]->textContent;
                $produit = $this->entityManager->getRepository(Produit::class)->findOneBySlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($title))));
                if($produit){
                    $prix = $xpath->evaluate('//div[@class="current-price"]/span/@content')[0]->value;

                    $stock = null;
                    if($xpath->evaluate('//span[@id="product-availability"]')[0] !== null){ //DESCRIPTION CHECK DOM
                        $stock = str_replace('\n', '', $xpath->evaluate('//span[@id="product-availability"]')[0]->textContent);
                        if(empty($stock)){
                            $stock = null;
                        }
                        // dd($stock);
                    }
                    $produit->setPrice($prix);
                    if($stock){
                        $produit->setQuantite(0);
                    } else {
                        $produit->setQuantite(1);
                    }
                    $this->entityManager->flush();

                }
            }
            return new Response();
        }

        public function insertDCAFrance(){
            $url = "https://www.dca-france.com/fichiers/100/produits.csv";
            file_put_contents('./suppliers/dca-france/revendeur-dca.csv',  file_get_contents($url)); //téléchargement du CSV

            $lesSubCategories = [ //valeurs qui correspondent à la BDD
                ['targetCategorie' => 'Chaussures', 'id' => 151], //Chaussures & gants
                ['targetCategorie' => "Gants", "id" => 151],
                ['targetCategorie' => 'Aerosols de defense', 'id' => 241], //Chaussures & gants
                ['targetCategorie' => 'Ceinturons / Pochettes Cordura', 'id' => 207], //Ceinturon/Ceinture
                ['targetCategorie' => 'Holsters', 'id' => 95], //Holsters & équipement
                ['targetCategorie' => 'Accessoires', 'id' => 191], //Holsters & accessoires
                ['targetCategorie' => 'Sac étanche', 'id' => 52], //sacs
                ['targetCategorie' => "Lampes-torches tactiques", 'id' => 80],
                ['targetCategorie' => "Lampes fontales", 'id' => 81],
                ['targetCategorie' => 'Sacs a dos + de 30 L', 'id' => 52], //sacs
                ['targetCategorie' => 'Sacs cargo à roulettes', 'id' => 52], //sacs
                ['targetCategorie' => 'Sacs de transport', 'id' => 52],
                ['targetCategorie' => 'Optiques', 'id' => 240], //Montage optiques
                ['targetCategorie' => "Entretien de l'arme", 'id' => 162], //Montage optiques
                ['targetCategorie' => "Vêtements", 'id' => 227], //Vêtements et bottes
                ['targetCategorie' => "Couteaux", 'id' => 114], //Couteaux tactiques
                ['targetCategorie' => "Equipement défense / Menottes", 'id' => 49], //Matraques, nunchaku & étoiles
                ['targetCategorie' => "Protections individuelles", 'id' => 61], //Protection auditives, masques & lunetterie
                ['targetCategorie' => "Casquettes", 'id' => 98],
                ['targetCategorie' => "Couteaux pliables" , "id" => 78],
                ['targetCategorie' => "Pochettes", "id" => 195],
                ['targetCategorie' => "Vetements en laine Mérinos", "id" => 227],
                ['targetCategorie' => "Polo Merinos", "id" => 227],
            ];

            $reader = new ReaderCsv();
            $reader->setReadDataOnly(true); //en lecture seulement
            $reader->setInputEncoding('CP1252'); //pour réparer les erreurs du format UTF-8

            $spreadsheet = $reader->load('./suppliers/dca-france/revendeur-dca.csv'); //chargement du csv
            $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
            $data = $sheet->toArray(); //conversion excel en tableau

            $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName("DCA France"); //OBLIGATOIREMENT DANS LA BDD
            $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName("DCA France");
            for($i = 1; $i < count($data); $i++){
                $subCategoryChecker = false;
                $imagePasse = true;
                $leProduitSlugCheck = $this->entityManager->getRepository(Produit::class)->findOneBySlug( strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$i][2] . "-" . $data[$i][0]))) ); //check si slug existe pour eviter doublons
                $produitExists = $this->entityManager->getRepository(Produit::class)->findOneByReference($data[$i][1]);
                $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById(5); //équipements
                try{
                    file_get_contents($data[$i][17]);
                } catch (Throwable $e){
                    echo "Image inexistante \n";
                    $imagePasse = false; //saute l'insertion du produit sans image afin d'éviter un blocage (quelques images a10 n'existent pas);
                }
                if(!$produitExists && !$leProduitSlugCheck && $imagePasse && $data[$i][15] !== "Tee-shirts personnalises" && $data[$i][15] !== "Serviettes de toilette" && $data[$i][15] !== "Accueil" && $data[$i][15] !== "Tongs" && $data[$i][15] !== "Classeurs et porte-documents" && $data[$i][15] !== "DIVERS" && $data[$i][15] !== "EQUIPEMENT"){ //pas de sous-catégorie t-shirts perso
                    // $laFamille = $this->entityManager->getRepository(Famille::class)->findOneByName($data[$i][11]);
                    $laTaille = $this->entityManager->getRepository(Taille::class)->findOneByTaille($data[$i][7]);
                    $entity = new Produit();  
                    $entity->setName($data[$i][3] . " " . $data[$i][7]); //nom + déclinaison
                    $entity->setSubtitle($data[$i][15]);
                    $entity->setReference($data[$i][1]); //ref
                    $entity->setFournisseurs($fournisseur);
                    $entity->setReferenceAssociation($data[$i][0]); 
                    if($data[$i][16] !== null || !empty($data[$i][16])){ //descriptif
                        $entity->setDescription($data[$i][16]);
                    } else {
                        $entity->setDescription("Descriptif complet à venir pour " . $entity->getName());
                    }
                    $entity->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$i][3] . "-" . $data[$i][1])))); //lien unique pour pas que ça fasse des conflits

                    $entity->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($data[$i][5]) * 100)))); //prix
                    $entity->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval(($data[$i][5]) * 100) * 0.97)))); //prix promo -3%

                    $entity->setQuantite($data[$i][10]);
    
                    $entity->setIsAffiche(true);
                    if($data[$i][11] > 0){
                        $entity->setMasse($data[$i][11]);
                    } else {
                        $entity->setMasse(1);
                    }
                    $nomImage = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($data[$i][3])));
                    file_put_contents('./public/uploads/' . $nomImage,  file_get_contents($data[$i][17]));
                    $entity->setIllustration($nomImage); //obligatoire

                    if($laMarque){
                        $entity->setMarque($laMarque);
                    } else {
                        if($data[$i][3] !== null || !empty($data[$i][3])){ //si nom de la marque pas vide
                            $marque = new Marque(); //sinon création de la marque
                            $marque->setName($data[$i][3]);
                            $this->entityManager->persist($marque);
                            $this->entityManager->flush(); //maj BDD
                        } else {
                            $marque = $this->entityManager->getRepository(Marque::class)->findOneById(261); //si pas de nom au marque du produit alors on met une marque générique
                        }
                        $entity->setMarque($marque);
                    }
                    if($laTaille){
                        $entity->setTaille($laTaille);
                    } else {
                        if($data[$i][7] !== null || !empty($data[$i][7])){ //si nom de la marque pas vide
                            $taille = new Taille(); //sinon création de la taille
                            $taille->setTaille($data[$i][7]);
                            $this->entityManager->persist($taille);
                            $this->entityManager->flush(); //maj BDD
                            if($taille){
                                $entity->setTaille($taille);
                            }
                        } 
                    }
                    $entity->setCategory($laCategorie);
                    if($data[$i][15] !== null){
                        foreach($lesSubCategories as $subCategorie){
                            if($subCategorie["targetCategorie"] == $data[$i][15]){
                                $laCategorie = $this->entityManager->getRepository(SubCategory::class)->findOneById($subCategorie["id"]);
                                $entity->setSubCategory($laCategorie);
                                $subCategoryChecker = true;
                            }
                        }
                    }
                    if(!$subCategoryChecker && $data[$i][15] !== null){ //sinon création sous-catégorie
                        $newSubCategory = new SubCategory();
                        $newSubCategory->setName($data[$i][15]);
                        $this->entityManager->persist($newSubCategory);
                        $this->entityManager->flush();
                        if($newSubCategory){
                            $entity->setSubCategory($newSubCategory);
                        }
                    }

                    // if(!$laFamille && $data[$i][11] !== null){
                    //     $subCategorieTwo = new Famille();
                    //     $subCategorieTwo->setName($data[$i][11]);
                    //     $this->entityManager->persist($subCategorieTwo);
                    //     $this->entityManager->flush();
                    //     // echo $subCategorieTwo->getName() . " a été inséré\n";
                    //     if($subCategorieTwo){
                    //     $entity->setFamille($subCategorieTwo);
                    //     }
                    // } 
                    $entity->setIsAffiche(true); //est affiche sur le site
                    $entity->setIsBest(false);
                    $entity->setIsOccassion(false);
                    $entity->setIsForcesOrdre(false);
                    $entity->setCodeRga(null);
                    $entity->setCaracteristique("-");
                    $entity->setIsCarteCadeau(false);
                    $entity->setIsForcesOrdre(false);
                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();
                    echo ($entity->getName()) . " à été inséré.\n";
                    //  dd($data[$i]);
                }
            } 
            return new Response();
        }
     
        public function updateDCAFrance(){ //mise à jour quantité et prix DCA
            $reader = new ReaderCsv();
            $reader->setReadDataOnly(true); //en lecture seulement
            $reader->setInputEncoding('CP1252'); //pour réparer les erreurs du format UTF-8

            $spreadsheet = $reader->load('./suppliers/dca-france/revendeur-dca.csv'); //chargement du csv
            $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
            $data = $sheet->toArray(); //conversion excel en tableau
            for($i = 1; $i < count($data); $i++){
                $produitExists = $this->entityManager->getRepository(Produit::class)->findOneByReference($data[$i][1]);
                if($produitExists){
                    $produitExists->setPrice(intval(preg_replace('/[^\d.]/', '', number_format(intval($data[$i][5]) * 100)))); //prix
                    $produitExists->setPricePromo(intval(preg_replace('/[^\d.]/', '', number_format(intval(($data[$i][5]) * 100) * 0.97)))); //prix promo -3%
                    $produitExists->setQuantite($data[$i][10]);
                    $this->entityManager->flush();
                    echo ($produitExists->getName()) . " à été mis à jour.\n";
                }
            }
            return new Response();
        }
    }

?>
