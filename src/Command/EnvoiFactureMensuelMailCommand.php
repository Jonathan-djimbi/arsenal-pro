<?php

namespace App\Command;

use App\Classe\Mail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:EnvoiFactureMensuelMail',
    description: 'Add a short description for your command',
)]
class EnvoiFactureMensuelMailCommand extends Command
{
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->mailing->sendFacturesRecapMensuel("arsenalpro74@gmail.com");
        $this->mailing->sendFacturesRecapMensuel("armurerie@arsenal-pro.com");

        $io->success('Factures envoyées par mail !');

        return Command::SUCCESS;
    }
}
