<?php

namespace App\Command;

use App\Classe\Mail;
use App\Entity\BourseArmes;
use App\Service\FluctuationBourseArmesService;
use App\Service\SupplierUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:SupplierUpdater',
    description: 'Mise à jour produits fournisseurs',
)]
class SupplierArmesCommand extends Command
{
    protected $supplier;
    /**
     * RunCommand constructor.
     * @param SupplierUpdaterService $bourse
     */
    public function __construct(SupplierUpdaterService $supplier) //constructeur
    {
        $this->supplier = $supplier;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        echo "Update produits europ-arm\n";
        $this->supplier->updateEuropArmProduits("sport-attitude.com", "https://europarm.fr/files/bibliotheque/photos-produits/", "clients_ea", "Ulf38l#3", "./suppliers/europarmListeProduits.xls", "/Europ-Arm-export-produits-revendeurs.xls"); //maj stock et prix produits europ-arm
        echo "Update produits Simac\n";
        $this->supplier->updateEuropArmProduits("simac.fr", "https://simac.fr/files/bibliotheque-simac/photos-produits/", "clients_simac", "Ulf38l#3", "./suppliers/simacListeProduits.xls", "/Simac-export-produits-revendeurs.xls"); //maj stock et prix produits europ-arm
        echo "Insertion nouveaux produits europ-arm\n";
        $this->supplier->insertLastEuropArmsProduits("sport-attitude.com", "https://europarm.fr/files/bibliotheque/photos-produits/", 'clients_ea', 'Ulf38l#3', "./suppliers/europarmListeProduits.xls", "/Europ-Arm-export-produits-revendeurs.xls", "Europ-arm"); //insertion nouveaux produits du mois
        echo "Insertion nouveaux produits simac\n";
        $this->supplier->insertLastEuropArmsProduits("simac.fr", "https://simac.fr/files/bibliotheque-simac/photos-produits/", 'clients_simac', 'Ulf38l#3', "./suppliers/simacListeProduits.xls", "/Simac-export-produits-revendeurs.xls", "Simac"); //insertion nouveaux produits du mois  
        echo "Delete produits europ-arm non actifs\n";
        $this->supplier->produitSupprimeEuropArm("sport-attitude.com", "clients_ea", "Ulf38l#3", "./suppliers/europarmProduitsSupprimes.csv", "/Europ-Arm-export-produits-supprimes.csv");
        echo "Delete produits simac non actifs\n";
        $this->supplier->produitSupprimeEuropArm("simac.fr", "clients_simac", "Ulf38l#3", "./suppliers/simacProduitsSupprimes.csv", "/Simac-export-produits-supprimes.csv");

        $io->success('Commande suppliers exécutée !');
        //prend en moyenne 100-200 minutes pour exécuter l'ensemble
        return Command::SUCCESS;
    }
}
