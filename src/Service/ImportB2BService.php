<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Calibre;
use App\Entity\Category;
use App\Entity\Famille;
use App\Entity\Fournisseurs;
use App\Entity\MailRetourStock;
use App\Entity\Marque;
use App\Entity\OrderDetails;
use App\Entity\Produit;
use App\Entity\SubCategory;
use App\Entity\VenteFlash;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class ImportB2BService extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function enleverCaracSpecial($str)
    {
        return str_replace('-', ' ', preg_replace('/[^A-Za-z0-9\s]/', '', $str)); //si ça ne match pas le regex alors caractère vide
    }
    public function importDataFromB2BApi_CS($apiUrl, $apiKey_CS, $fournisseur)
    {
        $batchSizing = 50;
        $batchCount = 0;
        /**
         * @var $apiUrl : url de l'api de base (sans les paramètres : article, stock)
         * @var $apiKey_CS : clé de l'api
         */
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $apiUrl, [
            'headers' => [
                'X-Auth-Token' => $apiKey_CS,
            ],
        ]);
        $data = $response->toArray();

        $logDone = './suppliers/log/B2B/logDone.log';
        $logError = './suppliers/log/B2B/logError.log';
        $logImage = './suppliers/log/B2B/logImage.log';
        $logDelte = './suppliers/log/B2B/logDelete.log';
        $importBy = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName($fournisseur); //OBLIGATOIREMENT DANS LA BDD
        if (!$importBy) {
            die("Fournisseur non trouvé dans la base de données.");
        }

        $excludedFamilies = ["ARTICLES POUR CHIEN", "ARCHERIE"];
        $excludedCategories = ["VESTES", "CASQUETTES CHAPEAUX", "BONNETS CAGOULES", "PANTALONS", "SACS", "BRETELLES", "GANTS", "POLO", "ACCESSOIRES POUR CHIEN", "POCHES", "HOUSSE", "GILETS", "COLLIERS", "LAISSES", "TAPIS", "ANTENNES", "PULL SWEAT", "MUNITIONS PLASTIQUES", "CHEMISES", "TEE SHIRTS", "ORGANISATION", "CIBLERIES", "CANNES", "FOURREAUX", "CEINTURES"];

        /**
         * @var             $product : produit de la BDD
         * @var             $articleData : données de l'api
         * @param int       $articleData['id'] : id de l'article
         * @param string    $articleData['code'] : référence de l'article
         * @param float     $articleData['pvpc'] : prix de l'article TTC conseillé
         * @param float     $articleData['coef'] : coefficient de l'article
         * @param string    $articleData['libelle'] : nom de l'article
         * @param string    $articleData['marque'] : marque de l'article
         * @param string    $articleData['departement'] : département de l'article
         * @param string    $articleData['famille'] : famille de l'article
         * @param string    $articleData['categorie'] : catégorie de l'article
         * @param string    $articleData['groupement'] : liaison d'un article à un article parent (ex : 090693 lié à 090939)
         * @param int       $articleData['reglementation'] : niveau de réglementation de l'article (0,1,2,3)
         * @param string    $articleData['agrementCategorie'] : catégorie d'agrément de l'article
         * @param bool      $articleData['rga'] : booléen pour savoir si l'article est en RGA
         * @param string    $articleData['rgaNumero'] : code RGA de l'article
         * @param string    $articleData['mdcode'] : inutilisé
         * @param string    $articleData['description'] : description de l'article
         * @param array     $articleData['images'] : tableau des images de l'article (url)
         */
        
        try{
            $existingProducts = $this->entityManager->getRepository(Produit::class)->findBy(['fournisseurs' => $importBy]);
            $existingSupplier = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName($fournisseur);

            if (!$existingSupplier) {
                die("Fournisseur non trouvé dans la base de données.");
            }
                $this->entityManager->beginTransaction();
                foreach ($data['articles'] as $articleData) {
                    // Check si le produit existe déjà dans la BDD.
                    $product = $this->entityManager->getRepository(Produit::class)->findOneBy(['reference' => $articleData['code']]);

                    // Si le produit existe déjà mais n'est pas dans l'api alors on affiche false
                    $existingProductCodes = array_map(fn($existingProduct) => $existingProduct->getReference(), $existingProducts);

                    if ($product && !in_array($articleData['code'], $existingProductCodes)) {
                        //si fournisseur est null alors on met ce fournisseur
                        if($product->getFournisseurs() == null){
                            $product->setFournisseurs($existingSupplier);
                            $this->entityManager->persist($product);
                            $this->entityManager->flush();
                        }else{
                            $product->setIsAffiche(false);
                            $this->entityManager->persist($product);
                            $this->entityManager->flush();
                            echo "Suppression du produit visuelle: " . $product->getReference() . "\n";
                            error_log(date('Y-m-d H:i:s') . ' Suppression visuelle du produit : ' . $product->getReference() . "\n", 3, $logDelte);
                            continue;
                        }
                    }
                    // Nouveau produit
                    if(!$product){
                        if (!in_array($articleData['famille'], $excludedFamilies) && !in_array($articleData['categorie'], $excludedCategories)) {
                            // Création d'un nouveau produit
                            echo "insertion de l'article : " . $articleData['code'] . " - " . $articleData['libelle'] . "\n";
                            $product = new Produit();
                            $product->setFournisseurs($existingSupplier);
                            $product->setName($articleData['libelle']);
                            $product->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($articleData['libelle']))));
                            $product->setReference($articleData['code']);
                            $product->setPrice($articleData['pvpc'] * 100);
                            $product->setSubtitle($articleData['libelle']);
                            $description = $articleData['description'];
                            if($articleData['description'] != null){
                                $descriptionSansLiens = preg_replace('/<a\b[^>]*>(.*?)<\/a>/', '', $description);
                                $product->setDescription($descriptionSansLiens);
                                //si lien dans la description on ne met pas le lien
                                if (preg_match('/Poids\s:\s*(\d+)/', $descriptionSansLiens, $matches) or preg_match('/Poids\s:\s*(\d+)/', $descriptionSansLiens, $matches) or preg_match('/Poids\s*\(gr\):\s*(\d+)/', $descriptionSansLiens, $matches) or preg_match('/Poids\s\(sans chargeur\)\s:\s*(\d+)/', $descriptionSansLiens, $matches)) {
                                    //si matches[1] est un nombre à virgule ',' alors je multiplie par 1000 pour obtenir le poids en grammes
                                    if (strpos($matches[1], ',') !== false) {
                                        $poids = (float) str_replace(',', '.', $matches[1]) * 1000;
                                    } else {
                                        $poids = $matches[1];
                                    }
                                    $product->setMasse((int)$poids);
                                } else {
                                    $product->setMasse(0);
                                }

                            }else{
                                $product->setDescription("Descriptif complet à venir pour " . $articleData['libelle']);
                            }
                            
                            //associer image a produit (link url)
                            $images = $articleData['images'];
                            if ($images != null) {
                                $imageBDD = ['setIllustration', 'setIllustrationUn', 'setIllustrationDeux', 'setIllustrationTrois', 'setIllustrationQuatre'];

                                foreach ($images as $index => $image) {
                                    if (isset($imageBDD[$index])) {
                                        $product->{$imageBDD[$index]}($image);
                                    } else {
                                        error_log(date('Y-m-d H:i:s') . 'BREAK Erreur image : ' . $articleData['code'] . ' - ' . $articleData['libelle'] . "\n", 3, $logImage);
                                        break; //si pas d'image
                                    }
                                }
                            } else {
                                error_log(date('Y-m-d H:i:s') . 'BREAK Erreur image : ' . $articleData['code'] . ' - ' . $articleData['libelle'] . "\n", 3, $logImage);
                                continue;
                            }


                            //check si marque existe
                            $marque = $this->entityManager->getRepository(Marque::class)->findOneByName($articleData['marque']);
                            if (!$marque) {
                                $marque = new Marque();
                                $marque->setName($articleData['marque']);
                                $this->entityManager->persist($marque);
                                $this->entityManager->flush();
                            }
                            //association catégorie -> id
                            $agrementCategorie = $articleData['agrementCategorie'];
                            if($agrementCategorie == 'D'){
                                $category = $this->entityManager->getRepository(Category::class)->findOneById(3);
                            }elseif ($agrementCategorie == 'C' or $agrementCategorie == 'C-1-B') {
                                $category = $this->entityManager->getRepository(Category::class)->findOneById(1);
                            } elseif ($agrementCategorie == 'B') {
                                $category = $this->entityManager->getRepository(Category::class)->findOneById(2);
                            } elseif ($agrementCategorie == 'A') {
                                $category = $this->entityManager->getRepository(Category::class)->findOneById(9);
                            } elseif ($agrementCategorie == null) {
                                if($articleData['reglementation'] == 0){
                                    $category = $this->entityManager->getRepository(Category::class)->findOneById(8);
                                }elseif ($articleData['reglementation'] == 1) {
                                    error_log(date('Y-m-d H:i:s') . ' Erreur catégorie : ' . $articleData['code'] . ' - ' . $articleData['libelle'] . ' - ' . $articleData['agrementCategorie'] . ' - ' . $articleData['categorie'] . "\n", 3, $logError);
                                    continue;
                                }
                            } else {
                                error_log(date('Y-m-d H:i:s') . ' Erreur catégorie : ' . $articleData['code'] . ' - ' . $articleData['libelle'] . ' - ' . $articleData['agrementCategorie'] . ' - ' . $articleData['categorie'] . "\n", 3, $logError);
                                continue;
                            }
                            //check si sous-catégorie existe
                            $subCategory = $this->entityManager->getRepository(SubCategory::class)->findOneByName($articleData['categorie']);
                            if (!$subCategory) {
                                $subCategory = new SubCategory();
                                $subCategory->setName($articleData['categorie']);
                                $this->entityManager->persist($subCategory);
                                $this->entityManager->flush();
                            }
                            //check si famille existe
                            $famille = $this->entityManager->getRepository(Famille::class)->findOneByName($articleData['famille']);
                            if (!$famille) {
                                $famille = new Famille();
                                $famille->setName($articleData['famille']);
                                $this->entityManager->persist($famille);
                                $this->entityManager->flush();
                            }

                            /* TODO */
                            $rga = $articleData['rga'];
                            if($rga == true){
                                if($articleData['rgaNumero'] != null){
                                    $product->setCodeRga($articleData['rgaNumero']);
                                }else{
                                    error_log(date('Y-m-d H:i:s') . ' Erreur RGA : ' . $articleData['code'] . ' - ' . $articleData['libelle'] . ' - ' . $articleData['rgaNumero'] . ' - ' . $articleData['rga'] . "\n", 3, $logError);
                                    $product->setCodeRga(null);
                                }
                            }else{
                                $product->setCodeRga(null);
                            }
                            
                            // Set relations to related entities.
                            $product->setMarque($marque);
                            $product->setCategory($category);
                            $product->setFamille($famille);
                            $product->setSubCategory($subCategory);
                            $product->setCaracteristique("-");
                            $product->setReferenceAssociation(null);
                            
                            //params
                            $product->setIsAffiche(false);
                            $product->setIsBest(false);
                            $product->setIsOccassion(false);
                            $product->setIsForcesOrdre(false);
                            $this->entityManager->persist($product);

                            error_log(date('Y-m-d H:i:s') . 'Insertion : ' . $articleData['code'] . ' - ' . $articleData['libelle'], 3, $logDone);
                            continue;
                        }
                    }
                    // Si le produit existe déjà, upload
                    if ($product) {
                        // Le produit existe déjà, vérifier les différences
                        if ($product->getName() != $articleData['libelle']) {
                            $product->setName($articleData['libelle']);
                        }
                        $description = $articleData['description'];
                        if ($articleData['description'] != null) {
                            $descriptionSansLiens = preg_replace('/<a\b[^>]*>(.*?)<\/a>/', '', $description);
                            if ($product->getDescription() != $descriptionSansLiens) {
                                $product->setDescription($descriptionSansLiens);
                                //si lien dans la description on ne met pas le lien
                                if (preg_match('/Poids\s:\s*(\d+)/', $descriptionSansLiens, $matches) or preg_match('/Poids\s:\s*(\d+)/', $descriptionSansLiens, $matches) or preg_match('/Poids\s*\(gr\):\s*(\d+)/', $descriptionSansLiens, $matches) or preg_match('/Poids\s\(sans chargeur\)\s:\s*(\d+)/', $descriptionSansLiens, $matches)
                                ) {
                                    //si matches[1] est un nombre à virgule ',' alors je multiplie par 1000 pour obtenir le poids en grammes
                                    if (strpos($matches[1], ',') !== false) {
                                        $poids = (float) str_replace(',', '.', $matches[1]) * 1000;
                                    } else {
                                        $poids = $matches[1];
                                    }
                                    $product->setMasse((int)$poids);
                                } else {
                                    $product->setMasse(0);
                                }
                            }
                        } elseif ($articleData['description'] == null) {
                            $product->setDescription("Descriptif complet à venir pour " . $articleData['libelle']);
                        }
                        
                        if ($product->getPrice() != $articleData['pvpc'] * 100  && $articleData['pvpc'] != null) {
                            $product->setPrice($articleData['pvpc'] * 100);
                        } elseif ($articleData['pvpc'] == null) {
                            $product->setIsAffiche(false);
                        }
                        if ($product->getIllustration() != $articleData['images'][0]  && $articleData['images'][0] != null) {
                            // Associez les nouvelles images au produit
                            $images = $articleData['images'];
                            if ($images != null) {
                                $imageBDD = ['setIllustration', 'setIllustrationUn', 'setIllustrationDeux', 'setIllustrationTrois', 'setIllustrationQuatre'];
                                foreach ($images as $index => $image) {
                                    if (isset($imageBDD[$index])) {
                                        $product->{$imageBDD[$index]}($image);
                                    }
                                }
                            }
                        } elseif ($articleData['images'][0] == null
                        ) {
                            $product->setIsAffiche(false);
                        }
                        $this->entityManager->persist($product);
                        echo "Mise à jour du produit : " . $articleData['code'] . " - " . $articleData['libelle'] . "\n";
                        continue;
                    } 
                    /* if (($batchCount  % $batchSizing) === 0) {
                        $this->entityManager->flush();
                        $this->entityManager->clear(); // Détacher toutes les entités pour éviter une consommation excessive de mémoire
                        echo "Flush et clear \n";
                        // echo "Clear \n";
                    } */
                    $batchCount++;
                }
                $this->entityManager->flush(); // Dernier flush
                $this->entityManager->commit();
                $this->entityManager->clear();
                echo "Flush et clear FIN \n";
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            echo "Erreur lors de l'insertion des produits " . $e->getMessage() . "\n";
            throw $e;
        }
        return count($data['articles']) . ' products inserted or updated from the API.';
    }
}