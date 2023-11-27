<?php

namespace App\Controller;

use App\Entity\HistoriqueReservation;
use App\Entity\ReservationActivite;
use App\Entity\ReservationFormation;
use App\Form\OrderDetailsReservationActiviteType;
use Doctrine\ORM\EntityManagerInterface;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function enleverAccents($str){ //efficace car le lien URL ne doit pas avoir d'accents provenant du nom et prénom
        return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }


    #[Route('/location-armes', name: 'app_location_armes')]
    public function locationArmes(): Response
    {
        $armes = $this->em->getRepository(ReservationActivite::class)->findBy(['type' => 0]);

        return $this->render('reservation/location_armes.html.twig', [
            'armes' => $armes,
        ]);
    }

    #[Route('/formations-specialisees', name: 'app_formations_specialisees')]
    public function formationsSpecialisees(): Response
    {
        $formations = $this->em->getRepository(ReservationActivite::class)->findBy(['type' => 1]);

        return $this->render('reservation/formations_specialisees.html.twig', [
            "formations" => $formations,
        ]);
    }

    #[Route('/activites-reserver/{id}-{name}', name: 'app_reservation_activite')]
    public function reservationActivite(Request $request, $id, $name): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $activite = $this->em->getRepository(ReservationActivite::class)->findOneById($id);
        
        if($activite){
            $orderReservation = new HistoriqueReservation();
            $form = $this->createForm(OrderDetailsReservationActiviteType::class, $orderReservation);
            $form->handleRequest($request);
            $dateNow = new \DateTimeImmutable('+1 day');
            if($form->isSubmitted()){
    
                $reserverDate = $form->get('reservationPourLe')->getData();
                $typeFormation = $form->get('typeFormation')->getData();
                if($reserverDate < $dateNow){
                    $this->addFlash('warning',"La date de réservation ne doit pas être inférieur ou égale à aujourd'hui");
                    return $this->redirectToRoute('app_reservation_activite', ['id' => $id, 'name' => $name]);
                }
                $orderReservation->setUser($this->getUser());
                $orderReservation->setActiviteName($activite->getName());
                $orderReservation->setActivite($activite);
                $orderReservation->setReservationPourLe($reserverDate);

                if($typeFormation){
                    $orderReservation->setTypeFormation($typeFormation);
                }

                $facturecount = 1; //on commence par 1001 pour la facture
                $countMonth = intval(date('m'));
                $annee = intval(date('Y'));
                if(!is_dir("./../factures/". date("m-Y"). "")){ //dossier mois-année
                    mkdir("./../factures/". date("m-Y"). "");
                } else {
                    for($i = 1; $i < $countMonth+1; $i++){
                        if($i < 10){ // mois
                            $facturecount = $facturecount + count(glob("./../factures/" .'0'. $i . '-' . $annee . "/*")); //compte combien de factures sont dans le dossier
                            // dd(glob("./../factures/" .'0'. $i . '-' . $annee . "/*"));
                        } else {
                            $facturecount = $facturecount + count(glob("./../factures/". $i . '-' . $annee . "/*")); //compte combien de factures sont dans le dossier
                        }
                    }
                    $facturecount = $facturecount + 1; //final count
                }
                // dd($facturecount);
                if($facturecount < 999){
                    $nofacture = "S" . substr(date("Y"),2,4) ."-". date("m-d"). "-1" .  str_pad($facturecount,3,0,STR_PAD_LEFT);
                } else {
                    $nofacture = "S" . substr(date("Y"),2,4) ."-". date("m-d"). "-" .  (1000 + $facturecount);
                }
                if($activite->getType() === 0){ //location
                    $reference = $nofacture . '-' .  $this->enleverAccents($orderReservation->getUser()->getLastname())  . "-Loca-" . substr(uniqid(),4,4);
                }
                if($activite->getType() === 1){ //formation
                    $reference = $nofacture . '-' .  $this->enleverAccents($orderReservation->getUser()->getLastname())  . "-Forma-" . substr(uniqid(),4,4);
                }
                
                //
                $orderReservation->setReference($reference);
                $orderReservation->setTotal($activite->getPrice());
                $orderReservation->setState(0); //non payé
                //insertion BDD
                $this->em->persist($orderReservation);
                $this->em->flush();

                return $this->redirectToRoute('app_systempay_reservation', ['reference' => $reference]);

            }
            return $this->render('reservation/reservation.html.twig', [
                "activite" => $activite,
                "form" => $form->createView(),
            ]);
        } else {
            return new Response();
        }

    }

}


