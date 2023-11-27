<?php
namespace App\Command;

use App\Classe\Mail;
use App\Controller\MailProduitRestockController;
use App\Entity\MailRetourStock;
use App\Service\MailRestockService as ServiceMailRestockService;
use App\Entity\Produit;
use App\Service\FideliteMailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'app:mailAlertePointFideliteCommand')]
class MailAlertePointFideliteCommand extends Command{

    // public function __construct(EntityManagerInterface $entityManager)
    // {
    //     $this->entityManager = $entityManager;
    // }
    protected $mailing;
    /**
     * RunCommand constructor.
     * @param FideliteMailService $mailFideliteRecapCommand 
     */
    public function __construct(FideliteMailService $mailing) //constructeur
    {
        $this->mailing = $mailing;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $this->mailing->envoiMail('À propos de vos points fidélité ARSENAL PRO','alerte_points'); //appel du service
        $output->writeln('<comment>Envoi mail alerte points de fidélité...</comment>'); //message pour dire que la commande est lancée

        return Command::SUCCESS;

    }
}


?>
