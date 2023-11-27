<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\CarteCadeau;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteCadeauController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    #[Route('/carte/cadeau', name: 'app_carde_cadeau')]
    public function index(): Response
    {
        return $this->render('carde_cadeau/index.html.twig', [
            'controller_name' => 'CardeCadeauController',
        ]);
    }

    #[Route('/carte/cadeau/generator-test', name: 'app_carde_cadeau_generator')]
    public function generator($carteElement, $nofacture, $order): Response
    {
        $dataCode = [];
        foreach($carteElement as $cm){
            $prix = $cm["prix"];
            $carte = new CarteCadeau();
            $code = strtoupper($this->getUser()->getLastname() . "" . str_replace(["+", "/", "=", "?", "!", "#","&",".","'","-"], "", random_int(0,99) . substr(base64_encode(uniqid()), 7,4)));
            //TODO : faire une fonction qui permet de check si le code qu'on va créer existe déjà
            // $check = $this->em->getRepository(CarteCadeau::class)->findByCode($code); 
            // if(count($check) > 0){ //si le même code existe, on regénére
            //     $code = strtoupper($this->getUser()->getLastname() . "" . str_replace(["+", "/", "=", "?", "!", "#","&",".","'","-"], "", random_int(0,99) . substr(base64_encode(uniqid()), 7,4))); 
            // }
            $carte->setCode($code);
            $carte->setGeneratedAt(new \DateTimeImmutable());
            $carte->setPrice($prix);
            $this->em->persist($carte);
            $this->em->flush();
            $dataCode[] = ["prix" => $prix, "code" => $code];
            // dd('CARTE CADEAU CREEE');
        }
        $this->genererVisuelCadeau($dataCode, $nofacture, $order);

        return new Response();
    }

    public function genererVisuelCadeau($dataCode, $nofacture, $order){
        $mail = new Mail();
        $codes = "";
        foreach($dataCode as $count){
            $codes .= "<p style='font-weight: bold; margin-bottom: 10px;'>Code de " . number_format($count["prix"]/100) . "€ : ". $count["code"] ."</p><br/>";
        }
        $mailContent = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
        <div>
        <h2 style='text-align: center; font-weight: normal;'>Vous avez un ou plusieurs code(s) cadeau(x) pour la commande n°" . $nofacture . " !</h2>
        <h3 style='font-weight: normal;'>Activer votre/vos carte(s) cadeau(x) depuis votre compte client ou le(s) partager à un(e) ami(e) !</h3>
        </div>
        <br/><h3 style='font-weight: normal; text-align: center;'>Le(s) code(s) ci-dessous</h3>
        <div style='margin-top: 25px;'>
            <div style='text-align: center; background-color: #07af15; width: auto; padding: 10px; margin: auto; width: 200px; color: white;'>
                " . $codes . "
            </div>
        </div><br/>    
        </section></section>";    

        // $mail->send("arsenalpro74@gmail.com", "ARSENAL PRO", "COMMANDE : " . $order->getReference(), $content_vendeur, 4639500);
        $mail->send($order->getUser()->getEmail(), "ARSENAL PRO", "CODE CARTE CADEAU ARSENAL PRO", $mailContent, 4639822);
        return new Response();
    }

}
