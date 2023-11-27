<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Entity\SubCategory;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class SubCategoryCrudController extends AbstractCrudController
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
        return SubCategory::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $afficherProduits = Action::new('affichageSubCategory', 'Afficher produits', 'fas fa-eye')->linkToCrudAction('affichageSubCategory');
        $deafficherProduits = Action::new('desaffichageSubCategory', 'Désafficher produits', 'fas fa-eye-slash')->linkToCrudAction('desaffichageSubCategory');

        return $actions
            ->add('index', $afficherProduits)
            ->add('index', $deafficherProduits);

    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->hideWhenCreating(),
            TextField::new('name','Sous-catégorie'),
        ];
    }
    public function updateAffichageProduits($context, $state){
        $subCategory = $context->getEntity()->getInstance();
        $produits = $this->entityManager->getRepository(Produit::class)->findBySubCategory($subCategory);
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
        $this->addFlash('notice', "En tout <strong>" . $countProduits . " produit(s)</strong> de la sous-catégorie : <strong>" . $subCategory->getName() . "</strong> ont été " . $textSuccess ." !");
    }

    public function affichageSubCategory(AdminContext $context){
        $this->updateAffichageProduits($context, true);

        $url = $this->adminUrl
            //->build()
            ->setController(SubCategoryCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);

    }

    public function desaffichageSubCategory(AdminContext $context){
        $this->updateAffichageProduits($context, false);

        $url = $this->adminUrl
            //->build()
            ->setController(SubCategoryCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }


}
