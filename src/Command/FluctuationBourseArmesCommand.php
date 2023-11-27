<?php

namespace App\Command;

use App\Classe\Mail;
use App\Entity\BourseArmes;
use App\Service\FluctuationBourseArmesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:FluctuationBourseArmes',
    description: 'Mise à jour des prix pour la fluctuation des produits en bourse',
)]
class FluctuationBourseArmesCommand extends Command
{
    protected $bourse;
    /**
     * RunCommand constructor.
     * @param FluctuationBourseArmesService $bourse
     */
    public function __construct(FluctuationBourseArmesService $bourse) //constructeur
    {
        $this->bourse = $bourse;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $this->bourse->updatePrixArmes();

        $io->success('Prix produits en bourse MAJ !');

        return Command::SUCCESS;
    }
}
