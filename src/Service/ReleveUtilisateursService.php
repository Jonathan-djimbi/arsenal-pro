<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\CarteFidelite;
use App\Entity\MailRetourStock;
use App\Entity\Produit;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReleveUtilisateursService{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function envoiMail($state, $emplacement){ //ENVOI DE MAIL AUTOMATIQUE
       
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $adresse = '';
        $fideliteUser = '';
        $countRows = 2;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //HEADER PREMIERE LIGNE
        $sheet->setCellValue('A1', 'Nom');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Adresse');
        $sheet->setCellValue('D1', 'Code postal');
        $sheet->setCellValue('E1', 'Code pays');
        $sheet->setCellValue('F1', 'Téléphone');
        $sheet->setCellValue('G1', "Nombre d'achat");
        $sheet->setCellValue('H1', "Dernier achat effectué le");
        //LES DONNEES
        foreach($users as $user){
            $adresse = $this->entityManager->getRepository(Adress::class)->findBy(['user' => $user]);
            $fideliteUser = $this->entityManager->getRepository(CarteFidelite::class)->findOneBy(['user' => $user]);

            $sheet->setCellValue('A'. $countRows, $user->getFullname());
            $sheet->setCellValue('B'. $countRows, $user->getEmail());
            if($adresse){ //au-cas-où s'il y a un utilisateur n'a pas d'adresse (notamment pour les très ancien compte)
                $sheet->setCellValue('C'. $countRows, $adresse[0]->getAdress());
                $sheet->setCellValue('D'. $countRows, $adresse[0]->getPostal());
                $sheet->setCellValue('E'. $countRows, $adresse[0]->getCountry());
                $sheet->setCellValue('F'. $countRows, $adresse[0]->getPhone());
            }
            if($fideliteUser){ //au-cas-où s'il y a un utilisateur n'est pas fidele automatiquement (notamment pour les très ancien compte)
                $sheet->setCellValue('G' . $countRows, $fideliteUser->getNombreAchat());
                $sheet->setCellValue('H' . $countRows, $fideliteUser->getDernierAchat());
            }
            $countRows++;
        }

        $fichier = new Xlsx($spreadsheet);
        $fichier->save($emplacement); //sauvegarder excel
        if($state == "mail"){
            $mailEnvoi = new Mail();
            $subject = 'Les relevés des utilisateurs mensuel du site ARSENAL PRO';
            $getFichier = '/var/www/arsenal/compte/liste/site-client-' . date('m-Y') .'.xlsx'; //emplacement fichier du serveur UNIX
            $mailEnvoi->sendExcel('arsenalpro74@gmail.com', "Arsenal Pro", $subject, $getFichier, 'site-client-' . date('m-Y') .'.xlsx', $this->mailContent()); //envoi de mail
            $mailEnvoi->sendExcel('armurerie@arsenal-pro.com', "Arsenal Pro", $subject, $getFichier, 'site-client-' . date('m-Y') .'.xlsx', $this->mailContent()); //envoi de mail
        } 
        if ($state == "download"){
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $content = file_get_contents($emplacement); //on obtient le fichier déjà sauvegardé
            header("Content-Disposition: attachment; filename=site-client-" . date('m-Y') .".xlsx"); //en telechargement
            exit($content); //telecharger au bon format et quitter
        }

        return new Response();
    }

    public function mailContent(){
        $URL = "https://arsenal-pro.fr";
        $content = "<section style='font-family: arial;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='font-weight: normal;'>Les utilisateurs d'ARSENAL PRO</h2>
        <div>
            <h2 style='font-weight: normal;'>Bonjour, voici le relevé des clients mensuel en excel pour le site ARSENAL PRO</h2><br><br>
        </div>
        </div>
        </section></section>";

        return $content;
    }

    public function resetPointFidelite(){ //UNE FOIS CHAQUE ANNEE

        $fideliteUsers = $this->entityManager->getRepository(CarteFidelite::class)->findAll();

        foreach($fideliteUsers as $fidele){ //pour chaque utilisateurs fideles
            $fidele->setPoints(0); //on remet à 0 les points
        }
        $this->entityManager->flush();
        return new Response();
    }
}