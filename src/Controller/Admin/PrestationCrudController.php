<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PrestationCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityRepository $entityRepository;

    public function __construct(EntityManagerInterface $entityManager, EntityRepository $entityRepository, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;  
        $this->entityRepository = $entityRepository;
    }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            ->addHtmlContentToBody('<script src="/assets/js/customscript.js"></script>')
            ->addHtmlContentToBody('<script src="/assets/js/c0259a756f9e68d046a64d68e6d54b.js"></script>')
            ->addHtmlContentToBody('<!-- generated at '.time().' -->');
    }

    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'index', title: "Les prestations")
        ->setPageTitle(pageName: 'new', title: "Ajouter une nouvelle prestation")
        ->setDefaultSort(['id' => 'DESC']); //Ordre décroissant sur easyadmin en SQL
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder //REQUETE SQL
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    
        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere('entity.category = 7'); //afficher uniquement les prestations
    
        return $response;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name','Intitulé de la prestation'),
            SlugField::new('slug','Lien URL')->setTargetFieldName('name')->hideOnIndex(),
            TextField::new('description','Description'),
            MoneyField::new('price','Tarif')->setCurrency('EUR'),

            AssociationField::new('marque','Marque')->hideOnIndex(),
           
            ImageField::new('illustration')
               ->setBasePath('uploads/')
               ->setUploadDir('public/uploads')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false)->hideOnIndex(),
           TextField::new('subtitle')->hideOnIndex(),
           BooleanField::new('isAffiche','Afficher ?'),
           AssociationField::new('category','Catégorie')->setCssClass('category_ea')->hideOnIndex(),
           NumberField::new('quantite','Quantité : Mettez 9999 pour la préstation')->hideOnIndex(),
        ];
    }
    
}
