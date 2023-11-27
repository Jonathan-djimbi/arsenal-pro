<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\CarteFidelite;
use App\Entity\Header;
use App\Entity\User;
use App\Entity\ComptesDocuments;
use App\Entity\ProfessionnelAssociationCompte;
use App\Form\ProfessionnelAssociationCompteType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager=$entityManager;
        
    }

    #[Route('/inscription', name: 'app_register')]
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $isconnecte = $this->container->get('security.authorization_checker');
        // $headers = $this->entityManager->getRepository(Header::class)->findAll();
        $notification = null;
        $URL = "https://arsenal-pro.fr";

       if(!$isconnecte->isGranted('IS_AUTHENTICATED_FULLY')){

            $user = new User();
            $adresse = new Adress();
            $compteFidelite = new CarteFidelite();
            $proAssoc = new ProfessionnelAssociationCompte();
            $form = $this->createForm(RegisterType::class, $user);

            $form->handleRequest($request);

            
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $adresseOuput = ["adress" => $request->get("adresse_compte_create"),
                    "city" => $request->get("ville_compte_create"),
                    "postal" => $request->get("postal_compte_create"), 
                    "country" => $request->get("country_compte_create"), 
                    "phone" => $request->get("tel_compte_create"),
                ];
                $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());
                if (!$search_email) {
                   
                    $password = $hasher->hashPassword($user, $user->getPassword());
                    $user->setPassword($password);

                    $this->entityManager->persist($user); //pas besoins de faire des setter vu que le Form s'en est déjà occupé
                    //ajout adresse
                    $adresse->setUser($user);
                    $adresse->setFirstname($form->get("firstname")->getData());
                    $adresse->setLastname($form->get("lastname")->getData());
                    $adresse->setAdress($adresseOuput["adress"]);
                    $adresse->setCity($adresseOuput["city"]);
                    $adresse->setPostal($adresseOuput["postal"]);
                    $adresse->setCountry($adresseOuput["country"]);
                    $adresse->setPhone($adresseOuput["phone"]);
                    $adresse->setName("Livraison");

                    //création compte fidélité
                    $compteFidelite->setUser($user);
                    $compteFidelite->setAdress($adresse);
                    $compteFidelite->setTelephone($adresse->getPhone());
                    $compteFidelite->setNombreAchat(0);
                    $compteFidelite->setPoints(0);
                    $compteFidelite->setSommeCompte(0);

                    $this->entityManager->persist($adresse); //insertion BDD
                    $this->entityManager->persist($compteFidelite); //insertion BDD
                    $this->entityManager->flush(); //INSERT + MAJ BDD

                    if($request->get("proAssoc") == "on"){ //si coche
                        $proAssoc->setUser($user);
                        //est-ce que c'est un code spaghetti ? à voir
                        if(empty($request->get('pro_raisonSocial'))){ //si vide cela veut dire NULL
                            $proAssoc->setRaisonSocial(null);
                        } else {
                            $proAssoc->setRaisonSocial($request->get('pro_raisonSocial'));
                        }
                        if(empty($request->get("pro_siret"))){
                            $proAssoc->setSiret(null);
                        } else {
                            $proAssoc->setSiret($request->get("pro_siret"));
                        }
                        if(empty($request->get("pro_noTVA"))){
                            $proAssoc->setNoTVA(null);
                        } else {
                            $proAssoc->setNoTVA($request->get("pro_noTVA"));
                        }
                        if($request->get("fdo") == "on"){ //si coche
                            if(empty($request->get('pro_typeFDO'))){
                                $proAssoc->setTypeFDO(null);
                            } else {
                                $proAssoc->setTypeFDO($request->get('pro_typeFDO'));
                            }
                            if(empty($request->get('pro_numeroMatricule'))){
                                $proAssoc->setNumeroMatricule(null);
                            } else {
                                $proAssoc->setNumeroMatricule($request->get('pro_numeroMatricule'));
                            }
                        }
                        $this->entityManager->persist($proAssoc); //insertion BDD
                        $this->entityManager->flush(); //INSERT + MAJ BDD
                    }

                    $mail = new Mail();
                    $content = "<h3 style='font-weight: normal;'>Bonjour ".$user->getFirstname()."</h3><br/>
                    <h3 style='font-weight: normal;'>Votre compte ARSENAL PRO est créé, nous vous avons préparé une sélection pour vous guider au mieux sur la boutique</h3><br/>
                    <p style='font-weight: normal;'>Déposez vos documents sur votre compte, ils sont valables une année pour tous vos achats</p><br/>
                    <div style='text-align: center; background-color: #07af15; width: 200px; padding: 12px; font-size: 1.1em; margin: auto;'><a style='color: white; text-decoration: none;' href='". $URL ."/compte/documents'>Déposez vos documents</a></div>
                    <br/><br/>";
                    $mail->send($user->getEmail(), $user->getFirstname(), 'Vous faites désormais partie de l’aventure ARSENAL PRO ' . $user->getFirstname(), $content, 4639472);

                    $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.";
                } else {
                    $notification = "L'email que vous avez renseigné existe déjà chez ARSENAL PRO.";
                }
            }

            return $this->render('register/index.html.twig', [
                'form' => $form->createView(),
                'notification' => $notification,
            ]);

        } else { //si on est déjà connecté, on ne peut pas avoir accès à la page d'inscription
            return $this->redirectToRoute('app_account');
            
        }
    }
}