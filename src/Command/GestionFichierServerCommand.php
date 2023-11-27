<?php
namespace App\Command;

use App\Classe\Mail;
use App\Service\GestionFichierServerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'app:gestionFichierServerCommand')]
class GestionFichierServerCommand extends Command{

    protected $gestionfichier;
    /**
     * RunCommand constructor.
     * @param GestionFichierService $gestionfichier 
     */
    public function __construct(GestionFichierServerService $gestionfichier) //constructeur
    {
        $this->gestionfichier = $gestionfichier;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $this->gestionfichier->supprimerFichierDepotVente(); //appel du service, supprimer les fichiers depot vente chaque an
        $output->writeln('<comment>Test...</comment>'); //message pour dire que la commande est lancée

        return Command::SUCCESS;

    }
}


?>
