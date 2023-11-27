<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ComptesDocuments;
use App\Entity\ProfessionnelAssociationCompte;
use App\Entity\User;
use App\Form\CompteDocumentsType;
use App\Form\CompteDocumentsVerificationType;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Security;


class CompteDocumentsController extends AbstractController
{
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/documents', name: 'app_account_documents')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $datenow = date('d-m-Y');
        $URL = "https://arsenal-pro.fr";
        $verifdate = true;
        $notif = '';
        $notifverif = '';
        $notifnow = '';
        $notifverifarray = [];
        $notifcolor = '';
        $lesdates = [];
        $lesDocumentsDispo = [];
        $erreurCNI = false;
        $erreurLicenceTir = false;
        $erreurCertificatMedical = false;

        $comptedocuments = new ComptesDocuments();
        $form = $this->createForm(CompteDocumentsType::class, $comptedocuments);
        $uniquedocs = $this->entityManager->createQuery(
            'SELECT d
            FROM App\Entity\ComptesDocuments d
            WHERE d.user = :user ORDER BY d.id DESC'
        )->setParameter('user', $this->getUser())->getResult();
        $checkSiPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneBy(['user' => $this->getUser()]);

        // dd($checkSiPolice);
        if($uniquedocs){ //verif si le client a déjà déposé ou non des documents 
            $mesdocsunique = $uniquedocs[0];
            $notifverifarray = array(['nom' => 'CNI', 'entity' => $mesdocsunique->getCartId(), 'check' => $mesdocsunique->getCartIdcheck()], ['nom' => 'Licence de tir', 'entity' => $mesdocsunique->getLicenceTirId(), 'check' => $mesdocsunique->getLicenceTirIdcheck()],['nom' => 'Certificat médical', 'entity' => $mesdocsunique->getCertificatMedicalId(), 'check' => $mesdocsunique->getCertificatMedicalIdcheck()]);

            //base64_encode lien documents pour sécurité
            $lesDocumentsDispo[] = ['cni' => 'data: '.mime_content_type('./../public/uploads/documents/' . $mesdocsunique->getCartId()).';base64,'. (base64_encode(file_get_contents('./../public/uploads/documents/' . $mesdocsunique->getCartId())))]; //obligatoire
            if($checkSiPolice){ //si police
                if($checkSiPolice->getTypeFDO() !== null && $mesdocsunique->getCartPoliceId() !== null){
                    $notifverifarray = array(['nom' => 'CNI', 'entity' => $mesdocsunique->getCartId(), 'check' => $mesdocsunique->getCartIdcheck()], ['nom' => 'Licence de tir', 'entity' => $mesdocsunique->getLicenceTirId(), 'check' => $mesdocsunique->getLicenceTirIdcheck()],['nom' => 'Certificat médical', 'entity' => $mesdocsunique->getCertificatMedicalId(), 'check' => $mesdocsunique->getCertificatMedicalIdcheck()], ['nom' => 'Carte de police', 'entity' => $mesdocsunique->getCartPoliceId(), 'check' => $mesdocsunique->isCartPoliceIdcheck()]);
                    $lesDocumentsDispo[] = ['cp' => 'data: '.mime_content_type('./../public/uploads/documents/' . $mesdocsunique->getCartPoliceId()).';base64,'. (base64_encode(file_get_contents('./../public/uploads/documents/' . $mesdocsunique->getCartPoliceId())))];
                    
                }
            }

            //base64_encode lien documents pour sécurité
            if($mesdocsunique->getLicenceTirId() !== null){
                $lesDocumentsDispo[] = ['licenceTir' => 'data: '.mime_content_type('./../public/uploads/documents/' . $mesdocsunique->getLicenceTirId()).';base64,'. (base64_encode(file_get_contents('./../public/uploads/documents/' . $mesdocsunique->getLicenceTirId())))];
            }
            if($mesdocsunique->getCertificatMedicalId() !== null){
                $lesDocumentsDispo[] = ['cm' => 'data: '.mime_content_type('./../public/uploads/documents/' . $mesdocsunique->getCertificatMedicalId()).';base64,'. (base64_encode(file_get_contents('./../public/uploads/documents/' . $mesdocsunique->getCertificatMedicalId())))];
            }

            // var_dump($lesDocumentsDispo);
            if(!$mesdocsunique->getVosdocumentsverifies()){
                $notifnow = 'Vos documents sont en cours de vérification, veuillez patienter...';
            } else {
                $notifnow = "Vos documents ont été vérifiés ! Veuillez regarder <a href='#notifverifMessage'>ci-dessous de la page</a> pour voir quels documents sont valides."; 
                    for($i = 0; $i < 3; $i++){
                        if($notifverifarray[$i]['entity'] !== null){
                            if($notifverifarray[$i]['check']){
                                $notifverif .= "<li style='color: green; list-style: none;'>" . $notifverifarray[$i]['nom'] ." est valable </li>";
                            } else {
                                $notifverif .= "<li style='color: red; list-style: none;'>" . $notifverifarray[$i]['nom'] ." n'est plus valable, veuillez mettre le document à jour</li>";
                            }
                        }
                        //à faire verif police message
                }
            }
        } else {
            $mesdocsunique = null;
            $lesDocumentsDispo = null;
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()){ //si ajout ou modification $form->isValid() uniquement si on a rempli tout le formulaire
            // dd("ae");
            $documentsExistant = $this->entityManager->getRepository(ComptesDocuments::class)->findBy(['user' => $this->getUser()->getId()]);

            if(count($documentsExistant) > 0){ //on regarde dans la liste s'il y e déjà des documents en cours ou non
                foreach($documentsExistant as $etabli){ 
                        $this->entityManager->remove($etabli);
                        $this->entityManager->flush(); //on efface pour pas prendre de l'espace | on ne peut pas faire un update car on veut que la ligne soit en haut lors d'un insert
                }
            }

            $cardiddate_convert = $form->get('cartIdDate')->getData()->format('d-m-Y');
            $lesdates[] = ['nom'=> 'CNI','date' => $cardiddate_convert];
            if($form->get('licenceTirId')->getData() !== null && $form->get('licenceTirIdDate')->getData() !== null){
                $licencetirdate_convert = $form->get('licenceTirIdDate')->getData()->format('d-m-Y');
                $lesdates[] = ['nom' => 'licence de tir', 'date'=> $licencetirdate_convert];
            } else {
                $comptedocuments->setLicenceTirIdcheck(false); //direct en false car ça n'existe pas
                $comptedocuments->setLicenceTirIdDate(null); 
            }
            if($form->get('certificatMedicalId')->getData() !== null && $form->get('certificatMedicalIdDate')->getData() !== null){
                $certmedicaldate_convert = $form->get('certificatMedicalIdDate')->getData()->format('d-m-Y');
                $lesdates[] = ['nom' => 'certificat médical','date' => $certmedicaldate_convert];
            } else {
                $comptedocuments->setCertificatMedicalIdcheck(false); 
                $comptedocuments->setCertificatMedicalIdDate(null); //direct en false car ça n'existe pas dans le form
            }
            
            $dateErreurString = '';
            foreach($lesdates as $unedate){ //checking date
                if($verifdate){
                    if(strtotime($unedate['date']) > strtotime($datenow)){
                        $verifdate = true;
                    } else {
                        $verifdate = false;
                        $dateErreurString = $unedate['nom']; //pour message d'erreur de date
                    }
                } else {
                    $verifdate = false; //stop la condition de comparaison avec ceci
                }
            }
            //UPLOAD IMAGES
            if($verifdate){
                $dossier = "uploads/documents/" . $this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname();
                if(!is_dir($dossier)) { //si dossier client pas existant
                    mkdir($dossier); //créer dossier client | documents/ en perm 777
                }
                $cardifile = $form->get('cartId')->getData();
                $licenceTirFile = $form->get('licenceTirId')->getData();
                $certificatMedicalFile = $form->get('certificatMedicalId')->getData();
                $cartpolice = $form->get('cartPoliceId')->getData();
                $numeroSIA = $form->get('numero_sea')->getData();
                $justificatifDocuments = $form->get('justificatifDomicile')->getData();
                $noNumeroSIA = $request->request->get('noNumeroSIA'); //case à cocher pour le numero SIA

                $comptedocuments->setUser($this->getUser()); //Recup l'utilisateur

                $files = array(['nom' => 'CNI', 'file'=> $cardifile], ['nom' => 'Licence-de-tir', 'file'=> $licenceTirFile],['nom' => 'Certificat-medical', 'file'=> $certificatMedicalFile], ['nom' => 'Justificat-domicile', 'file'=> $justificatifDocuments], ['nom' => 'Carte-police', 'file' => $cartpolice]);
                $mailDocuments = "";
                foreach ($files as $file){
                    if($file['file']){
                        // dd($files);
                        $file_name = pathinfo("Client-N" . $comptedocuments->getUser()->getId() . "-document-" . $file['nom'] . "-de-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname(), PATHINFO_FILENAME);
                        $newFilename = $this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() ."/".$file_name . '-'. $datenow .'.'.$file['file']->guessExtension();
                        try {
                            $file['file']->move(
                                $this->getParameter('documents_client_directory').$dossier, //public/uploads/documents/userdossier/
                                $newFilename
                            );
                        } catch (FileException $e) {
                            //
                        }
                        if($file['nom'] == "CNI"){ //si file['nom'] match une des conditions alors
                            $comptedocuments->setCartId($newFilename);
                            $comptedocuments->setCartIdcheck(false);
                            $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML

                        }
                        if($file['nom'] == "Licence-de-tir"){
                            if($comptedocuments->getLicenceTirIddate() !== null){
                                $comptedocuments->setLicenceTirId($newFilename);
                                $comptedocuments->setLicenceTirIdcheck(false);
                                $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML

                            } else {
                                $comptedocuments->setLicenceTirId(null); //pour éviter erreur car de base l'insertion d'image prend l'image du formulaire en format .tmp
                            }
                        }
                        if($file['nom'] == "Certificat-medical"){
                            if($comptedocuments->getCertificatMedicalIdDate() !== null){
                                $comptedocuments->setCertificatMedicalId($newFilename);
                                $comptedocuments->setCertificatMedicalIdcheck(false);
                                $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                            } else {
                                $comptedocuments->setCertificatMedicalId(null); //pour éviter erreur car de base l'insertion d'image prend l'image du formulaire en format .tmp
                            }
                        }
                        if($file['nom'] == "Justificat-domicile"){
                            $comptedocuments->setJustificatifDomicile($newFilename);
                            $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                        }
                        if($file['nom'] == "Carte-police"){
                            $comptedocuments->setCartPoliceId($newFilename);
                            $comptedocuments->setCartIdcheck(false);
                            $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                        }
                    }
                }
                //MAJ BDD
                if(empty($numeroSIA) && isset($noNumeroSIA) ){
                    $comptedocuments->setNumeroSea("Pas de numéro SIA");
                } else {
                    $comptedocuments->setNumeroSea($numeroSIA);
                }
                $comptedocuments->setNumeroSeaCheck(false);
                $comptedocuments->setVosdocumentsverifies(false);
                $comptedocuments->setDateEnvoi(new DateTimeImmutable('now +1 hours')); //date d'envoi, les documents sont conservés pendant un an
                $this->entityManager->persist($comptedocuments);
                $this->entityManager->flush();

                //envoie de mél à nous même, admin
                $mail = new Mail();
                $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                <div>
                <h2 style='font-weight: normal;'>Des documents au nom de <strong>".$comptedocuments->getUser()->getFullName()."</strong> ont été déposés</h2>
                <h3 style='font-weight: normal;'>Faites une vérification maintenant <a href='https://arsenal-pro.fr/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CComptesDocumentsCrudController&menuIndex=7&submenuIndex=-1&query=" . $comptedocuments->getUser()->getLastname() ."'>ici</a></h3>
                </div>
                <br/>
                <div>
                    <div style='display: flex; justify-content: space-around;'>
                        <div>
                            <p style='font-weight: normal;'>ID Utilisateur : " . $comptedocuments->getUser()->getId() . "</p>
                            <p style='font-weight: normal;'>" . $comptedocuments->getUser()->getFullName() . "</p>
                            <p style='font-weight: normal;'>" . $comptedocuments->getUser()->getEmail() . "</p>
                            <p style='font-weight: normal;'>" . $comptedocuments->getUser()->getAdresses()[0]->getPhone(). "</p>
                            <p style='font-weight: normal;'>" . $comptedocuments->getUser()->getAdresses()[0]->getAdress(). ", " . $comptedocuments->getUser()->getAdresses()[0]->getCity() . ", " . $comptedocuments->getUser()->getAdresses()[0]->getPostal() . ", " . $comptedocuments->getUser()->getAdresses()[0]->getCountry() . "</p>
                        </div>
                        <div>" . $mailDocuments . "<div><p>Numéro SIA : " . $comptedocuments->getNumeroSea(). "</p></div></div>
                    </div>
                </div>
                </section></section>";

                $mail->send("armurerie@arsenal-pro.com", "ARSENAL PRO", "DOCUMENTS A VERIFIER", $content, 4639500);
                $mail->send("arsenalpro74@gmail.com", "ARSENAL PRO", "DOCUMENTS A VERIFIER", $content, 4639500);

                $notif = "Tous vos documents sont enregistrés et sont en cours de validation !";
                $notifcolor = "green";
                $notifverif = ''; //override si ça existe déjà

                } else { //dates pas OK si admin a vérifié 
                    //si erreurs dates, on utilise un code customisé qui est à part du Symfony:Forms, il faut qu'on gère nous même les erreurs de styles (rouge en CSS).
                    if($dateErreurString == "CNI"){
                        $erreurCNI = true;
                    }
                    if($dateErreurString == "licence de tir"){
                        $erreurLicenceTir = true;
                    }
                    if($dateErreurString == "certificat médical"){
                        $erreurCertificatMedical = true;
                    }
                    $notif = "Votre ". $dateErreurString ." n'est plus valable. Il a expiré. Veuillez s'il vous plaît mettre le document à jour.";
                    $notifcolor = "red";
                    $notifverif = ''; //override si ça existe déjà
                }
                $this->addFlash('notice', $notif); //permet d'afficher un message de pop-up de notification en haut de la page des documents clients
                return $this->redirectToRoute('app_account_documents'); //rechargement de nouveau vers la même page après avoir mis les documents
        }

        return $this->render('account/compte_document.html.twig',[
            'form' => $form->createview(),
            'notif' => $notif, //pop-up message notification en haut de la page
            'notifverif' => $notifverif,
            'notifnow' => $notifnow,
            'notifcolor' => $notifcolor,
            'mesdocs' => $mesdocsunique,
            'docsDispo' => $lesDocumentsDispo,
            'siPolice' => $checkSiPolice,
            'erreursDepot' => ['cni' => $erreurCNI, 'licenceTir' => $erreurLicenceTir, 'certificatMedical' => $erreurCertificatMedical]
        ]);
    }

    #[Route('/admin/documents-clients', name: 'app_library_account_documents')]
    public function insertDocumentClientsAdmin(Request $request): Response //page admin
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $datenow = date('d-m-Y');
        $URL = "https://arsenal-pro.fr";
        $verifdate = true;
        $notif = '';
        $notifcolor = '';
        $lesdates = [];
        $erreurCNI = false;
        $erreurLicenceTir = false;
        $erreurCertificatMedical = false;
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $listeUser = [];
        foreach($users as $user){
            $checkifPro =  $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($user);
            $special = null;
            if($checkifPro){
                if($checkifPro->getNumeroMatricule()){
                    $special = "FDO";
                } else {
                    $special = "Pro";
                }
            }
            $listeUser[] = ['user' => $user, 'special' => $special];
        }

        $comptedocuments = new ComptesDocuments();
        $form = $this->createForm(CompteDocumentsType::class, $comptedocuments);

        $form->handleRequest($request);

        if ($form->isSubmitted()){ //si ajout ou modification $form->isValid() uniquement si on a rempli tout le formulaire
            $userInput = $this->entityManager->getRepository(User::class)->findOneById(intval($request->get("user")));
            $documentsExistant = $this->entityManager->getRepository(ComptesDocuments::class)->findByUser($userInput);

            if(count($documentsExistant) > 0){ //on regarde dans la liste s'il y e déjà des documents en cours ou non
                foreach($documentsExistant as $etabli){ 
                        $this->entityManager->remove($etabli);
                        $this->entityManager->flush(); //on efface pour pas prendre de l'espace | on ne peut pas faire un update car on veut que la ligne soit en haut lors d'un insert
                }
            }

            $cardiddate_convert = $form->get('cartIdDate')->getData()->format('d-m-Y');
            $lesdates[] = ['nom'=> 'CNI','date' => $cardiddate_convert];
            if($form->get('licenceTirId')->getData() !== null && $form->get('licenceTirIdDate')->getData() !== null){
                $licencetirdate_convert = $form->get('licenceTirIdDate')->getData()->format('d-m-Y');
                $lesdates[] = ['nom' => 'licence de tir', 'date'=> $licencetirdate_convert];
            } else {
                $comptedocuments->setLicenceTirIdcheck(false); //direct en false car ça n'existe pas
                $comptedocuments->setLicenceTirIdDate(null); 
            }
            if($form->get('certificatMedicalId')->getData() !== null && $form->get('certificatMedicalIdDate')->getData() !== null){
                $certmedicaldate_convert = $form->get('certificatMedicalIdDate')->getData()->format('d-m-Y');
                $lesdates[] = ['nom' => 'certificat médical','date' => $certmedicaldate_convert];
            } else {
                $comptedocuments->setCertificatMedicalIdcheck(false); 
                $comptedocuments->setCertificatMedicalIdDate(null); //direct en false car ça n'existe pas dans le form
            }
            
            $dateErreurString = '';
            foreach($lesdates as $unedate){ //checking date
                if($verifdate){
                    if(strtotime($unedate['date']) > strtotime($datenow)){
                        $verifdate = true;
                    } else {
                        $verifdate = false;
                        $dateErreurString = $unedate['nom']; //pour message d'erreur de date
                    }
                } else {
                    $verifdate = false; //stop la condition de comparaison avec ceci
                }
            }
            //UPLOAD IMAGES
            if($verifdate){
                $dossier = "uploads/documents/" . $userInput->getId() . "-" . $userInput->getFirstname() . "-" . $userInput->getLastname();
                if(!is_dir($dossier)) { //si dossier client pas existant
                    mkdir($dossier); //créer dossier client | documents/ en perm 777
                }
                $cardifile = $form->get('cartId')->getData();
                $licenceTirFile = $form->get('licenceTirId')->getData();
                $certificatMedicalFile = $form->get('certificatMedicalId')->getData();
                $cartpolice = $form->get('cartPoliceId')->getData();
                $numeroSIA = $form->get('numero_sea')->getData();
                $justificatifDocuments = $form->get('justificatifDomicile')->getData();
                $noNumeroSIA = $request->request->get('noNumeroSIA'); //case à cocher pour le numero SIA

                $comptedocuments->setUser($userInput); //Recup l'utilisateur

                $files = array(['nom' => 'CNI', 'file'=> $cardifile], ['nom' => 'Licence-de-tir', 'file'=> $licenceTirFile],['nom' => 'Certificat-medical', 'file'=> $certificatMedicalFile], ['nom' => 'Justificat-domicile', 'file'=> $justificatifDocuments], ['nom' => 'Carte-police', 'file' => $cartpolice]);
                // $mailDocuments = "";
                foreach ($files as $file){
                    if($file['file']){
                        // dd($files);
                        $file_name = pathinfo("Client-N" . $userInput->getId() . "-document-" . $file['nom'] . "-de-" . $userInput->getFirstname() . "-" . $userInput->getLastname(), PATHINFO_FILENAME);
                        $newFilename = $userInput->getId() . "-" . $userInput->getFirstname() . "-" . $userInput->getLastname() ."/".$file_name . '-'. $datenow .'.'.$file['file']->guessExtension();
                        try {
                            $file['file']->move(
                                $this->getParameter('documents_client_directory').$dossier, //public/uploads/documents/userdossier/
                                $newFilename
                            );
                        } catch (FileException $e) {
                            //
                        }
                        if($file['nom'] == "CNI"){ //si file['nom'] match une des conditions alors
                            $comptedocuments->setCartId($newFilename);
                            $comptedocuments->setCartIdcheck(true);
                            // $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML

                        }
                        if($file['nom'] == "Licence-de-tir"){
                            if($comptedocuments->getLicenceTirIddate() !== null){
                                $comptedocuments->setLicenceTirId($newFilename);
                                $comptedocuments->setLicenceTirIdcheck(true);
                                // $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML

                            } else {
                                $comptedocuments->setLicenceTirId(null); //pour éviter erreur car de base l'insertion d'image prend l'image du formulaire en format .tmp
                            }
                        }
                        if($file['nom'] == "Certificat-medical"){
                            if($comptedocuments->getCertificatMedicalIdDate() !== null){
                                $comptedocuments->setCertificatMedicalId($newFilename);
                                $comptedocuments->setCertificatMedicalIdcheck(true);
                                // $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                            } else {
                                $comptedocuments->setCertificatMedicalId(null); //pour éviter erreur car de base l'insertion d'image prend l'image du formulaire en format .tmp
                            }
                        }
                        if($file['nom'] == "Justificat-domicile"){
                            $comptedocuments->setJustificatifDomicile($newFilename);
                            // $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                        }
                        if($file['nom'] == "Carte-police"){
                            $comptedocuments->setCartPoliceId($newFilename);
                            $comptedocuments->setCartIdcheck(false);
                            // $mailDocuments .= "<div style='display: flex; flex-direction: row;'><p style='width: 100px; max-width: 100px;'>" . $file['nom'] ."</p><img src='" . $URL ."/uploads/documents/" . $newFilename ."' width='100' height='80' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                        }
                    }
                }
                //MAJ BDD
                if(empty($numeroSIA) && isset($noNumeroSIA) ){
                    $comptedocuments->setNumeroSea("Pas de numéro SIA");
                } else {
                    $comptedocuments->setNumeroSea($numeroSIA);
                }
                $comptedocuments->setNumeroSeaCheck(false);
                $comptedocuments->setVosdocumentsverifies(true);
                $comptedocuments->setDateEnvoi(new DateTimeImmutable('now +1 hours')); //date d'envoi, les documents sont conservés pendant un an
                $this->entityManager->persist($comptedocuments);
                $this->entityManager->flush();

                $notif = "Tous les documents de ". $userInput->getFullname() ." sont enregistrés !";
                $notifcolor = "green";
                // unset($form);

                } else { //dates pas OK
                    //si erreurs dates, on utilise un code customisé qui est à part du Symfony:Forms, il faut qu'on gère nous même les erreurs de styles (rouge en CSS).
                    if($dateErreurString == "CNI"){
                        $erreurCNI = true;
                    }
                    if($dateErreurString == "licence de tir"){
                        $erreurLicenceTir = true;
                    }
                    if($dateErreurString == "certificat médical"){
                        $erreurCertificatMedical = true;
                    }
                    $notif = "Votre ". $dateErreurString ." n'est plus valable. Il a expiré. Veuillez s'il vous plaît mettre le document à jour.";
                    $notifcolor = "red";
                }
        }

        return $this->render('library/compte_document.html.twig',[
            'user' => $listeUser,
            'form' => $form->createview(),
            'notif' => $notif,
            'notifcolor' => $notifcolor,
            'erreursDepot' => ['cni' => $erreurCNI, 'licenceTir' => $erreurLicenceTir, 'certificatMedical' => $erreurCertificatMedical]
        ]);
    }

    public function deleteDocumentsCompte($user){
        $documents = $this->entityManager->getRepository(ComptesDocuments::class)->findOneById($user);
        $type = "CNI";
        // dd($documents->getUser()->getId(), $documents->getUser()->getFirstname(), $documents->getUser()->getLastname());
        if($documents){
            $dossier = "./../public/uploads/documents/" . $documents->getUser()->getId() . "-" . $documents->getUser()->getFirstname() . "-" . $documents->getUser()->getLastname();
            if(is_dir($dossier)){
                $fichiers = glob($dossier . "/*"); //fichiers dossier
                foreach($fichiers as $fichier){
                    if(!preg_match("/\b" . $type . "\b/iu", $fichier)){
                        unlink($fichier);
                    }
                }
                $this->entityManager->remove($documents);

                $this->entityManager->flush();
            }
  
        }
        return new Response();
    }

    #[Route('/admin/documents-clients/verifier/{userId}', name: 'app_library_account_documents_check')]
    public function validerDocumentsCompte(Request $req, $userId){
        $documents = $this->entityManager->getRepository(ComptesDocuments::class)->findOneById($userId);
        $user = $this->entityManager->getRepository(User::class)->findOneById($documents->getUser()->getId());
        $checkifPro =  $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($documents->getUser());
        $form = $this->createForm(CompteDocumentsVerificationType::class);
        $mail = new Mail();
        $dateNow = new DateTimeImmutable('now +1 hours');
        $documentsVerifies = "";
        $descriptionClient = "";
        $URL = "arsenal-pro.fr";
        $form->handleRequest($req);

        if($documents){
            if ($form->isSubmitted()){ //si ajout ou modification $form->isValid() uniquement si on a rempli tout le formulaire
                $documents->setVosdocumentsverifies(true);
                $documents->setCartIdCheck($form->get('cartIdcheck')->getData());

                if($form->get('licenceTirIdcheck')->getData() !== null){
                    $documents->setLicenceTirIdCheck($form->get('licenceTirIdcheck')->getData());
                }
                if($form->get('certificatMedicalIdcheck')->getData() !== null){
                    $documents->setCertificatMedicalIdCheck($form->get('certificatMedicalIdcheck')->getData());
                }
                if($form->get('cartPoliceIdcheck')->getData() !== null){
                    $documents->setCartPoliceIdcheck($form->get('cartPoliceIdcheck')->getData());
                }
                if($form->get('numero_sia_check')->getData() !== null){
                    $documents->setNumeroSeaCheck($form->get('numero_sia_check')->getData());
                }

                $listeVerifie = [
                    ["nom" => "CNI", "verif" => $documents->getCartIdCheck(), "doc" => $documents->getCartId()],
                    ["nom" => "Licence de tir", "verif" => $documents->getLicenceTirIdCheck(), "doc" => $documents->getLicenceTirId()], 
                    ["nom" => "Certificat Médical", "verif" => $documents->getCertificatMedicalIdCheck(), "doc" => $documents->getCertificatMedicalId()],
                    ["nom" => "Carte police", "verif" => $documents->isCartPoliceIdcheck(), "doc" => $documents->getCartPoliceId()],
                ];

                foreach($listeVerifie as $verification){
                    if($verification["doc"] !== null){ //si document en null alors on ne le prend pas en compte
                        if($verification["verif"]){ //si vérifié alors
                            $documentsVerifies .= "<li>" . $verification["nom"] ." : <strong style='color : green;'>VALABLE</strong></li>";
                        } else { //si non vérifié alors
                            $documentsVerifies .= "<li>" . $verification["nom"] ." : <strong style='color : red;'>NON VALABLE</strong></li>";
                        }
                    }
                }
                if($documents->getNumeroSea() !== "Pas de numéro SIA"){ //toujours cette égalité si l'utilisateur n'a pas de numéro SIA
                    if(!$documents->isNumeroSeaCheck()){
                        $documentsVerifies .= "<li>Numéro SIA : <strong style='color : red;'>NON VALABLE</strong></li>";
                    } else {
                        $documentsVerifies .= "<li>Numéro SIA : <strong style='color : green;'>VALABLE</strong></li>";
                    }
                }
                if(!empty($form->get('description')->getData()) && $form->get('description')->getData() !== null){
                    $descriptionClient = "<div style='border: solid 1px grey; padding: 2px;'><h4>Commentaire de l'armurier :</h4><p style='text-align: justify;'>" . $form->get('description')->getData() . "</p></div><br/>"; 
                }
                $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                <div>
                    <h2 style='font-weight: normal;'>Vos documents ont été vérifiés, <strong>".$documents->getUser()->getFirstName()."</strong> !</h2>
                    <h3 style='font-weight: normal;'>Vérification fait le : " . $dateNow->format('d/m/Y') ."</h3>
                </div>
                <br/>
                <div>
                    <div style='display: flex; justify-content: space-around;'>
                        <div>
                            <p style='font-weight: normal;'>ID Utilisateur : " . $documents->getUser()->getId() . "</p>
                            <p style='font-weight: normal;'>" . $documents->getUser()->getFullName() . "</p>
                            <p style='font-weight: normal;'>" . $documents->getUser()->getEmail() . "</p>
                            <p style='font-weight: normal;'>" . $documents->getUser()->getAdresses()[0]->getPhone(). "</p>
                            <p style='font-weight: normal;'>" . $documents->getUser()->getAdresses()[0]->getAdress(). ", " . $documents->getUser()->getAdresses()[0]->getCity() . ", " . $documents->getUser()->getAdresses()[0]->getPostal() . ", " . $documents->getUser()->getAdresses()[0]->getCountry() . "</p>
                        </div>
                        <div>". $documentsVerifies ."</div>
                    </div>
                </div>
                " . $descriptionClient . "
                <div>
                    <h3 style='font-weight: normal;'>Pour vérifier ou modifier certains documents non valables, veuillez cliquer sur le bouton ci-dessous</h3>
                    <div style='text-align: center; background-color: #07af15; width: auto; padding: 10px; margin: auto; width: 200px; color: white;'><a href='". $URL ."/compte/documents/' style='font-weight: bold; color: white;'>Consulter vos documents</a></div>
                </div>
                </section></section>";
                $this->entityManager->flush(); //MAJ BDD

                $mail->send($documents->getUser()->getEmail(), "ARSENAL PRO", "Vos documents vérifiés ARSENAL PRO", $content, 4639822);

                $this->addFlash('notice', "<span style='color:green;'><strong>Les documents de ". $documents->getUser()->getFullname()." ont été validés.</strong></span>");
                return $this->redirect('/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CComptesDocumentsCrudController');
            }
        } else {
            $this->addFlash('warning', 'Les documents sont indisponibles ou introuvable');
            return $this->redirect('/admin?crudAction=index&crudControllerFqcn=App%5CController%5CAdmin%5CComptesDocumentsCrudController');
        }
        return $this->render('library/compte_document_verif.html.twig',[
            'form' => $form->createview(),
            'documents' => $documents,
            'pro' => $checkifPro,
            'user' => $user,
        ]);    
    }
}
