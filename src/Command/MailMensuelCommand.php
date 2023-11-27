<?php
namespace App\Command;

use App\Classe\Mail;
use App\Controller\MailProduitRestockController;
use App\Entity\MailRetourStock;
use App\Service\MailRestockService as ServiceMailRestockService;
use App\Entity\Produit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'app:mailTermesRecherches')]
class MailMensuelCommand extends Command{

    // public function __construct(EntityManagerInterface $entityManager)
    // {
    //     $this->entityManager = $entityManager;
    // }
    protected $mailing;
    /**
     * RunCommand constructor.
     * @param Mail $mailTermesRecherches
     */
    public function __construct(Mail $mailing) //constructeur
    {
        $this->mailing = $mailing;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) //ENVOI DE MAIL AUTOMATIQUE A L'AIDE DU php bin/console app:mailTermesRecherches
    {
        $this->mailing->sendSearchResults("arsenalpro74@gmail.com"); //appel method de la classe Mail
        $this->mailing->sendSearchResults("armurerie@arsenal-pro.com"); 

        $output->writeln('<comment>Envoi mail mensuel...</comment>'); //message pour dire que la commande est lancée

        return Command::SUCCESS;

    }
}


?>
