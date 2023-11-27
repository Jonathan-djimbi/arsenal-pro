<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ContactController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerService $mailer)
    {
        $datenow =  date('Y-m-d');
        $visiteur = $_SERVER['REMOTE_ADDR'];
        $URL = "https://arsenal-pro.fr/";
        $contactez = new Contact();
        $form = $this->createForm(ContactType::class, $contactez);
        $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenow], ['id'=>'desc']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(count($messagescontacts) >= 3 && strtotime($messagescontacts[0]->getFaitle()) === strtotime($datenow)){ //verif anti spam avec comparaison d'adresse ip et dates
                $this->addFlash('warning', "Désolé vous avez dépassé la limite d'envoi qui est de 3 messages par jours.");
            } else {
                $contactez->setFaitle($datenow);
                $contactez->setVisiteur($visiteur);

                if($this->getUser()){
                    $contactez->setUser($this->getUser());
                }
                $mail = new Mail();
                $contactFormData = $form->getData();
                $subject = 'Demande contact depuis site ' . $contactFormData->getPrenom() ." ".$contactFormData->getNom();
                // $content = "<h2>" . $contactFormData->getNom() . ' vous a envoyé le message suivant :</h2><br>' . $contactFormData->getDescription();
                $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                <div>
                    <h2 style='font-weight: normal;'>Message de ". $contactFormData->getPrenom() ." ".$contactFormData->getNom() ."</h2><br/>
                    <h3 style='font-weight: normal;'>". $contactFormData->getDescription()."</h3>
                </div><br/><br/>
                <div>
                    <p style='font-weight: normal;'>". $contactFormData->getPrenom() ." ".$contactFormData->getNom() ."</p>
                    <p style='font-weight: normal;'>" . $contactFormData->getEmail() ."</p>
                    <p style='font-weight: normal;'>Tél : " . $contactFormData->getPhone() ."</p>
                </div>
                </section></section>";

                $subjecttwo = 'ARSENAL PRO va vous répondre';
                $contenttwo = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                <h2>Accusé de réception de votre message</h2><br/>
                <div>
                    <h3 style='font-weight: normal;'>". $contactFormData->getPrenom() .", nous avons bien reçu votre message en date du " . $datenow . "</h3><br/>
                    <h3 style='font-weight: normal;'>Nous nous efforçons de répondre dans un délai inférieur à 48h. Nous recherchons systématiquement une solution avant de vous répondre.</h3>
                    <br/>
                    <h3 style='font-weight: normal;'>Votre message : <br>". $contactFormData->getDescription()."</h3>
                </div>
                </section></section>";

                $mail->send($contactFormData->getEmail(), $contactFormData->getNom(), $subjecttwo, $contenttwo, 4640141); //pour utilisateur
                $mail->send("arsenalpro74@gmail.com", $contactFormData->getNom(), $subject, $content, 4639500);
                $mail->send("armurerie@arsenal-pro.com", $contactFormData->getNom(), $subject, $content, 4639500);

                $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais.');

                $this->entityManager->persist($contactez); // MAJ BDD
                $this->entityManager->flush();
            }
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
