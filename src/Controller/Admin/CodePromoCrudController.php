<?php

namespace App\Controller\Admin;

use App\Entity\CodePromo;
use App\Entity\HistoriqueCodePromo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class CodePromoCrudController extends AbstractCrudController
{

    private AdminUrlGenerator $adminUrlGenerator;
    private EntityManagerInterface $entityManager;
 
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator){
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;  
    }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            ->addHtmlContentToBody('<script src="/assets/js/customscript.js?v=4"></script>') //notre js
            ->addHtmlContentToBody('<script src="/assets/js/0a598c254d68ea67f67c67b12d280f0.js"></script>')
            ->addHtmlContentToBody('<!-- '.time().' -->');
    }

    public static function getEntityFqcn(): string
    {
        return CodePromo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormOptions(
                ['validation_groups' => ['new']], // Crud::PAGE_NEW
                ['validation_groups' => ['edit']] // Crud::PAGE_EDIT
            )->setDefaultSort(['id' => 'DESC']); //montrer les derniers en premier
    }
    

    public function configureActions(Actions $actions): Actions
    {
        $supprime = Action::new('deleteCodePromo', 'Supprimer', 'text-warning')->linkToCrudAction('deleteCodePromo');

        return $actions
        ->remove(Crud::PAGE_INDEX, Action::DELETE) //On enleve le bouton supprimer de base car ça ne supprime pas les enfants du parent efficacement
        ->remove(Crud::PAGE_DETAIL, Action::DELETE) 
        ->add('detail', $supprime)
        ->add('index', $supprime);
    }

    public function deleteCodePromo(AdminContext $context){
        $codepromo = $context->getEntity()->getInstance();  
        $nom = $codepromo->getCode();
        $historiquePromo = $this->entityManager->getRepository(HistoriqueCodePromo::class)->findBy(['codePromo' => $codepromo->getId()]);
        if(count($historiquePromo) > 0){
            foreach($historiquePromo as $histo){
                $this->entityManager->remove($histo);
            }
        } 
        $this->entityManager->remove($codepromo);
        $this->entityManager->flush(); //MAJ BDD
        
        $this->addFlash('notice', "<span style='color:orange;'><strong>Le code promo " . $nom ." a été <u>supprimé</u>.</strong></span>");
        
        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(CodePromoCrudController::class)->setAction('index')->generateUrl();
        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->hideWhenCreating(),
            TextField::new('code'),
            NumberField::new('pourcentage','Remise en % (de 0.1% à 100%)')->setCssClass("pourcentage_promo_ea"),
            MoneyField::new('montantRemise','Remise en EUROS')->setCurrency("EUR")->setRequired(false)->setCssClass("montant_remise_promo_ea"),
            DateTimeField::new('temps','Validité du code promo'),
            MoneyField::new('maxAmount','Valeur minimum autorisée panier')->setCurrency("EUR")->setRequired(false)->hideOnIndex(),
            // AssociationField::new('user','Uniquement utilisable par le compte')->setRequired(false),
            ArrayField::new('users','Uniquement utilisable par le(s) compte(s)')->hideOnIndex()->setRequired(false),
            ArrayField::new('produits','Liste produits ID')->hideOnIndex()->setRequired(false),
            ArrayField::new('subCategories','Liste sous-catégories ID (par exemple ID = 137 pour Carabines semi-automatiques)')->hideOnIndex()->setRequired(false),
            NumberField::new('utilisationMax',"Nombre d'utilisation maximale du code promo")->hideOnIndex()->setRequired(false),
            NumberField::new('utilisation',"Nombre d'utilisation")->hideWhenUpdating()->hideWhenCreating(),
            NumberField::new('nbUtilisationMaxUser', "Nombre d'utilisation maximale par UTILISATEUR (champs vide = 1 utilisation)")->hideOnIndex()->setRequired(false),
        ];
    }

}
