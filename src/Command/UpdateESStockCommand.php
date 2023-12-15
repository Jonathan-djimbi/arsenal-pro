<?php

namespace App\Command;

use App\Service\UpdateStockEuroparmSimacService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:EuroparmSimacStock',
    description: 'Mise à jour produits fournisseurs',
)]
class UpdateESStockCommand extends Command
{
    protected $supplier;
    /**
     * RunCommand constructor.
     * @param SupplierUpdaterService $bourse
     */
    public function __construct(UpdateStockEuroparmSimacService $supplier) //constructeur
    {
        $this->supplier = $supplier;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTimeGlobal = microtime(true);
        $io = new SymfonyStyle($input, $output);
        
        /* // $startTimeDE = microtime(true);
        echo "Delete produits europ-arm non actifs\n";
        $this->supplier->produitSupprimeEuropArm("sport-attitude.com", "clients_ea", "Ulf38l#3", "./suppliers/europarmProduitsSupprimes.csv", "/Europ-Arm-export-produits-supprimes.csv");
        // $endTimeDE = microtime(true);
        // $startTimeDS = microtime(true);
        echo "Delete produits simac non actifs\n";
        $this->supplier->produitSupprimeEuropArm("simac.fr", "clients_simac", "Ulf38l#3", "./suppliers/simacProduitsSupprimes.csv", "/Simac-export-produits-supprimes.csv");
        // $endTimeDS = microtime(true); */
        // $startTimeUE = microtime(true);
        echo "Update produits europ-arm\n";
        $this->supplier->updateEuropArmProduits("sport-attitude.com", "clients_ea", "Ulf38l#3", "./suppliers/europarmStockProduits.csv", "/Europ-Arm-export-dispo-simplifie.csv"); //maj stock et prix produits europ-arm
        // $endTimeUE = microtime(true);
        // $startTimeUS = microtime(true);
        echo "Update produits Simac\n";
        $this->supplier->updateEuropArmProduits("simac.fr", "clients_simac", "Ulf38l#3", "./suppliers/simacStockProduits.csv", "/Simac-export-dispo-simplifie.csv"); //maj stock et prix produits europ-arm
        // $endTimeUS = microtime(true);
        // $startTimeIE = microtime(true);
        echo "Insertion nouveaux produits europ-arm\n";
        $endTimeGlobal = microtime(true);
        $executionTimeGlobal = ($endTimeGlobal - $startTimeGlobal) / 60;
        /* 
        $executionTimeDE = ($endTimeDE - $startTimeDE) / 60;
        $executionTimeDS = ($endTimeDS - $startTimeDS) / 60;
        $executionTimeUE = ($endTimeUE - $startTimeUE) / 60;
        $executionTimeUS = ($endTimeUS - $startTimeUS) / 60;
        $executionTimeIE = ($endTimeIE - $startTimeIE) / 60;
        $executionTimeIS = ($endTimeIS - $startTimeIS) / 60; */
        $executionTime = "Temps d'exécution total Stock : " . $executionTimeGlobal . " minutes\n";
        /* 
        $executionTime .= "Temps d'exécution delete produits europ-arm : " . $executionTimeDE . " minutes\n";
        $executionTime .= "Temps d'exécution delete produits simac : " . $executionTimeDS . " minutes\n";
        $executionTime .= "Temps d'exécution update produits europ-arm : " . $executionTimeUE . " minutes\n";
        $executionTime .= "Temps d'exécution update produits simac : " . $executionTimeUS . " minutes\n";
        $executionTime .= "Temps d'exécution insert nouveaux produits europ-arm : " . $executionTimeIE . " minutes\n";
        $executionTime .= "Temps d'exécution insert nouveaux produits simac : " . $executionTimeIS . " minutes\n"; */
         // Enregistrez le temps d'exécution 
        $logMsgExecTime = date('Y-m-d H:i:s') . ' - ' . $executionTime;
        error_log($logMsgExecTime . "\n", 3, "./suppliers/log/cron_execution_time.txt");
        
        //prend en moyenne 100-200 minutes pour exécuter l'ensemble
        return Command::SUCCESS;
    }
}