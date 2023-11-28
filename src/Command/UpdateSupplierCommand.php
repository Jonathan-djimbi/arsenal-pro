<?php

namespace App\Command;

use App\Service\UpdateEuroparmSimacService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:EuroparmSimac',
    description: 'Mise à jour produits fournisseurs',
)]
class UpdateSupplierCommand extends Command
{
    protected $supplier;
    /**
     * RunCommand constructor.
     * @param SupplierUpdaterService $bourse
     */
    public function __construct(UpdateEuroparmSimacService $supplier) //constructeur
    {
        $this->supplier = $supplier;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTimeGlobal = microtime(true);
        $io = new SymfonyStyle($input, $output);

        // $startTimeIE = microtime(true);
        echo "Insertion nouveaux produits europ-arm\n";
        $this->supplier->insertLastEuropArmsProduits("sport-attitude.com", "https://europarm.fr/files/bibliotheque/photos-produits/", 'clients_ea', 'Ulf38l#3', "./suppliers/europarmListeProduits.csv", "Europ-arm"); //insertion nouveaux produits du mois
        // $endTimeIE = microtime(true);
        // $startTimeIS = microtime(true);
        echo "Insertion nouveaux produits simac\n";
        $this->supplier->insertLastEuropArmsProduits("simac.fr", "https://simac.fr/files/bibliotheque-simac/photos-produits/", 'clients_simac', 'Ulf38l#3', "./suppliers/simacListeProduits.csv", "Simac"); //insertion nouveaux produits du mois  
        // $endTimeIS = microtime(true);
        // $startTimeIA = microtime(true);
        // echo "Insertion nouveaux produits armsco\n";
        // $this->supplier->insertLastEuropArmsProduits("simac.fr", "https://simac.fr/files/bibliotheque-simac/photos-produits/", 'clients_simac', 'Ulf38l#3', "./suppliers/armscoListeProduits.csv", "Armsco"); //insertion nouveaux produits du mois  
        // $endTimeIA = microtime(true);
        // $startTimeDE = microtime(true);
        echo "Delete produits europ-arm non actifs\n";
        $this->supplier->produitSupprimeEuropArm("sport-attitude.com", "clients_ea", "Ulf38l#3", "./suppliers/europarmProduitsSupprimes.csv", "/Europ-Arm-export-produits-supprimes.csv");
        // $endTimeDE = microtime(true);
        // $startTimeDS = microtime(true);
        echo "Delete produits simac non actifs\n";
        $this->supplier->produitSupprimeEuropArm("simac.fr", "clients_simac", "Ulf38l#3", "./suppliers/simacProduitsSupprimes.csv", "/Simac-export-produits-supprimes.csv");
        // $endTimeDS = microtime(true);
        // $startTimeDA = microtime(true);
        // echo "Delete produits armsco non actifs\n";
        // $this->supplier->produitSupprimeEuropArm("simac.fr", "clients_simac", "Ulf38l#3", "./suppliers/simacProduitsSupprimes.csv", "/Simac-export-produits-supprimes.csv");
        // $endTimeDA = microtime(true);
        // $startTimeUE = microtime(true);
        echo "Update produits europ-arm\n";
        $this->supplier->updateEuropArmProduits("sport-attitude.com", "https://europarm.fr/files/bibliotheque/photos-produits/", "clients_ea", "Ulf38l#3", "./suppliers/europarmListeProduits.csv", "/Europ-Arm-export-produits-revendeurs.csv"); //maj stock et prix produits europ-arm
        // $endTimeUE = microtime(true);
        // $startTimeUS = microtime(true);
        echo "Update produits Simac\n";
        $this->supplier->updateEuropArmProduits("simac.fr", "https://simac.fr/files/bibliotheque-simac/photos-produits/", "clients_simac", "Ulf38l#3", "./suppliers/simacListeProduits.csv", "/Simac-export-produits-revendeurs.csv"); //maj stock et prix produits europ-arm
        // $endTimeUS = microtime(true);
        // $startTimeUS = microtime(true);
        // echo "Update produits armsco\n";
        // $this->supplier->updateEuropArmProduits("simac.fr", "https://simac.fr/files/bibliotheque-simac/photos-produits/", "clients_simac", "Ulf38l#3", "./suppliers/simacListeProduits.csv", "/Simac-export-produits-revendeurs.csv"); //maj stock et prix produits europ-arm
        // $endTimeUS = microtime(true);
        $io->success('Commande suppliers exécutée !');
        $endTimeGlobal = microtime(true);
        $executionTimeGlobal = ($endTimeGlobal - $startTimeGlobal) / 60;

        // $executionTimeDE = ($endTimeDE - $startTimeDE) / 60;
        // $executionTimeDS = ($endTimeDS - $startTimeDS) / 60;
        // $executionTimeUE = ($endTimeUE - $startTimeUE) / 60;
        // $executionTimeUS = ($endTimeUS - $startTimeUS) / 60;
        // $executionTimeIE = ($endTimeIE - $startTimeIE) / 60;
        // $executionTimeIS = ($endTimeIS - $startTimeIS) / 60;
        $executionTime = "Temps d'exécution total : " . $executionTimeGlobal . " minutes\n";

        // $executionTime .= "Temps d'exécution delete produits europ-arm : " . $executionTimeDE . " minutes\n";
        // $executionTime .= "Temps d'exécution delete produits simac : " . $executionTimeDS . " minutes\n";
        // $executionTime .= "Temps d'exécution update produits europ-arm : " . $executionTimeUE . " minutes\n";
        // $executionTime .= "Temps d'exécution update produits simac : " . $executionTimeUS . " minutes\n";
        // $executionTime .= "Temps d'exécution insert nouveaux produits europ-arm : " . $executionTimeIE . " minutes\n";
        // $executionTime .= "Temps d'exécution insert nouveaux produits simac : " . $executionTimeIS . " minutes\n";
        // Enregistrez le temps d'exécution 
        $logMsgExecTime = date('Y-m-d H:i:s') . ' - ' . $executionTime;
        error_log($logMsgExecTime . "\n", 3, "./suppliers/log/cron_execution_time.txt");

        //prend en moyenne 90 minutes pour exécuter l'ensemble
        return Command::SUCCESS;
    }
}
