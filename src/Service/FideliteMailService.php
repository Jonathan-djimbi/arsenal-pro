<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use App\Entity\Adress;
use App\Entity\CarteFidelite;
use App\Entity\MailRetourStock;
use App\Entity\Produit;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FideliteMailService{
    
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/mail_fidelite_test_498464', name: 'mail_fidelite_testar')]
    public function envoiMail($subject, $state){ //ENVOI DE MAIL AUTOMATIQUE pour FIDELITE
       
        $compteFidele = $this->entityManager->getRepository(CarteFidelite::class)->findAll();
        // dd($compteFidele[0]->getPoints());
        $mailEnvoi = new Mail();
        // $count = 0;
        // dd($update);
        if($compteFidele && count($compteFidele) > 0){
            foreach($compteFidele as $cf){
                if($state == "recap"){ //RECAPITULATIF
                    // $mailEnvoi->send($cf->getUser()->getEmail(), "Utilisateur Arsenal Pro", $subject, $this->mailContentRecap($cf->getUser(), $cf->getPoints()), 4640369); //envoi de mail
                    // echo $cf->getUser()->getEmail(). " mail envoyé\n";
                    dd("UNUSED");
                }
                else if($state == "alerte_points"){ //CHAQUE 15 SEPTEMBRE, ON ALERTE LE CLIENT
                    $mailEnvoi->send($cf->getUser()->getEmail(), "Utilisateur Arsenal Pro", $subject, $this->alerteResetPoint($cf->getUser(), $cf->getPoints()), 4640369); //envoi de mail
                    echo $cf->getUser()->getEmail(). " mail envoyé\n";

                } else {
                    echo "Erreur dans le deuxième paramètre de la fonction";
                }
            }
        }
        return new Response();
    }

    // public function mailContentRecap($client, $points){
    //     $URL = "https://arsenal-pro.fr";
    //     $content = "<section style='font-family: arial;'> <section style='width: 95%; box-shadow: 2px 2px 10px black; padding: 15px 0;'>
    //     <div>
    //     <h2 style='font-weight: normal; text-align: center;'>Les rangs de fidélité ARSENAL PRO</h2>
    //     <div><br><br>
    //         <h3 style='font-weight: normal;'>Bonjour camarade ". $client->getFirstname() ."</h3>
    //         <p style='font-weight: normal;'>Vous avez en tout ". $points ." point(s) de fidélité dans votre compte client ARSENAL PRO !</p><br><br>
    //         <p style='font-weight: normal;'>Que dites vous de dépenser certain de vos points de fidélité pour vous acheter la carabine, le pistolet ou votre équipement de rêve en réduction ?</p><br><br>
    //         <a href='" .  $URL . "' style='color: white; text-decoration: none; margin: auto;'>
    //             <div style='text-align: center; background-color: #07af15; width: 200px; padding: 10px; margin: auto;'>
    //             Acheter chez ARSENAL PRO</div>
    //         </a>
    //     </div>
    //     <br/>
    //     <div>
    //         <p>Ou sinon... vous pouvez continuer à recollecter de plus en plus de points par achat en étant membre de fidélité !</p>
    //     </div>
    //     </div>
    //     </section></section>";

    //     return $content;
    // }

    public function alerteResetPoint($client, $points){
        $dateNow = new DateTimeImmutable('now +1 hours');
        $dateLimiteString = '' . date('Y') . '-12-31 23:00:00'; //Année dynamique, mois et jours statique
        $dateLimite = new DateTimeImmutable($dateLimiteString); // 31 décembre limite

        $joursRestant = intval((strtotime($dateLimite->format('Y-m-d')) - strtotime($dateNow->format('Y-m-d'))) / (24*60*60));
        $moisRestant = intval((strtotime($dateLimite->format('Y-m-d')) - strtotime($dateNow->format('Y-m-d'))) / ((24*60*60)*31));
        // dd($moisRestant);
        $URL = "https://arsenal-pro.fr";
        $content = "<section style='font-family: arial;'> <section style='width: 95%; box-shadow: 2px 2px 10px black; padding: 15px 0;'>
        <div>
        <h2 style='font-weight: normal; text-align: center;'>La fidélité dans les rangs d'ARSENAL PRO</h2>
        <div><br>
            <h3 style='font-weight: normal;'>Bonjour ". $client->getFirstname() ."</h3>
            <p style='font-weight: normal;'>Faites vous plaisir avec vos ". $points ." points qui représente une valeur en euros de " . number_format($points / 10) ." € !</p><br>
            <a href='" .  $URL . "' style='color: white; text-decoration: none; margin: auto; width: 200px;'>
                <div style='text-align: center; background-color: #07af15; padding: 10px; margin: auto;'>
                J'utilise mes points</div> 
            </a>
            </div>
            <p style='font-weight: normal;'>Vous disposez encore de " . $moisRestant . " mois, soit ". $joursRestant ." jours pour utiliser vos points.</p><br>
        </div>
        </section></section>";

        return $content;
    }

}