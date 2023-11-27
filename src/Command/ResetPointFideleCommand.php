<?php

namespace App\Command;

use App\Classe\Mail;
use App\Entity\BourseArmes;
use App\Service\FluctuationBourseArmesService;
use App\Service\ReleveUtilisateursService;
use App\Service\SupplierUpdaterService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:resetPointFidelite',
    description: 'Met 0 les points de tout le monde',
)]
class ResetPointFideleCommand extends Command
{
    protected $userFidele;
    /**
     * RunCommand constructor.
     * @param ReleveUtilisateursService $userFidele
     */
    public function __construct(ReleveUtilisateursService $userFidele) //constructeur
    {
        $this->userFidele = $userFidele;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $this->bourse->updatePrixArmes();
        $this->userFidele->resetPointFidelite(); //maj stock et prix produits europ-arm

        $io->success('Tous les points ont été remis à 0 !');

        return Command::SUCCESS;
    }
}
