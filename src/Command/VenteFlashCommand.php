<?php
namespace App\Command;

use App\Classe\Mail;
use App\Controller\MailProduitRestockController;
use App\Entity\MailRetourStock;
use App\Service\MailRestockService as ServiceMailRestockService;
use App\Entity\Produit;
use App\Service\FideliteMailService;
use App\Service\VenteFlashProduitService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'app:venteFlashCommand')]
class VenteFlashCommand extends Command{

    // public function __construct(EntityManagerInterface $entityManager)
    // {
    //     $this->entityManager = $entityManager;
    // }
    protected $venteFlash;
    /**
     * RunCommand constructor.
     * @param VenteFlashProduitService $mailFideliteRecapCommand 
     */
    public function __construct(VenteFlashProduitService $venteFlash) //constructeur
    {
        $this->venteFlash = $venteFlash;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) //ENVOI DE MAIL AUTOMATIQUE
    {
        $this->venteFlash->updateAutoVenteFlashState(); //appel du service
        $output->writeln('<comment>Update if flash sale time out...</comment>'); //message pour dire que la commande a été lancée

        return Command::SUCCESS;

    }
}


?>
