<?php

namespace App\Command;

use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'app:colissimoTesterToolBox',
    description: 'Add a short description for your command',
)]
class ColissimoTesterCommand extends Command{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->entityManager=$entityManager;
        parent::__construct();

    }

    public function generateTicketColissimo(){ //pour générer un ticket de livraison vers le service colissimo
            // $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId("22-12-07-Dilmac-5-63909f22d0bd4-R");
            $rey = $this->entityManager->getRepository(User::class)->findOneById(190);
            $acheteur = $this->entityManager->getRepository(Adress::class)->findByUser($rey)[0];
            $masse = 1; //kg
            $httpClient = HttpClient::create();
            $insuranceValue = 1500;
            //pallier prix assurance
            // if($prixCommandeTotal >= 15000 && $prixCommandeTotal < 25000){ //supérieur ou égal à 150 et inférieur à 250
            //     $insuranceValue = 15000;
            // }
            // if($prixCommandeTotal >= 25000 && $prixCommandeTotal < 40000){ //supérieur ou égal à 250 et inférieur à 400
            //     $insuranceValue = 30000;
            // }
            // if($prixCommandeTotal >= 40000 && $prixCommandeTotal < 75000){
            //     $insuranceValue = 50000;
            // }
            // if($prixCommandeTotal >= 75000 && $prixCommandeTotal < 175000){
            //     $insuranceValue = 100000;
            // }
            // if($prixCommandeTotal >= 175000 && $prixCommandeTotal < 400000){
            //     $insuranceValue = 200000;
            // }
            // if($prixCommandeTotal >= 400000) {
            //     $insuranceValue = 500000;
            // }
            $ticket = [
            "contractNumber" => "443747", 
            "password" => "ArsenalPro23+", 
            "outputFormat" => [
                    "x" => 0, 
                    "y" => 0, 
                    "outputPrintingType" => "PDF_A4_300dpi"
                ], 
            "letter" => [
                    "service" => [
                        "productCode" => "DOS", //A2P = point relais
                        "depositDate" => date("Y-m-d"), 
                        "orderNumber" => "S-" . date("Y-m-d") . "-" . $rey->getFirstName(), 
                        "commercialName" => "ARSENAL PRO" 
                    ], 
                    "parcel" => [
                            "weight" => $masse, 
                            "insuranceValue" => $insuranceValue,
                            //  "pickupLocationId" => "001055" //point relais
                        ], 
                    "sender" => [
                                "senderParcelRef" => "senderParcelRef", 
                                "address" => [
                                "companyName" => "ARSENAL Pro", 
                                "line0" => "", 
                                "line1" => "", 
                                "line2" => "710 Rue du Léman, C2a", 
                                "line3" => "", 
                                "countryCode" => "FR", 
                                "city" => "Chens-sur-Léman", 
                                "zipCode" => "74140" ,
                                "email" => "armurerie@arsenal-pro.com"
                                ] 
                            ], 
                    "addressee" => [
                                    "addresseeParcelRef" => "addresseeParcelRef", 
                                    "address" => [
                                        "lastName" => $rey->getLastname(), 
                                        "firstName" => $rey->getFirstname(), 
                                        "line0" => "", 
                                        "line1" => "", 
                                        "line2" => $acheteur->getAdress(), 
                                        "line3" => "", 
                                        "countryCode" => $acheteur->getCountry(), 
                                        "city" => $acheteur->getCity(), 
                                        "zipCode" => $acheteur->getPostal(), 
                                        "mobileNumber" => $acheteur->getPhone(), 
                                        "email" => $rey->getEmail() 
                                    ] 
                            ] 
                    ] 
        ]; 
            
            $response = $httpClient->request('POST','https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/2.0/generateLabel', [
                'headers' => [
                    "Content-Type" => "application/json;charset=UTF-8",
                ],
                'body' => json_encode($ticket, true),
            ]);
            if(!is_dir("./colissimo/". date("d-m-Y"). "")){ //si dossier date non existante
                mkdir("./colissimo/". date("d-m-Y"). ""); //création dossier date pour factures/
            }
            header("Content-type: application/octet-stream"); //conversion de la réponse en application/octet-stream
            header("Content-Type: application/pdf"); //puis conversion forcé en pdf
            $emplac_fichier = "/var/www/arsenal/colissimo/". date("d-m-Y"). "/colissimo-" . date("d-m-Y") .  "-"  . $rey->getFirstName() . ".pdf"; //emplacement fichier dans le serveur UNIX
            $nomfichier = "colissimo-" . date("d-m-Y") .  "-"  . $rey->getFirstName() . ".pdf";
            file_put_contents("./colissimo/". date("d-m-Y"). "/colissimo-" . date("d-m-Y") .  "-"  . $rey->getFirstName() . ".pdf", $response->getContent()); //nouveau pdf généré

            $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
            <div>
            <h2 style='text-align: center; font-weight: normal;'>NOUVEAU! <b>LABEL COLISSIMO</b> effectuée chez Arsenal Pro !</h2>
            <h3 style='font-weight: normal;'>Ce colissimo est destiné pour le client ". $rey->getFirstname() ." ". $rey->getLastname() ." !</h3>
            </div>
            </section></section>";
            $mail = new Mail();
            $mail->sendAvecFichierPDF("armurerie@arsenal-pro.com", "ARSENAL PRO", "Label colissimo pour " . $rey->getFullname() ."", $content, $emplac_fichier, $nomfichier, 4639500);
            $mail->sendAvecFichierPDF("arsenalpro74@gmail.com", "ARSENAL PRO", "Label colissimo pour " . $rey->getFullname() ."", $content, $emplac_fichier, $nomfichier, 4639500);

            return new Response();
        } 
        protected function execute(InputInterface $input, OutputInterface $output){
            $this->generateTicketColissimo();
        return Command::SUCCESS;

        }

}

