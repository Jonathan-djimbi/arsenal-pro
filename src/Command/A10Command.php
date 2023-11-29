<?php

namespace App\Command;

use App\Service\A10Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:a10',
    description: 'Mise à jour produits fournisseurs',
)]
class A10Command extends Command
{
    protected $supplier;
    /**
     * RunCommand constructor.
     * @param SupplierUpdaterService $bourse
     */
    public function __construct(A10Service $supplier) //constructeur
    {
        $this->supplier = $supplier;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTimeGlobal = microtime(true);
        $io = new SymfonyStyle($input, $output);
        
        echo "Update produits A10\n";
        $this->supplier->updateA10Produits(); //maj stock et prix produits A10
        echo "Insertion nouveaux produits A10\n";
        $this->supplier->insertA10Produits(); //insertion nouveaux produits A10
        $io->success('Commande suppliers exécutée !');
        $endTimeGlobal = microtime(true);
        $executionTimeGlobal = ($endTimeGlobal - $startTimeGlobal) / 60;
        
        $executionTime = "Temps d'exécution total : " . $executionTimeGlobal . " minutes\n";
        
        $logMsgExecTime = date('Y-m-d H:i:s') . ' - ' . $executionTime;
        error_log($logMsgExecTime . "\n", 3, "./suppliers/log/cron_execution_time.txt");
        
        return Command::SUCCESS;
    }
}
