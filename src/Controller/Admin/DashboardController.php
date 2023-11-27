<?php

namespace App\Controller\Admin;

use App\Entity\Adress;
use App\Entity\Annonce;
use App\Entity\BourseArmes;
use App\Entity\Calibre;
use App\Entity\Carrier;
use App\Entity\CarteCadeau;
use App\Entity\CarteFidelite;
use App\Entity\Category;
use App\Entity\CodePromo;
use App\Entity\ComptesDocuments;
use App\Entity\ConditionFidelite;
use App\Entity\ConditionGeneraleVente;
use App\Entity\DepotVente;
use App\Entity\Famille;
use App\Entity\Fournisseurs;
use App\Entity\Header;
use App\Entity\HistoriqueReservation;
use App\Entity\Marque;
use App\Entity\MenuCategories;
use App\Entity\Newsletter;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\PointFidelite;
use App\Entity\Prestation;
use App\Entity\Produit;
use App\Entity\ProduitListeAssociation;
use App\Entity\Pub;
use App\Entity\ReglementGeneraleProtectionDonnees;
use App\Entity\RemiseGroupe;
use App\Entity\ReservationActivite;
use App\Entity\ReservationFormation;
use App\Entity\SubCategory;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
       
        
        return $this->render('admin/index.html.twig');
        return parent::index();
        
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Arsenal Pro - Dashboard');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoUrl('Accueil du site', 'fas fa-home', '/');
        yield MenuItem::linktoUrl('Bibliothèque', 'fa fa-envelope', '/admin/library');
        yield MenuItem::linktoUrl('Statistique', 'fa fa-envelope', '/admin/stats');
        yield MenuItem::linkToCrud('Commandes', 'fas fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('Utilisateur', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Documents clients', 'fas fa-user', ComptesDocuments::class);
        yield MenuItem::linktoUrl('Ajouter documents clients', 'fas fa-user', '/admin/documents-clients');
        yield MenuItem::linkToCrud('Catégorie', 'fas fa-list', Category::class);
        yield MenuItem::linkToCrud('Menu manager', 'fas fa-list', MenuCategories::class);
        yield MenuItem::linkToCrud('Sous-catégorie', 'fas fa-list', SubCategory::class);
        yield MenuItem::linkToCrud('Sous-sous-catégorie', 'fas fa-list', Famille::class);
        yield MenuItem::linkToCrud('Codes promotion', 'fa fa-ticket', CodePromo::class);
        yield MenuItem::linkToCrud('Calibre', 'fas fa-list', Calibre::class);
        yield MenuItem::linkToCrud('Fournisseurs', 'fas fa-list', Fournisseurs::class);
        yield MenuItem::linkToCrud('Marques', 'fa-regular fa-registered', Marque::class);
        yield MenuItem::linkToCrud('Produits', 'fas fa-tag', Produit::class);
        yield MenuItem::linkToCrud('Prestations', 'fas fa-tag', Produit::class)->setController(PrestationCrudController::class);
        yield MenuItem::linkToCrud('Cartes cadeaux', 'fa fa-ticket', CarteCadeau::class);
        yield MenuItem::linkToCrud('Remise catégories', 'fa fa-ticket', RemiseGroupe::class);
        yield MenuItem::linkToCrud('Bourse aux munitions', 'fas fa-tag', BourseArmes::class);
        yield MenuItem::linkToCrud("Location d'armes", 'fas fa-tag', ReservationActivite::class)->setController(ReservationCrudController::class);
        yield MenuItem::linkToCrud("Formations spécialisées", 'fas fa-tag', ReservationActivite::class)->setController(ReservationFormationCrudController::class);
        yield MenuItem::linkToCrud('Transporteurs', 'fas fa-dolly', Carrier::class);
        yield MenuItem::linkToCrud('Réservations', 'fa fa-shopping-cart', HistoriqueReservation::class);
        yield MenuItem::linkToCrud('Annonce accueil', 'fas fa-exclamation-triangle', Annonce::class);
        yield MenuItem::linkToCrud('Les pubs', 'fa fa-desktop', Pub::class);
        yield MenuItem::linkToCrud('Dépôt vente', 'fas fa-circle-down', DepotVente::class)->setController(DepotVenteCrudController::class);
        yield MenuItem::linkToCrud("Rachat d'armes", 'fas fa-money-bill-1', DepotVente::class)->setController(RachatArmeCrudController::class);
        yield MenuItem::linkToCrud('Réglement - CGV', 'fas fa-scale-balanced', ConditionGeneraleVente::class);
        yield MenuItem::linkToCrud('Réglement - RGPD', 'fas fa-scale-balanced', ReglementGeneraleProtectionDonnees::class);
        yield MenuItem::linkToCrud('Programme de fidélité', 'fas fa-scale-balanced', ConditionFidelite::class);
        yield MenuItem::linkToCrud('Taux fidélité', 'fas fa-percent', PointFidelite::class);
        // yield MenuItem::linkToCrud('Montant compte', 'fas fa-percent', CarteFidelite::class)->setController(MontantCompteCrudController::class);
        yield MenuItem::linkToCrud('Commandes non abouties', 'fas fa-circle-xmark', Order::class)->setController(Order2CrudController::class);
    }
}
