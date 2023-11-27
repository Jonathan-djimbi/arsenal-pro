<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Fournisseurs;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;

class UpdateB2BService extends AbstractController
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

    public function updateStockFromB2BApi_CS($apiUrl, $apiKey_CS, $fournisseur)
    {
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
        /**
         * $data : tableau des stocks
         * @param string $ref Référence de l'article.
         * @param string $lib Nom de l'article.
         * @param float $pvht Prix de l'article HT.
         * @param float $pvpcttc Prix de l'article TTC conseillé.
         * @param int $stock Stock de l'article.
         */

        $fournisseur = $this->entityManager->getRepository(Fournisseurs::class)->findOneByName($fournisseur);
        $batchSize = 50;
        $i = 1;
        $this->entityManager->beginTransaction();
        foreach ($data as $articleData) {
            // Retrouvez le produit en fonction de la référence (ref).
            $product = $this->entityManager->getRepository(Produit::class)->findOneBy([
                'reference' => $articleData['ref'],
            ]);

            if ($product) {
                // Mettez à jour les informations de stock et de prix.
                $product->setQuantite($articleData['stock']);
                $price = $articleData['pvpcttc'] * 100;
                $remises = $this->getBestApplicableRemises($product);
                $bestRemise = $this->findBestRemiseForProduct($product, $remises);
                if ($product->getPrice() != $price) {
                    $product->setPrice($price);
                }
                if ($bestRemise) {
                    $product->setPricePromo(intval($price * (1 - ($bestRemise->getRemise() / 100))));
                } else {
                    $product->setPricePromo(intval($price * 0.97)); // 3% de réduction par défaut
                }
                $product->setIsAffiche(true);
                echo "Produit update : " . $articleData['ref'] . "\n";
                
                // Sauvegardez les modifications.
                $this->entityManager->persist($product);
                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    echo "Flush et clear \n";
                }
                $i++;
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->entityManager->commit();
        echo "Flush et clear FIN\n";

        return count($data) . ' products updated for stock and price from the API.';
    
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
    private function findBestRemiseForProduct($product, $remises)
    {
        foreach ($remises as $remise) {
            return $remise;
        }
        return null;
    }
}