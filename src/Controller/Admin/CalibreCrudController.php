<?php

namespace App\Controller\Admin;

use App\Entity\Calibre;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class CalibreCrudController extends AbstractCrudController
{

    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrl;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrl)
    {
        $this->entityManager = $entityManager;
        $this->adminUrl = $adminUrl;
    }

    public static function getEntityFqcn(): string
    {
        return Calibre::class;
    }
    
    public function configureActions(Actions $actions): Actions
    {
        $afficherProduits = Action::new('affichageCalibre', 'Afficher produits', 'fas fa-eye')->linkToCrudAction('affichageCalibre');
        $deafficherProduits = Action::new('desaffichageCalibre', 'Désafficher produits', 'fas fa-eye-slash')->linkToCrudAction('desaffichageCalibre');

        return $actions
            ->add('index', $afficherProduits)
            ->add('index', $deafficherProduits);

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'index', title: "La liste des calibres existants du site")
        ->setPageTitle(pageName: 'new', title: "Ajouter un nouveau calibre à la liste")
        ->setDefaultSort(['calibre' => 'ASC']); //Ordre décroissant sur easyadmin en SQL
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('calibre'),
        ];
    }

    public function updateAffichageProduits($context, $state){
        $calibre = $context->getEntity()->getInstance();
        $produits = $this->entityManager->getRepository(Produit::class)->findByCalibres($calibre);
        // dd($produits);
        $countProduits = 0;
        $textSuccess = "";
        foreach($produits as $produit){
            $produit->setIsAffiche($state);
            $countProduits++;
        }
        if(!$state){
            $textSuccess = "désaffichés(s)";
        } else {
            $textSuccess = "affichés(s)";
        }
        $this->entityManager->flush();
        $this->addFlash('notice', "En tout <strong>" . $countProduits . " produit(s)</strong> de type calibre : <strong>" . $calibre->getCalibre() . "</strong> ont été " . $textSuccess ." !");
    }

    public function affichageCalibre(AdminContext $context){
        $this->updateAffichageProduits($context, true);

        $url = $this->adminUrl
            //->build()
            ->setController(CalibreCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);

    }

    public function desaffichageCalibre(AdminContext $context){
        $this->updateAffichageProduits($context, false);

        $url = $this->adminUrl
            //->build()
            ->setController(CalibreCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }
    
}
