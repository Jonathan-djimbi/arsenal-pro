<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Newsletter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter', name: 'sauvegarde_newsletter_mail')] //route pour le post en ajax
    public function index(Request $req, EntityManagerInterface $entityManager, Mail $mail): Response
    {
        $email = $req->request->get('email');
        $abonne = $req->request->get('abonne');
        $check = $entityManager->getRepository(Newsletter::class)->findBy(['email' => $email]);
        if(count($check) <= 0){
            if(!empty($email) && !empty($abonne)){

                $newsletter = new Newsletter();
                $newsletter->setEmail($email);
                $newsletter->setAbonne($abonne);
                $entityManager->persist($newsletter); // MAJ BDD
                $entityManager->flush(); //sauvegarde vers BDD
                setcookie("inscrit_newsletter", true, time() + 150 * 24 * 60 * 60); //enregistre un cookie "inscrit_newsletter" comme ça on ne reaffiche plus le panel newsletter

                $mail = new Mail();
                $content = "<section style='font-family: arial;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                <div>
                    <h2 style='font-weight:  normal;'>Merci d'avoir rejoint les rangs ARSENAL PRO !</h2>
                    <p style='font-weight:  normal;'>Votre adresse email est validé pour la newsletter.</p>
                </div>
                <br/>
                <div>
                    <h3 style='font-weight: normal;'>Profitez dès à présent une remise de 5% pour votre prochaine commande !</h3><br/>
                    <div style='text-align: center; background-color: #07af15; width: auto; padding: 10px; margin: auto; width: 200px; color: white;'><p style='font-weight: bold;'>CODE : NEWSLETTER</p></div>
                </div>
                </section></section>";

                $mail->send($email, "ARSENAL PRO", "Bienvenue dans les rangs ARSENAL PRO " . $email, $content, 4130880);
            }
        }
        return new Response();
    }

    #[Route('/newsletter_stop_afficher', name: 'stop_newsletter_cookie')] //route pour le post en ajax
    public function stopAfficherNewsletter(): Response
    {
        setcookie("inscrit_newsletter", true, time() + 31 * 24 * 60 * 60); //enregistre un cookie "inscrit_newsletter" comme ça on ne reaffiche plus le panel newsletter
        return new Response();
    }
}
