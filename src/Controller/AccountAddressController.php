<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Adress;
use App\Entity\ComptesDocuments;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/address', name: 'app_account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig', []);
    }

    #[Route('/compte/ajouter-une-adresse', name: 'app_account_address_add')]
    public function add(Cart $cart ,Request $request)
    {
        $checkLimiteAddress = $this->entityManager->getRepository(Adress::class)->findBy(["user" => $this->getUser()]);
        if(count($checkLimiteAddress) <= 1){ //si un malin veut créer plus de 2 adresses de livraison via le lien alors...
            $address = new Adress();

            $form = $this->createForm(AddressType::class, $address);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $address->setUser($this->getUser());
                $this->entityManager->persist($address);
                $this->entityManager->flush();
                // if($cart->get()){
                //     return  $this->redirectToRoute('app_order');
                // }

                return $this->redirectToRoute('app_account_address');

            };
            return $this->render('account/address_add.html.twig', [

                'form' => $form->createView()
            ]);
        } else {
            if($cart->get()){
                return  $this->redirectToRoute('app_order');
            }
            return $this->redirectToRoute('app_account_address');
        }
    }


    #[Route('/compte/modifier-une-adresse/{id}', name: 'app_account_address_edit')]
    public function modifier(Request $request, $id): Response
    {
        //on cherche l'objet qu'on veut modifier

        // $address = new Adress();
        $address = $this->entityManager->getRepository(Adress::class)->findOneById($id);
        //vérifier si l'adresse existe
        if (!$address || $address->getUser() != $this->getUser()) {


            //s'il n'existe pas on le redirige vers adresse
            //verifie egalement si l'adres est bien de celui de user
            return $this->redirectToRoute('app_account_address');
        }
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
//si adress ajoutée donc on fait une rédirection vers le compte
            return $this->redirectToRoute('app_account_address');
            //dd($address);
        }
        return $this->render('account/address_add.html.twig', [

            'form' => $form->createView()
        ]);


    }

    //suppression
    #[Route('/compte/supprimer-une-adresse/{id}', name: 'app_account_address_delete')]
    public function delete($id): Response
    {
        $address = $this->entityManager->getRepository(Adress::class)->findOneById($id); //adresse séléctionnée
        $listeAddressUser = $this->entityManager->getRepository(Adress::class)->findBy(['user' => $this->getUser()], ['id'=>'asc']); //recherche première adresse d'user
        if($listeAddressUser[0] !== $address){ //vérifie si ça correspond ou pas la première adresse [0] créée lors de l'inscription.
            if($address){ //vérifier si l'adresse existe
                if($address->getUser() == $this->getUser()){ // vérifier si l'adresse correspond à l'utilisateur connecté 
                    $this->entityManager->remove($address);
                    $this->entityManager->flush();
                }
            }
        } 
        // redirection vers le compte
        return $this->redirectToRoute('app_account_address');

    }
}
