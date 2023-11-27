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
    name: 'app:SupplierSecondaryUpdater',
    description: 'Mise à jour produits fournisseurs',
)]
class SupplierArmesSecondaryCommand extends Command
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

        echo "Insertion DCA-France\n";
        $this->supplier->insertDCAFrance();
        
        echo "Update produits DCA-France\n";
        $this->supplier->updateDCAFrance();

        echo "Update produits Armsco\n";
        $this->supplier->updateEuropArmProduits("armsco.fr", "https://armsco.fr/files/bibliotheque/photos-produits/", "clients_armsco", "Ulf38l#3", "./suppliers/armscoListeProduits.xls", "/Force de l'ordre-export-produits-revendeurs.xls"); //maj stock et prix produits europ-arm
      
        echo "Insertion nouveaux produits Armsco\n";
        $this->supplier->insertLastEuropArmsProduits("sport-attitude.com", "https://armsco.fr/files/bibliotheque/photos-produits/", 'clients_armsco', 'Ulf38l#3', "./suppliers/armscoListeProduits.xls", "/Force de l'ordre-export-produits-revendeurs.xls", "Armsco"); //insertion nouveaux produits du mois

        $io->success('Commande secondary suppliers exécutée !');
        //prend en moyenne 100-200 minutes pour exécuter l'ensemble
        return Command::SUCCESS;
    }
}
