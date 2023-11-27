<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MailRetourStock;
use App\Entity\OrderDetails;
use App\Entity\Produit;
use App\Entity\VenteFlash;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;

class UpdateStockEuroparmSimacService extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function updateEuropArmProduits($url, $login, $mdp, $notreDossier, $fichierFTP)
    {
        $reader = new ReaderCsv();
        $reader->setTestAutoDetect(false); 

        // Connexion FTP
        $ftpLogFile = './suppliers/log/logFTPstock.txt';
        $ftp = ftp_connect($url, 21);
        if (!$ftp) {
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur de connexion au FTP : " . $url . ". Veuillez tester si le domaine fonctionne.";
            file_put_contents($ftpLogFile, $errorMessage . PHP_EOL, FILE_APPEND);
            die($errorMessage);
        }
        file_put_contents($ftpLogFile, "Connexion au FTP avec succès." . PHP_EOL, FILE_APPEND);

        $login_result = ftp_login($ftp, $login, $mdp);
        if (!$login_result) {
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur de Login au FTP : " . $url . ". Veuillez tester si le login et le mot de passe sont corrects.";
            file_put_contents($ftpLogFile, $errorMessage . PHP_EOL, FILE_APPEND);
            die($errorMessage);
        }
        file_put_contents($ftpLogFile, "Login au FTP avec succès." . PHP_EOL, FILE_APPEND);

        ftp_pasv($ftp, true);
        if (ftp_get($ftp, $notreDossier, $fichierFTP, FTP_BINARY)) {
            $message = "[" . date('Y-m-d H:i:s') . "] Le fichier $notreDossier a été écrit avec succès";
            file_put_contents($ftpLogFile, $message . PHP_EOL, FILE_APPEND);
        } else {
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Il y a eu un problème lors du chargement du fichier $notreDossier";
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
        // Utilisation d'une transaction pour les opérations de base de données
        $this->entityManager->beginTransaction();
        foreach (array_slice($data, 1) as $row) {
            $this->processProductRow($row, $products);
        }
        // Valider la transaction
        $this->entityManager->commit();
        echo "Mise à jour du stock terminée\n";
        return new Response();
    }

    private function processProductRow($row)
    {
        $reference = $row[0];
        $newStock = intval($row[2]);
        $product = $this->entityManager->getRepository(Produit::class)->findOneBy(['reference' => $reference]);

        if ($product) {
            $product->setQuantite($newStock);

            $this->entityManager->flush();

            $logMessage = date('Y-m-d H:i:s') . ' - ' . $reference . " - Nouveau stock : " . $newStock . "\n";
            file_put_contents('./suppliers/log/logStock.txt', $logMessage, FILE_APPEND);
            echo $logMessage;
        }
    }

}