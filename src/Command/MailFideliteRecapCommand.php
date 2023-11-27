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

#[AsCommand(name: 'app:mailFideliteRecapCommand')]
class MailFideliteRecapCommand extends Command{

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

    protected function execute(InputInterface $input, OutputInterface $output) //ENVOI DE MAIL AUTOMATIQUE
    {
        // $this->mailing->envoiMail('Voici le récapitulatif de vos points de fidélité ARSENAL PRO','recap'); //appel du service
        // $output->writeln('<comment>Envoi mail fidélité mensuel...</comment>'); //message pour dire que la commande est lancée

        return Command::SUCCESS;

    }
}


?>
