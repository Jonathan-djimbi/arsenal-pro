<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\ComptesDocuments;
use App\Entity\HistoriqueReservation;
use App\Entity\Newsletter;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\ProfessionnelAssociationCompte;
use App\Entity\User;
use App\Form\ColissimoGeneratorType;
use App\Service\ReleveUtilisateursService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

class LibraryController extends AbstractController
{
    private ReleveUtilisateursService $downloadClient; //appel du service releve client
    private EntityManagerInterface $entityManager;

    public function __construct(ReleveUtilisateursService $downloadClient, EntityManagerInterface $entityManager){
        $this->downloadClient = $downloadClient;
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/library/', name: 'app_library')]
    public function index(): Response
    {
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){ //verifie si connecté et admin
            return $this->redirectToRoute('app_login');
        }

        return $this->render('library/index.html.twig');
    }

    #[Route('/admin/library/livraison', name: 'app_library_livraison')]
    public function livraisonPage(): Response
    {
        return $this->render('library/livraisons.html.twig');
    }

    #[Route('/admin/library/nos-clients', name: 'app_library_clients')]
    public function clientDownload() : Response
    {
        $this->downloadClient->envoiMail("download","./../compte/liste/site-client-". date('m-Y') .".xlsx"); //telecharger un fichier excel depuis le navigateur
        return new Response();
    }

    #[Route('/admin/library/factures', name: 'app_library_factures')]
    public function factures(Request $req): Response
    {
        // dd($req);
        $titre = "Les factures";
        $erreur = '';
        $facturecount = glob("./../factures/*/*"); //compte combien de factures sont dans le dossier
        // dd($facturecount);
        $nomFile = [];
        if(!isset($_POST['submit'])){
            foreach($facturecount as $i){
                $nomFile[] = ["nom" => substr($i, strrpos($i, '/') + 1), "mois" => explode("/", $i)[3]]; //nom => prend le dernier /
            }
        } else { //si submit
            $validation = false;
            $detecte = $_POST['searchDocs'];
            $trouveScan = glob("./../factures/*/*");
            // dd($trouveScan);
            foreach ($trouveScan as $trouve){ //boucle scan
                if(preg_match("/\b" . $detecte . "\b/iu", $trouve)){ //si une partie de la recherche match 
                    $nomFile[] = ["nom" => substr($trouve, strrpos($trouve, '/') + 1), "mois" => explode("/", $trouve)[3]];
                    $validation = true;
                } 
            }
            if(!$validation){
                $erreur = 'Aucun résultat trouvé.';
            }
        }
        // echo $trouveScan;
        // dd($nomFile);
        return $this->render('library/documents.html.twig', [
            'src' => $nomFile,
            'titre' => $titre,
            "type" => "factures",
            "erreur" => $erreur
        ]);
    }

    #[Route('/admin/library/colissimo', name: 'app_library_colissimo')]
    public function colissimo(Request $req): Response
    {
        $titre = "Les livraisons par Colissimo";
        $erreur = '';
        $colissimocount = glob("./../colissimo/*/*"); //compte combien de fichiers sont dans le dossier
        // dd($facturecount);
        $nomFile = [];
        if(!isset($_POST['submit'])){
            foreach($colissimocount as $c){
                $nomFile[] = ["nom" => substr($c, strrpos($c, '/') + 1), "date" => explode("/", $c)[3]]; //nom => prend le dernier /
            }
        } else { //si submit
            $validation = false;
            $detecte = $_POST['searchDocs'];
            $trouveScan = glob("./../colissimo/*/*");
            // dd($trouveScan);
            foreach ($trouveScan as $trouve){ //boucle qui fouille tous les fichiers du $trouveScan
                if(preg_match("/\b" . $detecte . "\b/iu", $trouve)){ //si une partie de la recherche match 
                    $nomFile[] = ["nom" => substr($trouve, strrpos($trouve, '/') + 1), "date" => explode("/", $trouve)[3]];
                    $validation = true;
                }
            }
            if(!$validation){
                $erreur = 'Aucun résultat trouvé.';
            }
        }
        // dd($nomFile);
        return $this->render('library/documents.html.twig', [
            'src' => $nomFile,
            'titre' => $titre,
            'type' => "colissimo",
            'erreur' => $erreur
        ]);
    }

    #[Route('/admin/library/factures-mensuel', name: 'app_library_factures_mensuel')]
    public function facturesMensuel(Request $req): Response
    {
        // dd($req);
        $titre = "Les factures mensuel";
        $erreur = '';
        $facturecount = glob("./../factures/backup/*"); //compte combien de factures sont dans le dossier
        // dd($facturecount);
        $nomFile = [];
        if(!isset($_POST['submit'])){
            foreach($facturecount as $i){
                $nomFile[] = ["nom" => substr($i, strrpos($i, '/') + 1)];
            }
        } else { //si submit
            $validation = false;
            $detecte = $_POST['searchDocs'];
            $trouveScan = glob("./../factures/backup/*");
            foreach ($trouveScan as $trouve){ //boucle scan
                // dd($detecte, $trouve);
                if(preg_match("/\b" . $detecte . "\b/iu", $trouve)){ //si une partie de la recherche match 
                    $nomFile[] = ["nom" => substr($trouve, strrpos($trouve, '/') + 1)];
                    $validation = true;
                } 
            }
            if(!$validation){
                $erreur = 'Aucune archive trouvée.';
            }
        }
        // echo $trouveScan;
        // dd($nomFile);
        return $this->render('library/documents.html.twig', [
            'src' => $nomFile,
            'titre' => $titre,
            "type" => "factures-mensuel",
            "erreur" => $erreur
        ]);
    }

    #[Route('/admin/library/factures/{mois}/{src}', name: 'app_library_pdf_factures')]
    public function readPDFFac($mois, $src) : Response{
        $lePdf = '';
        $link = "./../factures/". $mois ."/" . $src; 

        header("Content-Type: application/pdf"); //puis conversion forcé en pdf
        // header('Content-Disposition: attachment; filename=". $src ."');
        try { //essai pour voir si le file_get_contents ne retourne pas d'erreur(s)
            $lePdf = file_get_contents($link);
            echo $lePdf;
        }
        catch (Exception $e) {
            echo "Cette facture n'est pas disponible sur le serveur. Veuillez consulter la boîte mail d'ARSENAL PRO pour trouver si possible la facture.";
        }

        return new Response();
    }

    #[Route('/admin/library/livraison/colissimo/{jour}/{src}', name: 'app_library_pdf_colissimo')]
    public function readPDFCol($jour, $src) : Response{
        $pdfcol = '';
        $link = "./../colissimo/". $jour ."/" . $src; 

        header("Content-Type: application/pdf"); //puis conversion forcé en pdf
        // header('Content-Disposition: attachment; filename=". $src ."');
        $pdfcol = file_get_contents($link);   
        echo $pdfcol;
        return new Response();
    }

    #[Route('/admin/library/factures-mensuel/{fichier}', name: 'app_library_dl_factures_mensuel')]
    public function downloadArchiveFacture($fichier) : Response{
        $archive = '';
        $link = "./../factures/backup/". $fichier; 

        header("Content-Type: application/x-gzip"); 

        $archive = file_get_contents($link);
                
        echo $archive;
        return new Response();
    }

    #[Route('/admin/library/commande-clients/', name: 'app_library_dl_commande_clients')]
    public function downloadCommandes(EntityManagerInterface $em) : Response{

        $orders = $em->getRepository(Order::class)->findOrdersAsc();
        $reservations = $em->getRepository(HistoriqueReservation::class)->findReservationsAsc();
        $saveMonth = "";
        // $ordersDetails = $em->getRepository(OrderDetails::class)->findAll();
        $countRows = 2;
        $spreadsheet = new Spreadsheet();
        //onglet commandes
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Commandes');
        //onglet réservations
        $sheetTwo = $spreadsheet->createSheet();
        $sheetTwo->setTitle('Réservations');

        $sheets = [$sheet, $sheetTwo];
        //HEADER PREMIERE LIGNE
        foreach($sheets as $oneSheet){
            $oneSheet->setCellValue('A1', 'Date saisie');
            $oneSheet->setCellValue('B1', 'Raison sociale');
            $oneSheet->setCellValue('C1', 'Nom du client');
            $oneSheet->setCellValue('D1', 'Prénom du client');
            $oneSheet->setCellValue('E1', 'N° document');
            $oneSheet->setCellValue('F1', 'Montant HT');
            $oneSheet->setCellValue('G1', "Montant TVA");
            $oneSheet->setCellValue('H1', "Montant TTC");
            $oneSheet->setCellValue('I1', "Montant Remboursé");
            $oneSheet->setCellValue('J1', "Date de remboursement");
        }

        //LES DONNEES
        $this->commandsArrayInsertion($orders, $sheet, $countRows, $em, "commandes", NULL, NULL); //commandes
        $this->commandsArrayInsertion($reservations, $sheetTwo, $countRows, $em, "reservations", NULL, NULL); //réservations

        $emplacement = "./../compte/liste/site-commande-clients.xlsx";
        $fichier = new Xlsx($spreadsheet);
        $fichier->save($emplacement); //sauvegarder excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $content = file_get_contents($emplacement); //on obtient le fichier déjà sauvegardé
        header("Content-Disposition: attachment; filename=site-commande-clients.xlsx"); //en telechargement
        exit($content); //telecharger au bon format et quitter

        return new Response();
    }


    #[Route('/admin/library/journal-commande-clients-mensuel/', name: 'app_library_dl_commande_clients_mensuel')]
    public function downloadCommandesMensuel(EntityManagerInterface $em){
        $this->downloadCommandesSpecific($em, "m",date('m'));
    }

    #[Route('/admin/library/journal-commande-clients-annuel/', name: 'app_library_dl_commande_clients_annuel')]
    public function downloadCommandesAnnuel(EntityManagerInterface $em){
        $this->downloadCommandesSpecific($em, "Y",date('Y'));
    }

    public function downloadCommandesSpecific(EntityManagerInterface $em, $period, $time) : Response{

        $orders = $em->getRepository(Order::class)->findOrdersAsc();
        $reservations = $em->getRepository(HistoriqueReservation::class)->findReservationsAsc();
        $countRows = 2;
        $spreadsheet = new Spreadsheet();
        //onglet commandes
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Commandes');
        //onglet réservations
        $sheetTwo = $spreadsheet->createSheet();
        $sheetTwo->setTitle('Réservations');
        
        $sheets = [$sheet, $sheetTwo];
        //HEADER PREMIERE LIGNE
        foreach($sheets as $oneSheet){
            $oneSheet->setCellValue('A1', 'Date saisie');
            $oneSheet->setCellValue('B1', 'Raison sociale');
            $oneSheet->setCellValue('C1', 'Nom du client');
            $oneSheet->setCellValue('D1', 'Prénom du client');
            $oneSheet->setCellValue('E1', 'N° document');
            $oneSheet->setCellValue('F1', 'Montant HT');
            $oneSheet->setCellValue('G1', "Montant TVA");
            $oneSheet->setCellValue('H1', "Montant TTC");
            $oneSheet->setCellValue('I1', "Montant remboursé TTC");
            $oneSheet->setCellValue('I1', "Montant Remboursé");
            $oneSheet->setCellValue('J1', "Date de remboursement");
        }
        //LES DONNEES
        // foreach($orders as $order){
        //     $proAssoc = $em->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($order->getUser());

        //     if($order->getState() !== 0 && date_format($order->getCreateAt(),$period) == $time){
        //         // dd(date_format($order->getCreateAt(),"m"));
        //         $sheet->setCellValue('A'. $countRows, date_format($order->getCreateAt(), 'd/m/Y'));
        //         if($proAssoc){
        //             $sheet->setCellValue('B'. $countRows, $proAssoc->getRaisonSocial());
        //         } else {
        //             $sheet->setCellValue('B'. $countRows, "");
        //         }
        //         $sheet->setCellValue('C'. $countRows, $order->getUser()->getLastname());
        //         $sheet->setCellValue('D'. $countRows, $order->getUser()->getFirstname());
        //         $sheet->setCellValue('E'. $countRows, $order->getReference());
        //         $sheet->setCellValue('F'. $countRows, (number_format(($order->getTotalFinal()/100) / 1.2 ,3)));
        //         $sheet->setCellValue('G'. $countRows, (number_format((($order->getTotalFinal()) - ($order->getTotalFinal() / 1.2)) / 100, 3)));
        //         $sheet->setCellValue('H'. $countRows, $order->getTotalFinal() / 100);
        //         $sheet->setCellValue('I'. $countRows, $order->getRefundAmount() / 100);
        //         if($order->getRefundedAt() !== null){
        //             $sheet->setCellValue('J'. $countRows, date_format($order->getRefundedAt(), 'd/m/Y'));
        //         } else {
        //             $sheet->setCellValue('J'. $countRows, "");
        //         }
        //         $countRows++;
        //     }
        // }

        $this->commandsArrayInsertion($orders, $sheet, $countRows, $em, "commandes", $period, $time); //commandes
        $this->commandsArrayInsertion($reservations, $sheetTwo, $countRows, $em, "reservations", $period, $time); //réservations

        if($period == "m"){ //si date en mois
            if(!is_dir("./../compte/liste/journal-des-ventes/". date('Y') . "/". $time)){
                mkdir("./../compte/liste/journal-des-ventes/". date('Y') . "/". $time);
            }
            $emplacementNomFichier = date('Y') . "/". $time ."/journal-des-ventes-" . date('Y') . "-" .$time .".xlsx";
        } elseif($period == "Y") { //si date en annee strictement
            if(!is_dir("./../compte/liste/journal-des-ventes/". date('Y'))){
                mkdir("./../compte/liste/journal-des-ventes/". date('Y'));
            }
            $emplacementNomFichier = date('Y')."/journal-des-ventes-" . date('Y') .".xlsx";
        }
        $emplacement = "./../compte/liste/journal-des-ventes/". $emplacementNomFichier;
        $fichier = new Xlsx($spreadsheet);
        $fichier->save($emplacement); //sauvegarder excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $content = file_get_contents($emplacement); //on obtient le fichier déjà sauvegardé
        header("Content-Disposition: attachment; filename=journal-des-ventes-" . $time .".xlsx"); //en telechargement
        exit($content); //telecharger au bon format et quitter

        return new Response();
    }

    public function commandsArrayInsertion($orders, $sheet, $countRows, $em, $type, $period, $time){ //fonction appelé dans downloadCommandesSpecific() et downloadCommandes()
        foreach($orders as $order){
            if($time && $period){ //si un temps est affecté (année ou mois)
                $condition = $order->getState() !== 0 && date_format($order->getCreateAt(),$period) == $time;
            } else { //sinon, afficher tout
                $condition = $order->getState();
            }

            if($condition){
                if(!empty($saveMonth)){ //au début c'est vide
                    if($saveMonth !== date_format($order->getCreateAt(), 'm/Y')){
                        $sheet->setCellValue('A'. $countRows, '----- ' . date_format($order->getCreateAt(), 'm/Y') . ' -----');
                        $countRows++;
                    }
                }
                // dd(date_format($order->getCreateAt(),"m"));
                $proAssoc = $em->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($order->getUser());

                if($type == "commandes"){
                    $prix_total = $order->getTotalFinal();
                    $refundAmount = $order->getRefundAmount();
                } elseif ($type == "reservations"){
                    $prix_total = $order->getTotal();
                    $refundAmount = $order->getRefundAmountReservation();
                } else {
                    $prix_total = $order->getTotalFinal();
                }

                $sheet->setCellValue('A'. $countRows, date_format($order->getCreateAt(), 'd/m/Y'));
                if($proAssoc){
                    $sheet->setCellValue('B'. $countRows, $proAssoc->getRaisonSocial());
                } else {
                    $sheet->setCellValue('B'. $countRows, "");
                }
                $sheet->setCellValue('C'. $countRows, $order->getUser()->getLastname());
                $sheet->setCellValue('D'. $countRows, $order->getUser()->getFirstname());
                $sheet->setCellValue('E'. $countRows, $order->getReference());
                $sheet->setCellValue('F'. $countRows, (number_format(($prix_total / 100) / 1.2, 2))); //HT
                $sheet->setCellValue('G'. $countRows, (number_format((($prix_total) - ($prix_total / 1.2)) / 100, 2))); // TVA
                $sheet->setCellValue('H'. $countRows, $prix_total / 100); //TTC
                $sheet->setCellValue('I'. $countRows, $refundAmount / 100); //remboursé
                if($order->getRefundedAt() !== null){
                    $sheet->setCellValue('J'. $countRows, date_format($order->getRefundedAt(), 'd/m/Y'));
                } else {
                    $sheet->setCellValue('J'. $countRows, "");
                }
                $saveMonth = date_format($order->getCreateAt(), 'm/Y'); //sauvegarde de la date en cours
                $countRows++;
            }
        }
        return $sheet;
    }

    #[Route('/admin/library/site-newsletter/', name: 'app_library_dl_newsletter')]
    public function downloadEmailNewsletter(EntityManagerInterface $em) : Response{

        $newsletter = $em->getRepository(Newsletter::class)->findAll();
        $countRows = 2;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //HEADER PREMIERE LIGNE
        $sheet->setCellValue('A1', 'Adresse mail');
        $sheet->setCellValue('B1', 'Abonné');
        //LES DONNEES
        foreach($newsletter as $new){
            
            $sheet->setCellValue('A'. $countRows, $new->getEmail());
            $sheet->setCellValue('B'. $countRows, $new->isAbonne());
            $countRows++;
        
        }
        if(!is_dir("./../compte/liste/newsletter/")){
            mkdir("./../compte/liste/newsletter/");
        }
        $emplacementNomFichier = "site-newsletter.xlsx";
        
        $emplacement = "./../compte/liste/newsletter/". $emplacementNomFichier;
        $fichier = new Xlsx($spreadsheet);
        $fichier->save($emplacement); //sauvegarder excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $content = file_get_contents($emplacement); //on obtient le fichier déjà sauvegardé
        header("Content-Disposition: attachment; filename=" . $emplacementNomFichier); //en telechargement
        exit($content); //telecharger au bon format et quitter

        return new Response();
    }

    public function envoiEmailNewsletter(EntityManagerInterface $em){
        $this->downloadEmailNewsletter($em); //génerer la liste en XLSX
        $mailEnvoi = new Mail();
        $subject = 'La liste mails newsletter du site ARSENAL PRO';
        $content = "<section style='font-family: arial;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='font-weight: normal;'>La liste de NEWSLETTER d'ARSENAL PRO</h2>
        <div>
            <h2 style='font-weight: normal;'>Bonjour, voici le relevé mensuel des mails abonnés à la newsletter en excel pour le site ARSENAL PRO</h2><br><br>
        </div>
        </div>
        </section></section>";
        $getFichier = '/var/www/arsenal/compte/liste/newsletter/site-newsletter.xlsx'; //emplacement fichier du serveur UNIX
        $mailEnvoi->sendExcel('arsenalpro74@gmail.com', "Arsenal Pro", $subject, $getFichier, 'site-newsletter.xlsx', $content); //envoi de mail
        $mailEnvoi->sendExcel('armurerie@arsenal-pro.com', "Arsenal Pro", $subject, $getFichier, 'site-newsletter.xlsx', $content); //envoi de mail
        return new Response();
    }

    #[Route('/admin/colissimo-generator/', name: 'app_colissimo_generator')]
    public function makeCustomTicketColissimo(Request $req){ //pour générer un ticket de livraison vers le service colissimo
        $masse = 1;
        $insuranceValue = 0;
        $form = $this->createForm(ColissimoGeneratorType::class);
        $form->handleRequest($req);
        
        if($form->isSubmitted() && $form->isValid()){

            if($form->get('masse')->getData() !== null){
                $masse = $form->get('masse')->getData();
            }
    
            if($form->get('insuranceValue')->getData() !== null){
                $insuranceValue = intval($form->get('insuranceValue')->getData());
            }
            if($form->get('utilisateur')->getData() !== null){
                // $rey = $this->entityManager->getRepository(User::class)->findOneById($id);
                $rey = $form->get('utilisateur')->getData();
                $acheteur = $rey->getAdresses()[0];
                $httpClient = HttpClient::create();
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
                            "orderNumber" => "S-" . date("Y-m-d") . "-" . $rey->getFirstName() . "-" . substr(uniqid(),4,4), 
                            "commercialName" => "ARSENAL PRO" 
                        ], 
                        "parcel" => [
                                "weight" => $masse, //kg
                                "insuranceValue" => $insuranceValue,
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
                                            "phoneNumber" => $acheteur->getPhone(), 
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
                if(!is_dir("./../colissimo/". date("d-m-Y"). "")){ //si dossier date non existante
                    mkdir("./../colissimo/". date("d-m-Y"). ""); //création dossier date pour factures/
                }
                header("Content-type: application/octet-stream"); //conversion de la réponse en application/octet-stream
                header("Content-Type: application/pdf"); //puis conversion forcé en pdf
                $emplac_fichier = "/var/www/arsenal/colissimo/". date("d-m-Y"). "/colissimo-" . date("d-m-Y") .  "-"  . $rey->getFirstName() . "-" . substr(uniqid(),4,4) . ".pdf"; //emplacement fichier dans le serveur UNIX
                $nomfichier = "colissimo-" . date("d-m-Y") .  "-"  . $rey->getFirstName() .  "-" . substr(uniqid(),4,4) . ".pdf";
                file_put_contents("./../colissimo/". date("d-m-Y"). "/colissimo-" . date("d-m-Y") .  "-"  . $rey->getFirstName() .  "-" . substr(uniqid(),4,4) . ".pdf", $response->getContent()); //nouveau pdf généré
                
                $mail = new Mail();
                $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                    <div>
                    <h2 style='text-align: center; font-weight: normal;'>NOUVEAU! <b>LABEL COLISSIMO</b> effectuée chez Arsenal Pro !</h2>
                    <h3 style='font-weight: normal;'>Ce colissimo est destiné pour le client ". $rey->getFirstname() ." ". $rey->getLastname() ." !</h3>
                    </div>
                    </section></section>";
                    
                $mail->sendAvecFichierPDF("armurerie@arsenal-pro.com", "ARSENAL PRO", "Label colissimo pour " . $rey->getFullname() ."", $content, $emplac_fichier, $nomfichier, 4639500);
                $mail->sendAvecFichierPDF("arsenalpro74@gmail.com", "ARSENAL PRO", "Label colissimo pour " . $rey->getFullname() ."", $content, $emplac_fichier, $nomfichier, 4639500);
                $this->addFlash('notice', "<span style='color:green;'><strong>Le nouveau ticket Colissimo pour ". $rey->getFullname() ." a été généré !</strong></span>");
                return $this->redirectToRoute('app_library');

            } else {
                $this->addFlash('notice', "<span style='color:red;'><strong>Erreur : Client introuvable</strong></span>");
            }
            return $this->redirectToRoute("app_library");

        }
        return $this->render('library/colissimo_generator.html.twig',[
            'form' => $form->createview(),
        ]);    
    } 

    #[Route('/admin/download-documents-clients/', name: 'app_document_client_dl')]
    public function documentsClientDownload(){ 
        $absoluteLink = "./../compte/utilisateurs/document-client.tar.gz";
        if(is_dir("./../compte/utilisateurs/")){
            header('Content-Type: application/gzip');
            $content = file_get_contents($absoluteLink); //on obtient le fichier déjà sauvegardé
            header("Content-Disposition: attachment; filename=document-client.tar.gz"); //en telechargement
            exit($content); //telecharger au bon format et quitter
        } else {
            $this->addFlash('notice', "<span style='color:red;'><strong>Erreur : Fichier document-client.tar.gz introuvable</strong></span>");
        }
    }

    #[Route('/admin/library/documents-clients', name: 'app_library_documents_clients')]
    public function consulterDocumentsClient(Request $req): Response
    {
        // dd($req);
        $titre = "Les documents clients";
        $erreur = '';
        $docCount = glob("./../public/uploads/documents/*"); //compte combien de factures sont dans le dossier
        // dd($facturecount);
        $nomFile = [];
        if(!isset($_POST['submit'])){
            foreach($docCount as $i){
                $nomFile[] = ["src" => substr($i, strrpos($i, '/') + 1)];
            }
        } else { //si submit
            $validation = false;
            $detecte = $_POST['searchDocs'];
            $trouveScan = glob("./../public/uploads/documents/*");
            foreach ($trouveScan as $trouve){ //boucle scan
                // dd($detecte, $trouve);
                if(preg_match("/\b" . $detecte . "\b/iu", $trouve)){ //si une partie de la recherche match 
                    $nomFile[] = ["src" => substr($trouve, strrpos($trouve, '/') + 1)];
                    $validation = true;
                } 
            }
            if(!$validation){
                $erreur = 'Aucun client trouvé dans la recherche.';
            }
        }

        return $this->render('library/documents-clients.html.twig', [
            'src' => $nomFile,
            'titre' => $titre,
            "type" => "documents-clients",
            "erreur" => $erreur
        ]);
    }

    #[Route('/admin/library/documents-clients/{src}', name: 'app_library_documents_clients_one')]
    public function consulterDocumentsClientOnly(Request $req, $src): Response
    {
        $client = $this->entityManager->getRepository(User::class)->findOneById(explode("-", $src)[0]);
        if($client){
            $documentsBDD = $this->entityManager->getRepository(ComptesDocuments::class)->findOneByUser($client);
            $soustitre = "";
            $titre = "Les documents de " . $client->getFullname();
            if($documentsBDD){
                if($documentsBDD->getDateEnvoi() !== null){
                    $soustitre = "<br/>Dernière mise en ligne le " . $documentsBDD->getDateEnvoi()->format('d/m/Y');
                } 
            }
            $docs = [];
            $docCount = glob("./../public/uploads/documents/" . $src . "/*"); //compte combien de factures sont dans le dossier
            foreach($docCount as $unDoc){
                $docs[] = ["src" => substr($unDoc, strrpos($unDoc, '/') + 1)]; //pour prendre le dernier slash ou sinon erreur car convertion d'un lien en string
            }
            return $this->render('library/documents_clients_one.html.twig', [
                'parent' => $src,
                'docs' => $docs,
                'titre' => $titre,
                'sousTitre' => $soustitre,
                "type" => "documents-clients",
            ]);
        } else {
            die('Ce client est introuvable dans la base de donnée');
        }
    }

    #[Route('/admin/library/documents-clients/{src}/{fichier}', name: 'app_library_documents_clients_one_dl')]
    public function downloadDocumentsClients($src, $fichier) : Response{

        $link = "./../public/uploads/documents/" . $src . "/" . $fichier; 
        header("Content-Type: application/image/*"); 
        $document = file_get_contents($link); 
        echo $document;
        return new Response();
    }

}
