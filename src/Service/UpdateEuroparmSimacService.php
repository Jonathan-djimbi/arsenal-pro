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

class UpdateEuroparmSimacService extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /* TODO : supprimé */
    public function produitSupprimeEuropArm($url, $login, $mdp, $notreDossier, $fichierFTP)
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

    public function updateEuropArmProduits($url, $img, $login, $mdp, $notreDossier, $fichierFTP)
    {
        $reader = new ReaderCsv();

        // Connexion FTP
        $ftpLogFile = './suppliers/log/logFTP.txt';
        $ftp = ftp_connect($url, 21);
        if (!$ftp) {
            $errorMessage = date('Y-m-d H:i:s') . " - Erreur de connexion au FTP : " . $url . ". Veuillez tester si le domaine fonctionne.";
            file_put_contents($ftpLogFile, $errorMessage . PHP_EOL, FILE_APPEND);
            die($errorMessage);
        }
        file_put_contents($ftpLogFile, FILE_APPEND);

        $login_result = ftp_login($ftp, $login, $mdp);
        if (!$login_result) {
            $errorMessage = date('Y-m-d H:i:s') . " - Erreur de Login au FTP : " . $url . ". Veuillez tester si le login et le mot de passe sont corrects.";
            file_put_contents($ftpLogFile, $errorMessage . PHP_EOL, FILE_APPEND);
            die($errorMessage);
        }
        file_put_contents($ftpLogFile, FILE_APPEND);

        ftp_pasv($ftp, true);
        if (ftp_get($ftp, $notreDossier, $fichierFTP, FTP_BINARY)) {
            $message = date('Y-m-d H:i:s') . " - Le fichier $notreDossier a été écrit avec succès";
            file_put_contents($ftpLogFile, $message . PHP_EOL, FILE_APPEND);
        } else {
            $errorMessage = date('Y-m-d H:i:s') . " - Il y a eu un problème lors du chargement du fichier $notreDossier";
            file_put_contents($ftpLogFile, $errorMessage . PHP_EOL, FILE_APPEND);
        }
        ftp_close($ftp);

        $references = [];
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($notreDossier);
        $sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
        $data = $sheet->toArray();
        foreach (array_slice($data, 1) as $row) {
            $reference = $row[2];
            if ($reference !== null && $reference !== '') {
                $references[] = $reference;
            }
        }

        $products = $this->entityManager->getRepository(Produit::class)->findBy(['reference' => $references]);
        $remises = $this->getBestApplicableRemises($products);
        // Utilisation d'une transaction pour les opérations de base de données
        $this->entityManager->beginTransaction();
        foreach(array_slice($data, 1) as $row) {
            $reference = $row[2];
            $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[8])) * 100)));

            $product = $this->findProductByReference($products, $reference);
            if ($product && $row[8] != null && $row[8] != '') {
                if ($product->getFournisseurs() == null) {
                    $product->setIsAffiche(false);
                    $this->entityManager->persist($product);
                    // $this->entityManager->flush();
                    error_log($product->getName() . " - " . $product->getId() . " a été désaffiché.\n", 3, './suppliers/log/logDelete.txt');
                } else {
                    if (($product->getFournisseurs()->getId() === 1 or $product->getFournisseurs()->getId() === 11) && intval($row[8]) > 1) { //si le prix n'est pas à zero et que c'est simac ou europarm
                        // $logDate = date('Y-m-d H:i:s') . " - ";
                        // $logUpdate = "Produit : " . $product->getName() . " - " . $row[1] . " - " . $row[2];
                        if ($product->getPrice() != $price) {
                            $product->setPrice($price);
                            // $logUpdate .= " - Prix mis à jour : " . $price;
                        }
                        if($row[9] != $row[8] && intval($row[9] > 1)){
                            $pricepromo = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[9])) * 100)));
                            $product->setPricePromo($pricepromo);
                        }else{
                            $bestRemise = $this->findBestRemiseForProduct($product, $remises);
                        }
                        if ($bestRemise) {
                            $product->setPricePromo(intval($price * (1 - ($bestRemise->getRemise() / 100))));
                            // $logUpdate .= " - Prix promo mis à jour : " . intval($price * (1 - ($bestRemise->getRemise() / 100)));
                        } else {
                            $product->setPricePromo(intval($price * 0.97)); // 3% de réduction par défaut
                            // $logUpdate .= " - Prix promo mis à jour : " . intval($price * 0.97);
                        }
                        if ($product->getQuantite() != $row[6]) {
                            $product->setQuantite($row[6] ?? 0);
                            // $logUpdate .= " - Quantité mise à jour";
                        }
                        if ($product->getName() != $row[1]) {
                            $product->setName($row[1]);
                            // $logUpdate .= " - Nom mis à jour";
                        }
                        if ($product->getSubtitle() != $row[12]) {
                            $product->setSubtitle($row[12] ?? $row[1]);
                            // $logUpdate .= " - Sous-titre mis à jour";
                        }
                        if ($product->getDescription() != $row[15] && $row[15] != null && $row[15] != "") {
                            $product->setDescription($row[15] ?? "Descriptif complet à venir pour " . $product->getName());
                            // $logUpdate .= " - Description mise à jour";
                        }
                        if ($product->getMasse() != $row[5]) {
                            $product->setMasse($row[5] ?? 0);
                            // $logUpdate .= " - Masse mise à jour";
                        }
                        if ($product->getCodeRga() != $row[28]) {
                            $product->setCodeRga($row[28] ?? 0);
                            // $logUpdate .= " - Code RGA mis à jour : " . $row[28];
                        }
                        if ($product->getCaracteristique() != $row[14] && $row[14] != null) {
                            $product->setCaracteristique($row[14] ?? "-");
                            // $logUpdate .= " - Caractéristiques mise à jour";
                        }
                        $slug = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($row[1])));
                        if ($product->getSlug() != $slug) {
                            $product->setSlug($slug);
                            // $logUpdate .= " - Slug mis à jour : " . $slug;
                        }

                        $marque = $this->entityManager->getRepository(Marque::class)->findOneByName($row[3]);
                        if ($product->getMarque() != $marque && $row[3] != null) {
                            if ($marque) {
                                $product->setMarque($marque);
                            } else {
                                if ($row[3] != null || !empty($row[3])) { //si nom de la marque pas vide
                                    $marque = new Marque(); //sinon création de la marque
                                    $marque->setName($row[3]);
                                    $this->entityManager->persist($marque);
                                    // $this->entityManager->flush(); //maj BDD marque en avance car marque est une relation NOT NULL, si pas de maj maintenant, alors bug durant l'exécution du code
                                } else {
                                    $marque = $this->entityManager->getRepository(Marque::class)->findOneById(261); //si pas de nom au marque du produit alors on met une marque générique
                                }
                                $product->setMarque($marque);
                            }
                            // $logUpdate .= " - Marque mise à jour";
                        }
                        //categorie
                        $lesCategories = [ //valeurs qui correspondent à la BDD
                            ['categorie' => 'D', 'id' => 3],
                            ['categorie' => 'C', 'id' => 1],
                            ['categorie' => 'B', 'id' => 2],
                            ['categorie' => 'A', 'id' => 9],
                            ['categorie' => 'Vente libre', 'id' => 8],
                        ];
                        if ($product->getCategory() != $row[16]) {
                            foreach ($lesCategories as $uneCategorie) {
                                if ($row[16] == $uneCategorie['categorie']) { //verifie si categorie correspond
                                    $categorieId = $uneCategorie['id'];
                                    $familleId = null;
                                    if ($uneCategorie['categorie'] == "C" && $row[11] == "Munitions & rechargement") {
                                        $categorieId = 12; //alors catégorie = Munition CAT. C
                                    }
                                    if ($uneCategorie['categorie'] == "B" && $row[11] == "Munitions & rechargement") {
                                        $categorieId = 11; //alors catégorie = Munition CAT. B
                                    }
                                    if ($uneCategorie['categorie'] == "B" && $row[11] == "Armes règlementées & maintien de l'ordre") { //si arme de poing
                                        $familleId = 1;
                                    }
                                    $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById($categorieId);
                                    $laFamille = $this->entityManager->getRepository(Famille::class)->findOneById($familleId);
                                    $product->setCategory($laCategorie);
                                    $product->setFamille($laFamille);
                                    // $this->entityManager->flush();
                                } //sinon categorie en null
                            }
                            // $logUpdate .= " - Catégorie mise à jour";
                        }
                        //sous-categorie != row[12]
                        $subCategorie = $this->entityManager->getRepository(SubCategory::class)->findOneByName($row[12]);
                        if (!$subCategorie && $row[12] != null) {
                            $subCategorie = new SubCategory();
                            $subCategorie->setName($row[12]);
                            $this->entityManager->persist($subCategorie);
                            // $this->entityManager->flush();
                            // $logUpdate .= " - Sous-catégorie crée et mise à jour";
                        }
                        if ($subCategorie && $row[12] && $product->getSubCategory() != $subCategorie) {
                            $product->setSubCategory($subCategorie);
                            // $logUpdate .= " - Sous-catégorie mise à jour";
                        }
                        //sous-categorie 2
                        $subCategorieTwo = $this->entityManager->getRepository(Famille::class)->findOneByName($row[13]);

                        if (!$subCategorieTwo && $row[13] != null && !$subCategorieTwo->getId() == 1) {
                            $subCategorieTwo = new Famille();
                            $subCategorieTwo->setName($row[13]);
                            $this->entityManager->persist($subCategorieTwo);
                            // $this->entityManager->flush();
                            // $logUpdate .= " - Sous-catégorie 2 crée et mise à jour";
                        }
                        if ($subCategorieTwo && ($row[13] != null || $row[13] != "") && $product->getFamille() != $subCategorieTwo && !$subCategorieTwo->getId() == 1) {
                            $product->setFamille($subCategorieTwo);
                            // $logUpdate .= " - Sous-catégorie 2 mise à jour";
                        }
                        //calibre
                        $leCalibre = $this->entityManager->getRepository(Calibre::class)->findOneByCalibre($row[33]);
                        if ($product->getCalibres() != $leCalibre) {
                            if ($leCalibre) {
                                if ($row[33] == "9 x 19") { //SOUS EXCEPTION 9x19
                                    $leCalibre = $this->entityManager->getRepository(Calibre::class)->findOneByCalibre("9x19");
                                }
                                $product->setCalibres($leCalibre);
                            } else {
                                if ($row[33] != null) {
                                    $calibre = new Calibre(); //sinon création du calibre
                                    $calibre->setCalibre($row[33]);
                                    $this->entityManager->persist($calibre);
                                    // $this->entityManager->flush();
                                    $product->setCalibres($calibre);
                                } else {
                                    $product->setCalibres(null);
                                }
                            }
                            // $logUpdate .= " - Calibre mis à jour";
                        }
                        //images et image différente
                        $nomImage = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($row[1])));
                        if ($row[18] != null) {
                            $urlImage = $img . $row[18];
                            if ($product->getIllustration() != $nomImage . '-' . $row[18]) {
                                file_get_contents($urlImage);
                                file_put_contents('./public/uploads/' . $nomImage . '-' . $row[18],  file_get_contents($urlImage));
                                $product->setIllustration($nomImage . '-' . $row[18]);
                                // $logUpdate .= " - Image mise à jour";
                            }
                        }
                        if ($row[19] != null) {
                            $urlImageTwo = $img . $row[19];
                            if ($product->getIllustrationUn() != $nomImage . '-' . $row[19]) {
                                file_get_contents($urlImageTwo);
                                file_put_contents('./public/uploads/' . $nomImage . '-' . $row[19],  file_get_contents($urlImageTwo));
                                $product->setIllustrationUn($nomImage . '-' . $row[19]);
                                // $logUpdate .= " - Image 2 mise à jour";
                            }
                        }
                        if ($row[20] != null) {
                            $urlImageThree = $img . $row[20];
                            if ($product->getIllustrationDeux() != $nomImage . '-' . $row[20]) {
                                file_get_contents($urlImageThree);
                                file_put_contents('./public/uploads/' . $nomImage . '-' . $row[20],  file_get_contents($urlImageThree));
                                $product->setIllustrationDeux($nomImage . '-' . $row[20]);
                                // $logUpdate .= " - Image 3 mise à jour";
                            }
                        }
                        $this->entityManager->persist($product); //maj BDD
                        $this->entityManager->flush();
                        $logMessage = date('Y-m-d H:i:s') . " - " . $row[1] . " - " . $row[2] . "\n";
                        file_put_contents('./suppliers/log/log.txt', $logMessage, FILE_APPEND);
                        /* if($logUpdate != "Produit : " . $product->getName() . " - " . $row[1] . " - " . $row[2]){
                        $logUpdate = $logDate . $logUpdate;
                        error_log($logUpdate . "\n", 3, './suppliers/log/logUpdate.txt');
                        echo $logUpdate . "\n";
                    } */
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
    public function insertLastEuropArmsProduits($url, $urlImage, $login, $mdp, $notreDossier, $supplierName)
    {
        $lesCategories = [ //valeurs qui correspondent à la BDD
            ['categorie' => 'D', 'id' => 3],
            ['categorie' => 'C', 'id' => 1],
            ['categorie' => 'B', 'id' => 2],
            ['categorie' => 'A', 'id' => 9],
            ['categorie' => 'Vente libre', 'id' => 8],
        ];
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
                $leProduit = $this->entityManager->getRepository(Produit::class)->findOneByReference($row[2]); //check si ref existe
                if(!$leProduit && $row[11] != "Tenues de chasse & accessoires pour chiens" && $row[11] != "Chiens" && $row[11] != "Accessoires chien de chasse" && $row[12] != "Vêtements & bottes" && $row[12] != "Coffres et armoires fortes" && $row[12] != null && intval($row[8]) > 1 && $row[16] != null && $row[18] != null && $row[1] != null && $row[8] != null && $row[8] != ''){
                    //si le produit n'existe pas par rapport à la référence et que le prix trouvé est supérieur à 1, si image pas null et si nom pas null
                    $price = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[8])) * 100)));
                    $leProduitSlugCheck = $this->entityManager->getRepository(Produit::class)->findOneBySlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($row[1])))); //check si slug existe pour eviter doublons
                    $laMarque = $this->entityManager->getRepository(Marque::class)->findOneByName($row[3]);
                    $leCalibre = $this->entityManager->getRepository(Calibre::class)->findOneByCalibre($row[33]);
                    $subCategorie = $this->entityManager->getRepository(SubCategory::class)->findOneByName($row[12]);
                    $subCategorieTwo = $this->entityManager->getRepository(Famille::class)->findOneByName($row[13]);
                    $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName($supplierName); //OBLIGATOIREMENT DANS LA BDD
                    $passed = false; //pour verification prix promo

                    if ($price !== null && $leProduit == null && $leProduitSlugCheck == null) { 
                        $entity = new Produit();
                        $entity->setPrice($price);
                        $entity->setName($row[1]);
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
                        if ($leCalibre) {
                            if ($row[33] == "9 x 19") { //SOUS EXCEPTION 9x19
                                $leCalibre = $this->entityManager->getRepository(Calibre::class)->findOneByCalibre("9x19");
                            }
                            $entity->setCalibres($leCalibre);
                        } else {
                            if ($row[33] !== null) {
                                $calibre = new Calibre(); //sinon création du calibre
                                $calibre->setCalibre($row[33]);
                                $this->entityManager->persist($calibre);
                                $entity->setCalibres($calibre);
                            } else {
                                $entity->setCalibres(null);
                            }
                        }
                        if ($row[12] !== null) {
                            $entity->setSubtitle($row[12]);
                        } else {
                            $entity->setSubtitle($row[1]);
                        }
                        $entity->setReference($row[2]);
                        $entity->setSlug(strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($row[1])))); //lien unique pour pas que ça fasse des conflits
                        $nomImage = strtolower(str_replace(' ', '-', $this->enleverCaracSpecial($row[1])));

                        for ($y = 18; $y <= 20; $y++) {
                            if (
                                $row[$y] !== null
                            ) {
                                file_get_contents($urlImage . $row[$y]);
                                file_put_contents('./public/uploads/' . $nomImage . '-' . $row[$y],  file_get_contents($urlImage . $row[$y]));
                            }
                        }
                        $entity->setIllustration($nomImage . '-' . $row[18]); //obligatoire

                        if (file_exists("./public/uploads/" . $nomImage . '-' . $row[19])) { //verif si fichier n'est pas null
                            $entity->setIllustrationUn($nomImage . '-' . $row[19]);
                        }
                        if (file_exists("./public/uploads/" . $nomImage . '-' . $row[20])) { //verif si fichier n'est pas null
                            $entity->setIllustrationDeux($nomImage . '-' . $row[20]);
                        }
                        if ($row[15] !== null) { 
                            $entity->setDescription($row[15]);
                        } else {
                            $entity->setDescription("Descriptif complet à venir pour " . $entity->getName());
                        }

                        $entity->setIsAffiche(true); //est affiche sur le site
                        $entity->setIsBest(false);
                        $entity->setIsOccassion(false);
                        $entity->setIsForcesOrdre(false);
                        $entity->setMasse($row[5]);
                        $entity->setCodeRga($row[28]);

                        //sous-categorie
                        if (
                            !$subCategorie && $row[12] !== null
                        ) {
                            $subCategorieNew = new SubCategory();
                            $subCategorieNew->setName($row[12]);
                            $this->entityManager->persist($subCategorieNew);
                            $this->entityManager->flush();
                        }
                        if ($subCategorie && $row[12]) {
                            $entity->setSubCategory($subCategorie);
                        }

                        foreach ($lesCategories as $uneCategorie) {
                            if (
                                $row[16] == $uneCategorie['categorie']
                            ) { //verifie si categorie correspond
                                $categorieId = $uneCategorie['id'];
                                $familleId = null;

                                if ($uneCategorie['categorie'] == "C" && $row[11] == "Munitions & rechargement") {
                                    $categorieId = 12; //alors catégorie = Munition CAT. C
                                }
                                if ($uneCategorie['categorie'] == "B" && $row[11] == "Munitions & rechargement") {
                                    $categorieId = 11; //alors catégorie = Munition CAT. B
                                }
                                if ($uneCategorie['categorie'] == "B" && $row[11] == "Armes règlementées & maintien de l'ordre") { //si arme de poing
                                    $familleId = 1;
                                }
                                $laCategorie = $this->entityManager->getRepository(Category::class)->findOneById($categorieId);
                                $laFamille = $this->entityManager->getRepository(Famille::class)->findOneById($familleId);
                                $entity->setCategory($laCategorie);
                                $entity->setFamille($laFamille);
                            } //sinon categorie en null
                        }
                        //sous-categorie 2
                        if (!$subCategorieTwo && $row[13] !==null && !$subCategorieTwo->getId() == 1) {
                            $subCategorieTwo = new Famille();
                            $subCategorieTwo->setName($row[13]);
                            $this->entityManager->persist($subCategorieTwo);
                            $this->entityManager->flush();
                        }
                        if ($subCategorieTwo && $row[13] && !$subCategorieTwo->getId() == 1) {
                            $entity->setFamille($subCategorieTwo);
                        }

                        $entity->setQuantite($row[6] ?? 0);
                        $entity->setCaracteristique($row[14] ?? "-");
                        $entity->setFournisseurs($fournisseur);
                        $remises = $this->getBestApplicableRemises($entity);
                        if ($row[9] != $row[8] && intval($row[9] > 1)) {
                            $pricepromo = intval(preg_replace('/[^\d.]/', '', number_format(floatval(str_replace(',', '.', $row[9])) * 100)));
                            $entity->setPricePromo($pricepromo);
                        } else {
                            $bestRemise = $this->findBestRemiseForProduct($entity, $remises);
                        }
                        //trouver s'il y a déjà un prix promo appliqué pour notre selection du produit
                        if ($bestRemise) {
                            $entity->setPricePromo(intval($price * (1 - ($bestRemise->getRemise() / 100))));
                        } else {
                            $entity->setPricePromo(intval($price * 0.97)); // 3% de réduction par défaut
                        }
                        $this->entityManager->persist($entity);
                        $this->entityManager->flush();
                    }
                }
                /* $count++;
                if ($count % $batchSize == 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear(); // Libère les objets pour éviter la surcharge mémoire
                } */
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