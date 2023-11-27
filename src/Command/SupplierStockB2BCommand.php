<?php

namespace App\Command;

use App\Service\UpdateB2BService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:supplierb2b:update',
)]
class SupplierStockB2BCommand extends Command
{
    private $updateB2BService;

    public function __construct(UpdateB2BService $updateB2BService)
    {
        parent::__construct();

        $this->updateB2BService = $updateB2BService;
    }

    protected function configure()
    {
        $this
            ->setName('supplierb2b:update')
            ->setDescription('Update supplier stock & price from B2B API');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $apiKey_CS = 'fwLiBO-1PKz5W2eieJopb4bBVNBG-rjzUInjft6c8uc';
        $fournisseur = 'ColombiSports';
        // You can pass the API URL and API Key to the service method.
        $this->updateB2BService->updateStockFromB2BApi_CS('https://b2b.colombisports.com/api/v1/stock', $apiKey_CS, $fournisseur);

        $io->success('Supplier data updated successfully.');
        return Command::SUCCESS;
    }
}
