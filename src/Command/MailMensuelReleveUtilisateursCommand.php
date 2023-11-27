<?php

namespace App\Command;

use App\Controller\LibraryController;
use App\Service\ReleveUtilisateursService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:MailMensuelReleveUtilisateurs')]
class MailMensuelReleveUtilisateursCommand extends Command
{
    protected $mailing;
    /**
     * RunCommand constructor.
     * @param ReleveUtilisateursService $mailFideliteRecapCommand ReleveUtilisateursService
     */
    public function __construct(ReleveUtilisateursService $mailing) //constructeur
    {
        $this->mailing = $mailing;
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->mailing->envoiMail("mail","./compte/liste/site-client-" . date('m-Y') .".xlsx"); //appel du service
        $output->writeln('Mail mensuel relevé utilisateurs envoyé !');

        return Command::SUCCESS;
    }
}

#[AsCommand(name: 'app:MailMensuelNewsletter')] //exécuter à chaque mois
class ReleveNewsletterCommand extends Command
{
    protected $lib;
    private EntityManagerInterface $em;
    /**
     * RunCommand constructor.
     * @param ReleveUtilisateursService $mailFideliteRecapCommand ReleveUtilisateursService
     */
    public function __construct(LibraryController $lib, EntityManagerInterface $em) //constructeur
    {
        $this->lib = $lib;
        $this->em = $em;
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->lib->envoiEmailNewsletter($this->em);
        $output->writeln('Liste email newsletter excel envoyée !');

        return Command::SUCCESS;
    }
}


#[AsCommand(name: 'app:ResetPointFidelite')] //exécuter à chaque début d'année, le 1er janvier à 1 heure du matin
class MajReleveUtilisateursCommand extends Command
{
    protected $maj;
    /**
     * RunCommand constructor.
     * @param ReleveUtilisateursService $mailFideliteRecapCommand ReleveUtilisateursService
     */
    public function __construct(ReleveUtilisateursService $maj) //constructeur
    {
        $this->maj = $maj;
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $this->maj->majExcelClient(); //appel du service
        $this->maj->resetPointFidelite();
        $output->writeln('Les points des utilisateurs sont remise à 0 !');

        return Command::SUCCESS;
    }
}