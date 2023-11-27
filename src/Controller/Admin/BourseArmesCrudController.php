<?php

namespace App\Controller\Admin;

use App\Entity\BourseArmes;
use App\Entity\Produit;
use App\Service\FluctuationBourseArmesService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class BourseArmesCrudController extends AbstractCrudController
{

   private AdminUrlGenerator $adminUrlGenerator;
   private EntityRepository $entityRepository;
   private FluctuationBourseArmesService $bourseArmes;
   private EntityManagerInterface $entityManager;

   public function __construct(EntityManagerInterface $entityManager, EntityRepository $entityRepository, AdminUrlGenerator $adminUrlGenerator, FluctuationBourseArmesService $bourseArmes)
   {
       $this->entityManager = $entityManager;
       $this->adminUrlGenerator = $adminUrlGenerator;  
       $this->entityRepository = $entityRepository;
       $this->bourseArmes = $bourseArmes;
   }

    public static function getEntityFqcn(): string
    {
        return BourseArmes::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'index', title: "Les produits en bourse aux munitions")
        ->setPageTitle(pageName: 'new', title: "Ajouter un produit en bourse");
    }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            // it's equivalent to adding this inside the <head> element:
            // <script src="{{ asset('...') }}"></script>
            ->addHtmlContentToBody('<script src="/assets/js/c0259a756f9e68d046a64d68e6d54b.js"></script>')
            ->addHtmlContentToBody('<!-- generated at '.time().' -->');
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('refreshPrixBourse', 'Rafraîchir la courbe', 'fa-solid fa-arrows-rotate text-primary')->linkToCrudAction('refreshPrixBourse');
        $updatePrix = Action::new('prixEnPrixFinal', 'Mettre le prix en prix final bourse', 'fa-solid fa-euro-sign text-success')->linkToCrudAction('prixEnPrixFinal');

        return $actions
        ->add('index', $updatePrix)
        ->add('index', $updatePreparation);
            
    }
    
    public function prixEnPrixFinal(AdminContext $context){
        $produitUpdate = $context->getEntity()->getInstance();
        $produit = $this->entityManager->getRepository(Produit::class)->findOneById($produitUpdate->getPid());
        $produit->setPricepromo($produitUpdate->getPrixFinal());
        $this->entityManager->flush();
        $this->addFlash('notice', "<span style='color:green;'><strong>Le prix du produit " .  $produit->getName() ." a été mise à jour par le <u>prix final</u>.</strong></span>");
        $url = $this->adminUrlGenerator
            ->setController(BourseArmesCrudController::class)
            ->setAction('index')
            ->setEntityId($produitUpdate->getId())
            ->set('query', $produitUpdate->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    public function refreshPrixBourse(AdminContext $context){ //bouton refresh prix (génére un nouveau prix)
        $produitUpdate = $context->getEntity()->getInstance();
        $this->bourseArmes->updatePrixUneArme($produitUpdate, rand($produitUpdate->getQuantiteMax(),$produitUpdate->getQuantiteMax()+50));
        $this->entityManager->flush();
        
        $url = $this->adminUrlGenerator
            ->setController(BourseArmesCrudController::class)
            ->setAction('index')
            ->setEntityId($produitUpdate->getId())
            ->set('query', $produitUpdate->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->hideWhenCreating()->hideWhenUpdating(),
            AssociationField::new('pid','Sélectionner un produit'),
            NumberField::new('quantite_max','Quantite minimum pour acheter')->setRequired(true), //à quelle quantite on peut procurer le produit 
            BooleanField::new('is_affiche','Visible ou non ?'),
            DateTimeField::new('date_limite','La date limite pour la bourse aux armes')->setRequired(true),
            MoneyField::new('prix_final','Prix final')->setCurrency('EUR')->setRequired(true),
            ArrayField::new('prix_array','Prix évolutif')->hideOnIndex()->hideWhenCreating()->hideWhenUpdating(),
        ];
    }
    
}
