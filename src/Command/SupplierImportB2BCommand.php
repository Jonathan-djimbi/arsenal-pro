<?php

namespace App\Command;

use App\Service\ImportB2BService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:supplierb2b:import',
)]
class SupplierImportB2BCommand extends Command
{
    private $importB2BService;

    public function __construct(ImportB2BService $importB2BService)
    {
        parent::__construct();

        $this->importB2BService = $importB2BService;
    }

    protected function configure()
    {
        $this
            ->setName('supplierb2b:import')
            ->setDescription('Import supplier data from B2B API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $apiKey_CS = 'fwLiBO-1PKz5W2eieJopb4bBVNBG-rjzUInjft6c8uc';
        $fournisseur = 'ColombiSports';
        // You can pass the API URL and API Key to the service method.
        $this->importB2BService->importDataFromB2BApi_CS('https://b2b.colombisports.com/api/v1/article', $apiKey_CS, $fournisseur);

        $io->success('Supplier data updated successfully.');
        return Command::SUCCESS;
    }
}
