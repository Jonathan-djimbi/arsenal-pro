<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Command\GestionFichierServerCommand;
use App\Entity\Adress;
use App\Entity\DepotVente;
use App\Form\DepotType;
use App\Form\RachatType;
use App\Service\GestionFichierServerService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    private GestionFichierServerService $gs;

    public function __construct(GestionFichierServerService $gs)
    {
        $this->gs = $gs;
    }

    #[Route('/formations-specialisees', name: 'app_formations_specialisees')]
    public function formationsSpecialisees(): Response
    {
        return $this->render('reservation/formations_specialisees.html.twig', [
            'controller_name' => 'ReservationController',
        ]);
    }

    #[Route('/a-propos', name: 'app_about')]
    public function index(): Response
    {
        return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }

    #[Route('/heritage1', name: 'app_heritage1')]
    public function heritage1(): Response
    {
        return $this->render('about/heritage1.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }

    #[Route('/heritage2', name: 'app_heritage2')]
    public function heritage2(): Response
    {
        return $this->render('about/heritage2.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }

    #[Route('/heritage3', name: 'app_heritage3')]
    public function heritage3(): Response
    {
        return $this->render('about/heritage3.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('about/blog.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }

    #[Route('/depot-vente', name: 'app_depot_vente')]
    public function depoteVente(EntityManagerInterface $em, Request $request): Response
    {
        $URL = 'https://arsenal-pro.fr';
        $datenow = new \DateTimeImmutable('now +1 hours');
        $datechecker = new \DateTimeImmutable('now -18 years');
        $depotVente = new DepotVente();
        $form = $this->createForm(DepotType::class, $depotVente);
        $form->handleRequest($request);
        $adresse = [];
        $erreurs[0] = null; //declarer null ou sinon erreur key à 0
        $messageErreurs = false;
        if($this->getUser()){
            $adresse = $em->getRepository(Adress::class)->findBy(['user' => $this->getUser()]);
            $adresseUser = $adresse[0];
        } else {
            $adresseUser = null;
        }
        //check back-end
        if ($form->isSubmitted() && $form->isValid()) {
            $erreurs = [];
            $mailDocuments = "";
                if($this->getUser()){
                    $depotVente->setUser($this->getUser());
                }
                $depotVente->setType('depot-vente');
                $depotVente->setFaitLe($datenow);
                $mail = new Mail();
                $depotFormData = $form->getData();
                $files = [$form->get('photoUn')->getData(),$form->get('photoDeux')->getData(),$form->get('photoTrois')->getData(),$form->get('photoQuatre')->getData()];
                $calculNombreTotal = $depotFormData->getNbArmePoing() + $depotFormData->getNbArmeEpaule();
                $dossierDepot = $depotFormData->getNom() . "-" . $depotFormData->getPrenom() .'-'.date('d-m-Y');

                $depotVente->setNbTotalArme($calculNombreTotal); //nombre total d'armes

                if($depotFormData->getDateNaissance() > $datechecker){ //si utilisateur ayant moins de 18 ans | si plus jeune que 18 alors erreur
                    $erreurs[0]["naissance"] = "Vous devez au moins avoir 18 ans pour déposer des armes.";
                    $messageErreurs = true;
                }
               
                // dd($erreurs);
                if(!$messageErreurs){
                    $dossierFull = './../public/uploads/depot-ventearmes/' . $dossierDepot;
                    $idDepotImages = substr(uniqid(),5,5);
                    if(!is_dir($dossierFull)){
                        mkdir($dossierFull);
                    }
                    if($files){
                        $count = 0;
                        foreach($files as $file){
                            if($file !== null){
                                $file_name = pathinfo("depot-vente-" . $depotFormData->getNom() . "-" . $depotFormData->getPrenom() .'-'.date('d-m-Y') . '-' . $idDepotImages . '-' . $count, PATHINFO_FILENAME);
                                $newFilename = $file_name .'.'. $file->guessExtension();
                                try{
                                    // header("Content-Type: images/*"); //puis conversion forcé en pdf
                                    file_put_contents($dossierFull .'/'. $newFilename, file_get_contents($file));
                                    $this->gs->imageCompressor($dossierFull .'/'. $newFilename, $dossierFull .'/'. $newFilename); //compression d'image

                                } catch (FileException $e){
                                    //
                                }
                                switch($count){
                                    case 0:
                                        $depotVente->setPhotoUn($dossierDepot .'/'. $newFilename);
                                        break;
                                    case 1:
                                        $depotVente->setPhotoDeux($dossierDepot .'/'. $newFilename);
                                        break;
                                    case 2:
                                        $depotVente->setPhotoTrois($dossierDepot .'/'. $newFilename);
                                        break;
                                    case 3:
                                        $depotVente->setPhotoQuatre($dossierDepot .'/'. $newFilename);
                                        break;
                                }
                                $mailDocuments .= "<div style='display: flex; flex-direction: row;'><img src='" . $URL ."/uploads/depot-ventearmes/" . $dossierDepot .'/'. $newFilename ."' width='150' height='100' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                                $count++;
                            }
                        }
                    }
                    $em->persist($depotVente); // MAJ BDD
                    $em->flush();

                    $this->envoiMail($depotVente, $mailDocuments, $mail, "dépôt d'arme(s)");

                    $this->addFlash('notice', 'Merci de nous avoir contacté. Nous allons vous répondre dans les meilleurs délais.');
                    return $this->redirectToRoute('app_depot_vente');
                } else {
                    $this->addFlash('warning', 'Vous avez mal rempli le formulaire, veuillez consulter les erreurs ci-dessous.');
                }
            //}
        }

        return $this->render('about/depot_vente.html.twig', [
            'controller_name' => 'AboutController',
            'form' => $form->createView(),
            'adresse' => $adresseUser,
            'erreurs' => $erreurs[0]
        ]);
    }

    #[Route('/rachat-arme-cash', name: 'app_rachat_cash')]
    public function rachatArme(EntityManagerInterface $em, Request $request): Response
    {
        $URL = 'https://arsenal-pro.fr';
        $datenow = new \DateTimeImmutable('now +1 hours');
        $datechecker = new \DateTimeImmutable('now -18 years');
        $rachat = new DepotVente();
        $form = $this->createForm(RachatType::class, $rachat);
        $form->handleRequest($request);
        $adresse = [];
        $erreurs[0] = null; //declarer null ou sinon erreur key à 0
        $messageErreurs = false;
        if($this->getUser()){
            $adresse = $em->getRepository(Adress::class)->findBy(['user' => $this->getUser()]);
            $adresseUser = $adresse[0];
        } else {
            $adresseUser = null;
        }

        //check back-end
        if ($form->isSubmitted() && $form->isValid()) {
            $erreurs = [];
            $mailDocuments = "";
                if($this->getUser()){
                    $rachat->setUser($this->getUser());
                }
                $rachat->setType('rachat-arme');
                $rachat->setFaitLe($datenow);
                $mail = new Mail();
                $rachatFormData = $form->getData();
                $files = [$form->get('photoUn')->getData(),$form->get('photoDeux')->getData(),$form->get('photoTrois')->getData(),$form->get('photoQuatre')->getData()];
                $calculNombreTotal = $rachatFormData->getNbArmePoing() + $rachatFormData->getNbArmeEpaule();
                $dossierDepot = $rachatFormData->getNom() . "-" . $rachatFormData->getPrenom() .'-'.date('d-m-Y');

                // if($calculNombreTotal !== $rachatFormData->getNbTotalArme()){ //verif pour voir si l'addition des deux est égale au nb total d'armes
                //     $erreurs[0]["nombre"] = "Le nombre total d'armes doit être égale aux quantités d'armes de poing et quantités d'armes d'épaule total.";
                //     $messageErreurs = true;
                // }
                $rachat->setNbTotalArme($calculNombreTotal); //nombre total d'armes

                // dd($rachatFormData->getDateNaissance() < $datechecker, $rachatFormData->getDateNaissance(), $datechecker);
                if($rachatFormData->getDateNaissance() > $datechecker){ //si utilisateur ayant moins de 18 ans | si plus jeune que 18 alors erreur
                    $erreurs[0]["naissance"] = "Vous devez au moins avoir 18 ans pour déposer des armes.";
                    $messageErreurs = true;
                }
               
                // dd($erreurs);
                if(!$messageErreurs){
                    $dossierFull = './../public/uploads/rachat-armes/' . $dossierDepot;
                    $idDepotImages = substr(uniqid(),5,5);
                    if(!is_dir($dossierFull)){
                        mkdir($dossierFull);
                    }
                    if($files){
                        $count = 0;
                        foreach($files as $file){
                            if($file !== null){
                                $file_name = pathinfo("rachat-" . $rachatFormData->getNom() . "-" . $rachatFormData->getPrenom() .'-'.date('d-m-Y') . '-' . $idDepotImages . '-' . $count, PATHINFO_FILENAME);
                                $newFilename = $file_name .'.'. $file->guessExtension();
                                try{
                                    // header("Content-Type: images/*"); //puis conversion forcé en pdf
                                    file_put_contents($dossierFull .'/'. $newFilename, file_get_contents($file)); //ajout du fichier au dossier
                                    $this->gs->imageCompressor($dossierFull .'/'. $newFilename, $dossierFull .'/'. $newFilename); //compression d'image

                                } catch (FileException $e){
                                    //
                                }
                                switch($count){
                                    case 0:
                                        $rachat->setPhotoUn($dossierDepot .'/'. $newFilename);
                                        break;
                                    case 1:
                                        $rachat->setPhotoDeux($dossierDepot .'/'. $newFilename);
                                        break;
                                    case 2:
                                        $rachat->setPhotoTrois($dossierDepot .'/'. $newFilename);
                                        break;
                                    case 3:
                                        $rachat->setPhotoQuatre($dossierDepot .'/'. $newFilename);
                                        break;
                                }
                                $mailDocuments .= "<div style='display: flex; flex-direction: row;'><img src='" . $URL ."/uploads/rachat-armes/" . $dossierDepot .'/'. $newFilename ."' width='150' height='100' style='object-fit: contain; height: 100px !important;'/></div><br/>"; //pour le mail admin en HTML
                                $count++;
                            }
                        }
                    }
                    $em->persist($rachat); // MAJ BDD
                    $em->flush();

                    $this->envoiMail($rachat, $mailDocuments, $mail, "rachat d'arme(s)");

                    $this->addFlash('notice', 'Merci de nous avoir contacté. Nous allons vous répondre dans les meilleurs délais.');
                    return $this->redirectToRoute('app_rachat_cash');
                } else {
                    $this->addFlash('warning', 'Vous avez mal rempli le formulaire, veuillez consulter les erreurs ci-dessous.');
                }
            //}
        }

        return $this->render('about/rachat_armes.html.twig', [
            'controller_name' => 'AboutController',
            'form' => $form->createView(),
            'adresse' => $adresseUser,
            'erreurs' => $erreurs[0]
        ]);
    }

    public function envoiMail($depotVente, $mailDocuments, $mail, $type){
        $URL = "";
        if($type == "dépôt d'arme(s)"){
            $URL = "https://arsenal-pro.fr/admin?crudAction=detail&crudControllerFqcn=App%5CController%5CAdmin%5CDepotVenteCrudController&entityId=";
        }
        if($type == "rachat d'arme(s)"){
            $URL = "https://arsenal-pro.fr/admin?crudAction=detail&crudControllerFqcn=App%5CController%5CAdmin%5CRachatArmeCrudController&entityId=";
        }
        
        $subject = 'Demande de ' . $type .' depuis site ' . $depotVente->getPrenom() ." ".$depotVente->getNom();
        $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
            <h2 style='font-weight: normal;'>Description de ". $depotVente->getPrenom() ." ".$depotVente->getNom() ."</h2><br/>
            <h3 style='font-weight: normal;'>". $depotVente->getDescription()."</h3>
        </div><br/><br/>
        <div>
            <p style='font-weight: normal;'>". $depotVente->getPrenom() ." ".$depotVente->getNom() ."</p>
            <p style='font-weight: normal;'>" . $depotVente->getEmail() ."</p>
            <p style='font-weight: normal;'>Tél : " . $depotVente->getPhone() ."</p>
            <p style='font-weight: normal;'>Adresse : " . $depotVente->getAdresse() ."," . $depotVente->getPostal() . "," . $depotVente->getCity() ."</p>
        </div>
        <div>" . $mailDocuments . "</div>
        <div>Prix du lot : ". $depotVente->getPrixLot() ."</div>
        <div style='text-align: center; background-color: #07af15; width: 200px; padding: 12px; font-size: 1.1em; margin: auto;'><a style='color: white; text-decoration: none;' href='" . $URL . $depotVente->getId() ."&page=1'>Consulter la demande</a></div> 
        </section></section>";

        $subjecttwo = 'ARSENAL PRO va vérifier votre offre';
        $contenttwo = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <h2>Accusé de réception de votre dépôt d'arme(s)</h2><br/>
        <div>
            <h3 style='font-weight: normal;'>". $depotVente->getPrenom() .", nous avons bien reçu votre demande de " . $type ." en date du " . date('d/m/Y') . "</h3><br/>
            <h3 style='font-weight: normal;'>Nous nous efforçons de répondre dans un délai inférieur à 48h. Nous allons vérifier la description et l'état de vos armes à l'aide de vos photos.</h3>
            <br/>
            <div>" . $mailDocuments . "</div>
            <div>Prix du lot : ". $depotVente->getPrixLot() ."</div>
        </div>
        </section></section>";

        $mail->send($depotVente->getEmail(), $depotVente->getNom(), $subjecttwo, $contenttwo, 4640141); //pour utilisateur
        $mail->send("arsenalpro74@gmail.com", $depotVente->getNom(), $subject, $content, 4639500);
        $mail->send("armurerie@arsenal-pro.com", $depotVente->getNom(), $subject, $content, 4639500);
    }
}
