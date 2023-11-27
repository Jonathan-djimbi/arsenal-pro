<?php
namespace App\Command;

use App\Controller\MailProduitRestockController;
use App\Entity\MailRetourStock;
use App\Service\MailRestockService as ServiceMailRestockService;
use App\Entity\Produit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use MailRestockService;

#[AsCommand(name: 'app:mailRestockClient')]
class MailRestockClientCommand extends Command{

    // public function __construct(EntityManagerInterface $entityManager)
    // {
    //     $this->entityManager = $entityManager;
    // }
    protected $mailRestockService;

    /**
     * RunCommand constructor.
     * @param ServiceMailRestockService $mailRestockService
     */
    public function __construct(ServiceMailRestockService $mailRestockService) //constructeur
    {
        $this->mailRestockService = $mailRestockService;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) //ENVOI DE MAIL AUTOMATIQUE A L'AIDE DU php bin/console app:mailRestockClient
    {
        $this->mailRestockService->envoiMail(); //appel du service
        $this->mailRestockService->renvoieMail(); //pour les gens ayant regardé mais ne sont pas retournés
        $output->writeln('<comment>Envoi mail restock et mail relance consultation...</comment>'); //message pour dire que la commande est lancée

        return Command::SUCCESS;

    }
}


?>