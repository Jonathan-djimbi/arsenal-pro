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
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;

/**
 * @param float         $row[0] : reference (41.096222)
 * @param string        $row[1] : EAN (3760280962224)
 * @param string        $row[2] : Titre (Pochette grenade à fusil FLG-APAV oryx)
 * @param string        $row[3] : Marque (FLG)
 * @param string        $row[4] : Taille (3XL, 52)
 * @param string        $row[5] : Couleur (Camo vert)
 * @param float         $row[6] : Poids (0.00)
 * @param string        $row[7] : Description (Pochette grenade à fusil FLG-APAV oryx)
 * @param string        $row[8] : Univers (Univers Militaire)
 * @param string        $row[9] : Catégorie 1 (HABILLEMENT)
 * @param string        $row[10] : Catégorie 2 (Vêtements)
 * @param string        $row[11] : Catégorie 3 (Tee-shirts)
 * @param string        $row[12] : URL produit (https://www.a10-equipment-pro.com/sous-veste-thermo-performer-10-c-20-c-bleu-marine.html)
 * @param string        $row[13] : URL image (https://cdn2.a10-equipment.com/media/catalog/product/0/1/01.097229v1.jpg)
 * @param float         $row[14] : Prix (64.00)
 * @param float         $row[15] : Prix barre (72.00)
 * @param float         $row[16] : Prix revendeur (29.09)
 * @param float         $row[17] : Prix barre revendeur (32.86)
 * @param string        $row[18] : Monnaie (euro TTC)
 * @param string        $row[19] : Disponibilité (En stock, Hors stock)
 * @param string        $row[20] : Fin de vie (Oui, '')
 * @param string        $row[21] : Parent_enfant (parent, child)
 * @param string        $row[22] : Ancien Sku (201877)
 */
class A10Service extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /* TODO : supprimé */
    public function produitSupprimeA10($url, $login, $mdp, $notreDossier, $fichierFTP)
    {
        $reader = new ReaderCsv();
        $reader->setTestAutoDetect(false); // Éviter les erreurs de dépréciation
        $ftp_logError = './suppliers/log/logFTP.txt'; // Connexion FTP pour récupérer un fichier
        $ftp = ftp_connect($url, 21);
        if (!$ftp) {
            error_log("Erreur de connexion au FTP : " . $url . ". Veuillez tester si le domaine fonctionne.", 3, $ftp_logError);
            die("Erreur de connexion au FTP : " . $url . ". Veuillez tester si le domaine fonctionne.");
        }
        $login_result = ftp_login($ftp, $login, $mdp);
        if (!$login_result) {
            error_log("Erreur de Login au FTP : " . $url . ". Veuillez tester si le login et le mot de passe sont corrects.", 3, $ftp_logError);
            die("Erreur de Login au FTP : " . $url . ". Veuillez tester si le login et le mot de passe sont corrects.");
        }
        ftp_pasv($ftp, true);
        if (!ftp_get($ftp, $notreDossier, $fichierFTP, FTP_BINARY)) {
            $errorMessage = date('Y-m-d H:i:s') . " - Il y a eu un problème lors du chargement du fichier $notreDossier";
            file_put_contents($ftp_logError, $errorMessage . PHP_EOL, FILE_APPEND);
        }
        ftp_close($ftp);

        $reader->setReadDataOnly(true); // En lecture seulement
        $spreadsheet = $reader->load($notreDossier); // Chargement du CSV
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray(); // Conversion Excel en tableau
        foreach (array_slice($data, 1) as $row) {
            $reference = $row[0];
            if ($reference !== null && $reference !== '' && ($row[1] !== 1 || $row[1] !== "1")) {
                $references[] = $reference;
            }
        }
        $products = $this->entityManager->getRepository(Produit::class)->findBy(['reference' => $references]);
        // Utilisation d'une transaction pour les opérations de base de données
        $this->entityManager->beginTransaction();
        foreach (array_slice($data, 1) as $row) {
            $this->deleteProductRow($row, $products);
        }
        return new Response();
    }
    private function deleteProductRow($row, $products)
    {
        $reference = $row[0];
        $product = $this->findProductByReference($products, $reference);
        if ($product && ($row[1] == 0 || $row[1] == "0")) {
            error_log(date('Y-m-d H:i:s') . " - " . $product->getName() . " - " . $product->getId() . " a été désaffiché.\n", 3, './suppliers/log/logDelete.txt');
            $product->setIsAffiche(false);
        }
    }

    private function getBestApplicableRemises($products)
    {
        if (!is_array($products)) {
            $products = [$products];
        }
        $subCategoryIds = array_unique(array_map(function ($product) {
            return $product->getSubCategory();
        }, $products));
        $marqueIds = array_unique(array_map(function ($product) {
            return $product->getMarque();
        }, $products));
        $fournisseurIds = array_unique(array_map(function ($product) {
            return $product->getFournisseurs();
        }, $products));
        $parameters = [
            'subCategoryIds' => $subCategoryIds,
            'marqueIds' => $marqueIds,
            'fournisseurIds' => $fournisseurIds,
        ];
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('r')
            ->from('App\Entity\RemiseGroupe', 'r')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->in('r.subCategories', ':subCategoryIds'),
                $queryBuilder->expr()->in('r.marques', ':marqueIds'),
                $queryBuilder->expr()->in('r.fournisseur', ':fournisseurIds')
            ))
            ->andWhere('r.desactive = 0')
            ->orderBy('r.priority', 'DESC')
            ->setParameters($parameters);
        return $queryBuilder->getQuery()->getResult();
    }

    public function updateA10Produits()
    {
        $url = "http://phototheque.toe-concept.com/flux/export/revendeur.csv";
        file_put_contents('./suppliers/a10/revendeur-a10.csv',  file_get_contents($url)); //téléchargement du CSV
        $notreDossier = "./suppliers/a10/revendeur-a10.csv";

        $reader = new ReaderCsv();

        $references = [];
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($notreDossier);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();
        foreach (array_slice($data, 1) as $row) {
            $reference = $row[0];
            if ($reference !== null && $reference !== '') {
                $references[] = $reference;
            }
        }

        $products = $this->entityManager->getRepository(Produit::class)->findBy(['reference' => $references]);
        $remises = $this->getBestApplicableRemises($products);
        // Utilisation d'une transaction pour les opérations de base de données
        
        $this->entityManager->beginTransaction();
        
        foreach(array_slice($data, 1) as $row) {
            $reference = $row[0];
            $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[14])) * 100)));

            $product = $this->findProductByReference($products, $reference);
            if ($product && $row[14] != null && $row[14] != '') {
                if ($product->getFournisseurs() == null) {
                    $product->setIsAffiche(false);
                    $this->entityManager->persist($product);
                    // $this->entityManager->flush();
                    error_log($product->getName() . " - " . $product->getId() . " a été désaffiché.\n", 3, './suppliers/log/logDelete.txt');
                } else {
                    if ($product->getFournisseurs()->getId() === 14 && intval($row[14]) > 1) { //si le prix n'est pas à zero et que c'est simac ou europarm
                        $remises = $this->getBestApplicableRemises($product);
                        if ($row[14] <= $row[16] && $row[14] != null && $row[16] != null) { //si prix <= prix revendeur alors on passe
                            $prirev = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[16])) * 100)));
                            $price = $prirev * 1.5; //prix revendeur + 50%
                            $bestRemise = $this->findBestRemiseForProduct($product, $remises);
                        } elseif ($row[14] > $row[16] && $row[14] != null && $row[16] != null && $row[14] < $row[15]) {
                            $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[15])) * 100)));

                            if ($row[15] > $row[14] && intval($row[15] > 1) && intval($row[14] > 1)) {
                                $pricepromo = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[14])) * 100)));
                                $product->setPricePromo($pricepromo);
                            } else {
                                //trouver s'il y a déjà un prix promo appliqué pour notre selection du produit
                                $bestRemise = $this->findBestRemiseForProduct($product, $remises);
                            }
                        } elseif ($row[14] > $row[16] && $row[14] != null && $row[16] != null && $row[14] > $row[15]) {
                            $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[14])) * 100)));
                            $bestRemise = $this->findBestRemiseForProduct($product, $remises);
                        } else {
                            $product->setIsAffiche(false);
                            continue;
                        }
                        $product->setPrice($price);

                        if ($bestRemise && $pricepromo == null) {
                            $product->setPricePromo(intval($price * (1 - ($bestRemise->getRemise() / 100))));
                        } else {
                            $product->setPricePromo(intval($price * 0.97)); // 3% de réduction par défaut
                        }

                        //si quantité = "En stock" alors quantité = 1 sinon 0
                        if ($row[19] == "En stock") {
                            $product->setQuantite(1);
                        } else {
                            $product->setQuantite(0);
                        }
                        $this->entityManager->persist($product); //maj BDD
                        $this->entityManager->flush();
                        $logMessage = date('Y-m-d H:i:s') . " - " . $row[1] . " - " . $row[2] . "\n";
                        file_put_contents('./suppliers/log/log.txt', $logMessage, FILE_APPEND);
                    } else {
                        $logMessageError = $row[0] . " - " . $row[1] . " - " . $row[2];
                        $logMessageErrorWithTimestamp = date('Y-m-d H:i:s') . ' - ' . $logMessageError;
                        error_log($logMessageErrorWithTimestamp . "\n", 3, './suppliers/log/logError.txt');
                    }
                }
            }
        }
        $this->entityManager->flush(); // Insérer les produits restants
        $this->entityManager->commit();
        $this->entityManager->clear(); // Detacher tous les objets
        // Valider la transaction
        
        return new Response();
    }
    private function findProductByReference($products, $reference)
    {
        foreach ($products as $product) {
            if ($product->getReference() === $reference ) {
                return $product;
            }
        }
        return null;
    }
    private function findBestRemiseForProduct($product, $remises)
    {
        foreach ($remises as $remise) {
            return $remise;
        }
        return null;
    }
    public function enleverCaracSpecial($str)
    {
        return str_replace('-', ' ', preg_replace('/[^A-Za-z0-9\s]/', '', $str)); //si ça ne match pas le regex alors caractère vide
    }
    // insertion des nouveaux produits
    public function insertA10Produits()
    {
        $supplierName = "A10 Equipements";
        $url = "http://phototheque.toe-concept.com/flux/export/revendeur.csv";
        file_put_contents('./suppliers/a10/revendeur-a10.csv',  file_get_contents($url)); //téléchargement du CSV
        $notreDossier = "./suppliers/a10/revendeur-a10.csv";
        $reader = new ReaderCsv();
        $reader->setReadDataOnly(true); //en lecture seulement

        $spreadsheet = $reader->load($notreDossier); //chargement du xls
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray(); //conversion excel en tableau
        $batchSize = 20; // Nombre d'enregistrements à insérer à la fois
        $count = 0;
        $this->entityManager->beginTransaction();
        try {
            foreach (array_slice($data, 1) as $row) {
                $leProduit = $this->entityManager->getRepository(Produit::class)->findOneByReference($row[0]); //check si ref existe
                $produit_nom = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($row[2])));
                if(!$leProduit && $row[14] != null && $row[13] != null && $row[3] != null && $row[9] != "HABILLEMENT" && $row[10] != "Vêtements" && $row[10] != "Cuisson" && $row[10] != "Verticalité" && $row[20] != "Oui" && $row[19] != "Hors stock"){
                    //si le produit n'existe pas par rapport à la référence et que le prix trouvé est supérieur à 1, si image pas null et si nom pas null
                    
                    $leProduitSlugCheck = $this->entityManager->getRepository(Produit::class)->findOneBySlug($produit_nom); //check si slug existe pour eviter doublons
                    $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName($row[3]);
                    $subCategorie = $this->entityManager->getRepository(SubCategory::class)->findOneByName($row[10]);
                    $subCategorieTwo = $this->entityManager->getRepository(Famille::class)->findOneByName($row[11]);
                    $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName($supplierName); //OBLIGATOIREMENT DANS LA BDD

                    if ($row[14] !== null && $leProduit == null && $leProduitSlugCheck == null) {
                        $entity = new Produit();
                        
                        $entity->setName($row[2]);
                        if ($laMarque) {
                            $entity->setMarque($laMarque);
                        } else {
                            if ($row[3] !== null || !empty($row[3])) { //si nom de la marque pas vide
                                $marque = new Marque(); //sinon création de la marque
                                $marque->setName($row[3]);
                                $this->entityManager->persist($marque);
                                $this->entityManager->flush(); //maj BDD marque en avance car marque est une relation NOT NULL, si pas de maj maintenant, alors bug durant l'exécution du code
                            } else {
                                $marque = $this->entityManager->getRepository(Marque::class)->findOneById(261); //si pas de nom au marque du produit alors on met une marque générique
                            }
                            $entity->setMarque($marque);
                        }
                        $entity->setCalibres(null);//calibre
                        $entity->setSubtitle($row[2]); //sous-titre = titre
                        $entity->setReference($row[0]);
                        $entity->setSlug($produit_nom); //lien unique pour pas que ça fasse des conflits
                       
                        $entity->setIllustration($row[13]); //obligatoire

                        if ($row[7] !== null) { 
                            $entity->setDescription($row[7]);
                        } else {
                            $entity->setDescription("Descriptif complet à venir pour " . $entity->getName());
                        }

                        $entity->setIsAffiche(true); //est affiche sur le site
                        $entity->setIsBest(false);
                        $entity->setIsOccassion(false);
                        $entity->setIsForcesOrdre(false);
                        $entity->setMasse($row[6]);

                        //sous-categorie
                        if (!$subCategorie && $row[10] !== null) {
                            $subCategorieNew = new SubCategory();
                            $subCategorieNew->setName($row[10]);
                            $this->entityManager->persist($subCategorieNew);
                            $this->entityManager->flush();
                        }
                        if ($subCategorie && $row[10] !== null) {
                            $entity->setSubCategory($subCategorie);
                        }

                        //categorie de vente = vente libre (pas d'armes)
                        $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById(8);
                        $entity->setCategory($laCategorie);
                        //sous-categorie 2
                        if (!$subCategorieTwo && $row[11] !==null) {
                            $subCategorieTwo = new Famille();
                            $subCategorieTwo->setName($row[11]);
                            $this->entityManager->persist($subCategorieTwo);
                            $this->entityManager->flush();
                        }
                        if ($subCategorieTwo && $row[11] !== null) {
                            $entity->setFamille($subCategorieTwo);
                        }

                        //si quantité = "En stock" alors quantité = 1 sinon 0
                        if ($row[19] == "En stock") {
                            $entity->setQuantite(1);
                        } else {
                            $entity->setQuantite(0);
                        }
                        $entity->setCaracteristique("Couleur : " . $row[5] . "\nUnivers : " . $row[8] . "\n" . (($row[4] != null) ? "Taille : " . $row[4] . "\n" : "")?? "-");
                        $entity->setFournisseurs($fournisseur);
                        $remises = $this->getBestApplicableRemises($entity);
                        if ($row[14] <= $row[16] && $row[14] != null && $row[16] != null) { //si prix <= prix revendeur alors on passe
                            $prirev = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[16])) * 100)));
                            $price = $prirev * 1.15; //prix revendeur + 15%
                            $bestRemise = $this->findBestRemiseForProduct($entity, $remises);
                        } elseif ($row[14] > $row[16] && $row[14] != null && $row[16] != null && $row[14] < $row[15]) {
                            $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[15])) * 100)));
                            
                            if ($row[15] > $row[14] && intval($row[15] > 1) && intval($row[14] > 1)) {
                                $pricepromo = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[14])) * 100)));
                                $entity->setPricePromo($pricepromo);
                            } else {
                                //trouver s'il y a déjà un prix promo appliqué pour notre selection du produit
                                $bestRemise = $this->findBestRemiseForProduct($entity, $remises);
                            }
                        } elseif ($row[14] > $row[16] && $row[14] != null && $row[16] != null && $row[14] > $row[15]) {
                            $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[14])) * 100)));
                            $bestRemise = $this->findBestRemiseForProduct($entity, $remises);
                        } else {
                            continue;
                        }
                        $entity->setPrice($price);

                        if ($bestRemise && $pricepromo == null) {
                            $entity->setPricePromo(intval($price * (1 - ($bestRemise->getRemise() / 100))));
                        } else {
                            $entity->setPricePromo(intval($price * 0.97)); // 3% de réduction par défaut
                        }
                        $this->entityManager->persist($entity);
                        $this->entityManager->flush();
                    }
                }
                $count++;
                if ($count % $batchSize == 0) {
                    $this->entityManager->flush();
                    $this->entityManager->commit();
                    $this->entityManager->clear(); // Libère les objets pour éviter la surcharge mémoire
                    $this->entityManager->beginTransaction();
                }
            }
            $this->entityManager->flush(); // Insérer les produits restants
            $this->entityManager->commit(); // Valider la transaction
            $this->entityManager->clear(); // Detacher tous les objets
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
        return new Response();
    } 
}