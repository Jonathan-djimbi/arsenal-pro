<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Entity\VenteFlash;
use App\Service\VenteFlashProduitService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;

class ProduitCrudController extends AbstractCrudController
{
   private AdminUrlGenerator $adminUrlGenerator;
   private EntityRepository $entityRepository;
   private VenteFlashProduitService $venteFlashProduit;
   private EntityManagerInterface $entityManager;

   public function __construct(EntityManagerInterface $entityManager, EntityRepository $entityRepository, AdminUrlGenerator $adminUrlGenerator, VenteFlashProduitService $venteFlashProduit)
   {
       $this->entityManager = $entityManager;
       $this->adminUrlGenerator = $adminUrlGenerator;  
       $this->entityRepository = $entityRepository;
       $this->venteFlashProduit = $venteFlashProduit;
   }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            // it's equivalent to adding this inside the <head> element:
            // <script src="{{ asset('...') }}"></script>
            ->addHtmlContentToBody('<script src="/assets/js/customscript.js"></script>')
            ->addHtmlContentToBody('<script src="/assets/js/0a598c254d68ea67f67c67b12d280f0.js"></script>')
            ->addHtmlContentToBody('<script src="/assets/js/c0259a756f9e68d046a64d68e6d54b.js"></script>')
            ->addHtmlContentToBody('<!-- generated at '.time().' -->');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'index', title: "Les produits")
        ->setPageTitle(pageName: 'new', title: "Ajouter un nouveau produit")
        ->setDefaultSort(['id' => 'DESC']); //Ordre décroissant sur easyadmin en SQL
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder //REQUETE SQL
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    
        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere('entity.category != 7'); //ne pas afficher les prestations
    
        return $response;
    }

    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('setVenteFlashProduit', 'ACTIVER/DESACTIVER VENTE FLASH', 'fa-solid fa-ticket text-danger')->linkToCrudAction('setVenteFlashProduit');
        $updateVenteFlash = Action::new('modifsFrer', 'Editer VENTE FLASH', 'fa-solid fa-ticket')->linkToCrudAction('modifsFrer');
        $setDupliquerDernierProduit = Action::new('setDupliquerDernierProduit', 'Dupliquer', 'fa-regular fa-copy')->linkToCrudAction('setDupliquerDernierProduit');

        return $actions
            ->add('index', $updateVenteFlash)
            ->add('index', $updatePreparation)
            ->add('index', $setDupliquerDernierProduit);
            
    }

    public function setVenteFlashProduit(AdminContext $context){
        $produit = $context->getEntity()->getInstance();  
        if(!$produit->isIsForcesOrdre() || $produit->getPriceFDO() == null){
            $this->venteFlashProduit->setVenteFlashAuProduit($produit); //mise en vente flash
            $this->entityManager->flush(); //MAJ BDD
        } else {
            $this->addFlash('notice', "<span style='color:red;'><strong>Les produits FDO ne peuvent pas être en <u>ventes flash pour l'instant</u>.</strong></span>");
        }
        $url = $this->adminUrlGenerator
        ->unsetAll()
        ->setController(ProduitCrudController::class)
        ->setAction('index')
        ->setEntityId($produit->getId())
        ->set('query', $produit->getSlug())
        ->generateUrl();
        return $this->redirect($url);        
    }
    
    public function modifsFrer(AdminContext $context){ //modifier VENTE FLASH (date)
        $produit = $context->getEntity()->getInstance();  
        $venteFlashExistant = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(['pid' => $produit->getId()]); //vérifier si un produit est ou était déjà en vente flash
        if($venteFlashExistant){ //si existant alors on peut le modifier

            $url = $this->adminUrlGenerator
                ->setController(VenteFlashCrudController::class)
                ->setAction('edit')
                ->setEntityId($venteFlashExistant->getId())
                ->set('query', $produit->getSlug())
                ->generateUrl();
            return $this->redirect($url);

        } else { //si pas existant, on ne pourra pas modifier quelque chose de non existant
            $this->addFlash('notice', "<span style='color:orange;'><strong>Ce produit n'est pas référencé en tant que <u>VENTE FLASH</u>.</strong></span>");
            $url = $this->adminUrlGenerator
                ->setController(ProduitCrudController::class)
                ->setAction('index')
                ->setEntityId($produit->getId())
                ->set('query', $produit->getSlug())
                ->generateUrl();
             return $this->redirect($url);
        }
    }
    public function setDupliquerDernierProduit(Request $request, AdminContext $context){ //duplication de produit avec le minimum de content obligatoire
        $produit = $context->getEntity()->getInstance();
        $entity = new Produit();

        $entity->setName($produit->getName());
        $entity->setMarque($produit->getMarque());
        $entity->setSubtitle($produit->getSubtitle());
        $entity->setSlug($produit->getSlug() ."-". substr(uniqid(),3,5)); //lien unique pour pas que ça fasse des conflits
        $entity->setIllustration($produit->getIllustration());
        $entity->setDescription($produit->getDescription());
        $entity->setCaracteristique($produit->getCaracteristique());
        $entity->setIsAffiche($produit->getIsAffiche());
        $entity->setIsBest($produit->isIsBest());
        $entity->setIsOccassion($produit->getIsOccassion());
        $entity->setIsForcesOrdre($produit->isIsForcesOrdre());
        $entity->setPrice($produit->getPrice());
        $entity->setPricePromo($produit->getPricePromo());
        $entity->setPriceFDO($produit->getPriceFDO());
        $entity->setMasse($produit->getMasse());
        $entity->setSubCategory($produit->getSubCategory());
        $entity->setCategory($produit->getCategory());
        $entity->setFamille($produit->getFamille());
        $entity->setQuantite($produit->getQuantite());
        $entity->setCalibres($produit->getCalibres());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->addFlash('notice', "<span style='color:orange;'><strong>Le produit a été <u>dupliqué</u>.</strong></span>");

        $url = $this->adminUrlGenerator->setController(ProduitCrudController::class)->setAction(Action::INDEX)->setEntityId($entity->getId())->generateUrl();

        return $this->redirect($url);
   }
    
    public function configureFields(string $pageName): iterable
    {

       return [
           BooleanField::new('isVenteFlash', '')->setTemplatePath('admin/fields/produits/notif_vente_flash.html.twig')->hideOnForm()->hideOnDetail()->hideWhenCreating(), //affiche dans le index si C'EST EN VENTE FLASH
           IdField::new('id','ID produit')->setFormTypeOption('disabled','disabled')->hideWhenCreating(),
           TextField::new('name','Titre'),
           TextField::new('reference','Reférence produit'),
           TextField::new('referenceAssociation','Association produit')->setRequired(false)->hideOnIndex(),
           AssociationField::new('fournisseurs','Fournisseur')->setRequired(false),
           TextField::new('codeRga','Code RGA')->hideOnIndex(),
           AssociationField::new('marque','Marque'),
           SlugField::new('slug','Lien URL')->setTargetFieldName('name'),
           
            ImageField::new('illustration')
               ->setBasePath('uploads/')
               ->setUploadDir('public/uploads')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
            ImageField::new('illustrationun','Deuxième photo')->hideOnIndex()
               ->setBasePath('uploads/')
               ->setUploadDir('public/uploads')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
            ImageField::new('illustrationdeux','Troisième photo')->hideOnIndex()
               ->setBasePath('uploads/')
               ->setUploadDir('/public/uploads')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
            ImageField::new('illustrationtrois','Quatrième photo')->hideOnIndex()
               ->setBasePath('uploads/')
               ->setUploadDir('public/uploads')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
            ImageField::new('illustrationquatre','Cinquième photo')->hideOnIndex()
               ->setBasePath('uploads/')
               ->setUploadDir('public/uploads')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
           TextField::new('subtitle'),
           TextareaField::new('description'),
           TextareaField::new('caracteristique','Caractéristiques'),
           AssociationField::new('calibres','Calibre')->setRequired(false)->hideOnIndex(), 
           AssociationField::new('taille','Taille/type')->setRequired(false)->hideOnIndex(),                   
           BooleanField::new('isAffiche','Afficher ?'),
           BooleanField::new('isBest'),
           BooleanField::new('isDegressif','Dégressif ?'),
           NumberField::new('munitionNbBoite','Nombre de munitions par boîte')->setRequired(false)->hideOnIndex(),
           BooleanField::new('isSuisse','Suisse ?')->hideOnIndex(),
           BooleanField::new('isOccassion','Occasion ?'),
           BooleanField::new('isForcesOrdre','Afficher dans Forces de l`ordre ?')->hideOnIndex(),
           BooleanField::new('isCarteCadeau','Ce produit est-il une carte cadeau ?')->hideOnIndex(),
           MoneyField::new('price','Prix')->setCurrency('EUR'),
           MoneyField::new('pricepromo','Prix promo')->setCurrency('EUR')->hideOnIndex(),
           MoneyField::new('priceFDO',"Prix forces de l'ordre (laisser la case à cocher FDO décoché si vous voulez afficher les prix pour les FDO et non FDO)")->setCurrency('EUR')->hideOnIndex(),
           NumberField::new('masse','Poids (kg)')->hideOnIndex(),
           AssociationField::new('category','Catégorie')->setCssClass('category_ea'),
           AssociationField::new('subCategory','Sous-catégorie')->setCssClass('category_ea')->setRequired(false),
           AssociationField::new('famille','Sous-catégorie 2 ou famille')->setCssClass('category_ea')->setRequired(false),
           IdField::new('accessoireLieA','Lier cet accessoire à un ID produit, (exemple pour un produit : 10, pour plusieurs produits : 10,12,13...)')->setCssClass('accessoireLie_ea')->setRequired(false)->hideOnIndex(),
           AssociationField::new('mainportee','Main')->setCssClass('main_ea')->setRequired(false)->hideOnIndex(),
           NumberField::new('quantite','Quantité en stock'),
       ];
    }
}
