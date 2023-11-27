<?php

namespace App\Controller;

use App\Entity\Adress;
use App\Entity\CarteCadeau;
use App\Entity\CarteFidelite;
use App\Entity\PointFidelite;
use App\Entity\ProfessionnelAssociationCompte;
use App\Form\CarteCadeauType;
use App\Form\CarteFideliteType;
use App\Form\ProfessionnelAssociationCompteType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options as PDFOptions;

class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    public function index(EntityManagerInterface $em, Request $req): Response
    {
        $datenow = new DateTimeImmutable('now +1 hours');
        $checkSiFidelite = $em->getRepository(CarteFidelite::class)->findBy(['user' => $this->getUser()]);
        $checkSiProAssoc = $em->getRepository(ProfessionnelAssociationCompte::class)->findOneBy(['user' => $this->getUser()]);
        $adress = $em->getRepository(Adress::class)->findBy(['user' => $this->getUser()]);
        if(!$adress){ //si au-cas-où pas de adresse alors
            return $this->redirectToRoute('app_account_address');
        }
  
        $form = $this->createForm(CarteFideliteType::class, null, [
            'method' => 'POST',
            'user' => $this->getUser(),
        ]);

        $specialite = ["Pistolet", "Carabine"];
        $pays = ["pays" => 
            ["nom" => 'FR', "image" => "/assets/image/pays/fr.jpg"],
            ["nom" => 'CH', "image" => "/assets/image/pays/ch.jpg"],
            ["nom" => 'BE', "image" => "/assets/image/pays/be.jpg"],
        ];
        // dd($checkSiFidelite);
        if(count($checkSiFidelite) > 0){
            $carte = $checkSiFidelite[0];
            $form->handleRequest($req);
            if($form->isSubmitted() && $form->isValid()){
                $file = $form->get('photo')->getData();
                if($carte->getClub() === null || empty($carte->getClub())){ //si club est null et specialite aussi
                    $carte->setClub($req->get('clubInput')); //récup valeur
                }
                if($carte->getSpecialite() === null){
                    $carte->setSpecialite($form->get('specialite')->getData()); //Recup valeur
                }
                    // dd(pathinfo($file)['extension']);
                if($file){
                    if(!is_dir("./../compte/utilisateurs/". $this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . "")){ 
                        mkdir("./../compte/utilisateurs/". $this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . ""); 
                        mkdir("./../compte/utilisateurs/". $this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . "/fidelite"); 
                    }
                    $file_name = "photoFidelite-" . $this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . ".jpg";
                    file_put_contents("./../compte/utilisateurs/".$this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . "/fidelite/" .$file_name, file_get_contents($file)); 
                    $carte->setPhoto($file_name);
                }            
                $em->persist($carte);
                $em->flush();
                return $this->redirectToRoute('app_account');
            }
            if($carte->getPhoto() !== NULL){
                $trouverPfp = "./../compte/utilisateurs/".$this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . "/fidelite/" . $checkSiFidelite[0]->getPhoto() . "";
                $imagePfp = base64_encode(file_get_contents($trouverPfp)); //lire fichier du serveur en sécurisé
                $src = 'data: '.mime_content_type($trouverPfp).';base64,'.$imagePfp; //puis le rendre sur le client du siteweb à l'aide du MIME
            } else {
                $src = "assets/image/default_pfp.jpg"; //si image pfp NULL
            }
        } else { //si ancien compte alors création automatique depuis la page /compte
            // dd("aaaa");
            $nouveauCompte = new CarteFidelite();
    
            $nouveauCompte->setUser($this->getUser());
            $nouveauCompte->setAdress($adress[0]);
            $nouveauCompte->setTelephone($adress[0]->getPhone());
            $nouveauCompte->setNombreAchat(0);
            $nouveauCompte->setPoints(0);

            $em->persist($nouveauCompte); //insertion BDD
            $em->flush(); //INSERT + MAJ BDD
            $carte = $nouveauCompte;
        }

        return $this->render('account/index.html.twig', [
            'utilisateur_fidele' => $carte,
            'form' => $form->createview(),
            'user' => $this->getUser(),
            'adress' => $adress,
            'proAssoc' => $checkSiProAssoc,
            'pfp' => $src,
            'specialite' => $specialite,
            'pays' => $pays,
            'datenow' => $datenow,
        ]);
    }

    #[Route('/fidelite/points-fidelite', name: 'app_fidelite')]
    public function fidelite(EntityManagerInterface $em, Request $req): Response
    {
        $checkSiFidelite = $em->getRepository(CarteFidelite::class)->findOneBy(['user' => $this->getUser()]);
        $pointFidelite = $em->getRepository(PointFidelite::class)->findAll();

        return $this->render('reglement/grille_fidelite.html.twig', [
            'point_fidelite' => $pointFidelite[0],
            'utilisateur_fidele' => $checkSiFidelite,
        ]);
    }

    #[Route('/compte/carte-cadeau', name: 'app_account_gift_card')]
    public function carteCadeauForm(EntityManagerInterface $em, Request $req): Response
    {
        $form = $this->createForm(CarteCadeauType::class, null);
        $compte = $em->getRepository(CarteFidelite::class)->findOneByUser($this->getUser());
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()){
            $codeinsere = $form->get('code')->getData();
            $checkSiCode = $em->getRepository(CarteCadeau::class)->findOneByCode($codeinsere);
            if($checkSiCode){
                if($checkSiCode->getUsedAt() !== null || $checkSiCode->getClaimedBy() !== null){
                    $this->addFlash('warning',"Ce code cadeau a déjà été utilisé.");
                } else {
                    $compte->setSommeCompte($compte->getSommeCompte() + $checkSiCode->getPrice());
                    $checkSiCode->setUsedAt(new \DateTimeImmutable());
                    $checkSiCode->setClaimedBy($this->getUser());
                    $em->flush();
                    $this->addFlash('success','Votre code est valable et vous avez gagné en tout ' . number_format($checkSiCode->getPrice()/100) .'€ dans votre compte !');
                }
            } else {
                $this->addFlash('warning',"Ce code cadeau n'existe pas.");
            }
        }

        return $this->render('account/carte_cadeau_form.html.twig', [
            'form' => $form->createview(),
            'compte' => $compte,
        ]);
    }


    #[Route('/compte/fidelite/votre-carte-generer', name: 'app_PDFCarteFidelite')]
    public function PDFCarteFidelite(EntityManagerInterface $em, Request $req): Response
    {
        $URL = "https://arsenal-pro.fr";
        $pays = ["pays" => 
            ["nom" => 'FR', "image" => "/assets/image/pays/fr.jpg"],
            ["nom" => 'CH', "image" => "/assets/image/pays/ch.jpg"],
            ["nom" => 'BE', "image" => "/assets/image/pays/be.jpg"],
        ];
        $html_pays = '';
        $html_level = '';
        $src = '';
        $fidelite = $em->getRepository(CarteFidelite::class)->findOneBy(['user' => $this->getUser()]);
        $adresse = $em->getRepository(Adress::class)->findBy(['user' => $this->getUser()]);
        if($fidelite){
            // if($fidelite->getPhoto() !== NULL){
            //     $trouverPfp = "./../compte/utilisateurs/".$this->getUser()->getId() . "-" . $this->getUser()->getFirstname() . "-" . $this->getUser()->getLastname() . "/fidelite/" . $fidelite->getPhoto() . "";
            //     $imagePfp = base64_encode(file_get_contents($trouverPfp)); //lire fichier du serveur en sécurisé
            //     $src = 'data: '.mime_content_type($trouverPfp).';base64,'.$imagePfp; //puis le rendre sur le client du siteweb à l'aide du MIME
            // } else {
                $src = "https://arsenal-pro.fr/assets/image/default_pfp.jpg"; //si image pfp NULL
            //}
            foreach($pays as $unPays){
                if($unPays['nom'] == $adresse[0]->getCountry()){
                    $html_pays = "<img id='paysImage' width='90' height='60' alt='votre pays drapeaux flag' src='" . $URL . $unPays['image'] . "' draggable='false' />";
                }
            }
            if ($fidelite->getNombreAchat() >= 2 && $fidelite->getNombreAchat() < 6){ //Niveau 1 fidelite
                $html_level = "<img width='60' id='carteFidelite_imageNiveau' src='" . $URL . "/assets/image/fidelite_niveau_un.png' alt='arsenal-pro-logo-level' draggable='false'/>";
            }
            if ($fidelite->getNombreAchat()  >= 6 && $fidelite->getNombreAchat() < 12){ //Niveau 2 fidelite
                $html_level = "<img width='60' id='carteFidelite_imageNiveau' src='" . $URL . "/assets/image/fidelite_niveau_deux.png' alt='arsenal-pro-logo-level' draggable='false'/>";
            } 
            if ($fidelite->getNombreAchat()  >= 12){ //Niveau 3 fidelite
                $html_level = "<img width='60' id='carteFidelite_imageNiveau' src='" . $URL . "/assets/image/fidelite_niveau_trois.png' alt='arsenal-pro-logo-level' draggable='false'/>";
            }

            $renduCarteFidelite = "
            <style>
            @font-face { font-family: Roboto;  font-style: normal; src: url('https://arsenal-pro.fr/assets/fonts/Roboto-Regular.ttf'); }
            *{
                font-family: Roboto;
                font-weight: normal;
            }
            label {
                display: inline-block !important;
                margin-bottom: 0.5rem !important;
            }
            .carteFidelite{
                width: 100%;
                margin: auto;
                border: solid 1px;
                border-radius: 15px;
                max-width: 500px;
                margin: auto;
                position: relative;
                background: linear-gradient(140deg,white,white,#ddddddb3,#ddddddb3, white);
                height: 280px;
            }
            .inputCarteFidelite, .inputCarteFideliteCreation{
                border: none;
                border-bottom: solid 1px rgba(0, 0, 0, 0.007);
                border-radius: 0;
                background-color: transparent;
            }
            #carteFidelite_points{
                position: absolute;
                bottom: 5px;
                left: 5px;
                display: block;
            }
            #carteFidelite_pfp, #paysImage{
                border: solid 1px black;
                object-fit: cover;
            }
            .carteFidelite_bandeau{
                display: block;
                position: relative;
                height: 40px;
                background-color: rgb(35, 128, 250);
                width: 100%;
                text-align: center;
                font-size: 22px;
                color: white;
                border-top-left-radius: 13px;
                border-top-right-radius: 13px;
            }
            #carteFidelite_imageLogo, #carteFidelite_imageNiveau{
                position: absolute; 
                right: 0;
            }
            #carteFidelite_imageNiveau{
                bottom: 5px;
                height: 90px;
            }
            #carteFidelite_niveaux{
                display: flex;
                flex-direction: row;
                justify-content: center;
                height: auto;
            }
            #carteFidelite_niveaux > div{
                width: 200px;
                height: 300px;
                margin: 0 10px;
            }
            #carteFidelite_niveaux div img{
                object-fit: contain;
            }
            #info_fidelite{
                padding: 15px;
            }
            #info_fidelite > .photo{
                position: absolute;
            }
            fieldset {
                min-width: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                border: 0 !important;
            }
            .ml-7{
                margin-left: 7rem!important;
                display: block;
            }
            .mb-3{
                margin-bottom: 1rem!important;
                display: block;
            }
            </style>
            <div class='carteFidelite'>
            <div class='carteFidelite_bandeau'>ARSENAL PRO</div>
                <img width='80' id='carteFidelite_imageLogo' src='https://arsenal-pro.fr/assets/image/icon-logo-arsenal-pro.png' alt='arsenal-pro-logo-icon' draggable='false'/>
                " . $html_level ."
                <section id='info_fidelite'>
                <div class='photo'>
                    <div style='margin-bottom: 5px;'>
                        <img id='carteFidelite_pfp' width='90' height='90' alt='photo-profile-fidelite-arsenal-pro' src='" . $src .  "' draggable='false'/>
                    </div>
                    " . $html_pays . "
                </div>
                <div class='ml-7'>
                    <fieldset>
                        <label for='name'>Nom : </label>
                        <input name='name' type='text' class='inputCarteFidelite' value='". $this->getUser()->getFullname() ."' disabled/>
                    </fieldset>
                    <fieldset>
                        <label for='email'>Email : </label>
                        <input name='email' type='text' class='inputCarteFidelite' style='width: 220px; text-overflow: ellipsis;' value='". $this->getUser()->getEmail() ."' disabled/>
                    </fieldset>
                    <fieldset>
                        <label for='tel'>Téléphone : </label>
                        <input name='tel' type='text' class='inputCarteFidelite' value='". $adresse[0]->getPhone() ."'/>
                    </fieldset>
                    <fieldset>
                        <label for='adress'>Adresse : </label>
                        <input name='adress' type='text' class='inputCarteFidelite' value='". $adresse[0]->getAdress() . ", " . $adresse[0]->getCity() ."' disabled/><br/>
                    </fieldset>
                    <fieldset>
                        <label for='spe'>Spécialité : </label>
                        <input name='spe' type='text' class='inputCarteFidelite' value='". $fidelite->getSpecialite() . "' disabled/>
                    </fieldset>
                    <fieldset>
                        <label for='club'>Club de tir : </label>
                        <input name='club' type='text' class='inputCarteFidelite' value='" . $fidelite->getClub() . "' />
                    </fieldset>
                <div class='mb-2'></div>
            </div>
            <div id='carteFidelite_points'>
                <div>Vos points : " . $fidelite->getPoints() ."</div>
            </div>
            </div>";
            // echo $renduCarteFidelite;
            $options = new PDFOptions();
            $options->setIsRemoteEnabled(true); //activer la lecture d'images
            $pdf = new Dompdf($options);
            $pdf->loadHtml($renduCarteFidelite); //conversion html en pdf
            $pdf->setPaper('A4');
            $pdf->render();
            $pdf->stream("carte_fidelite_arsenal_pro.pdf", array("Attachment" => true));
        }
        return new Response();
    }
}
   