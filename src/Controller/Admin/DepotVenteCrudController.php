<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Entity\DepotVente;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\MailProduitDelivereService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository as ORMEntityRepository;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use Dompdf\FrameDecorator\Text;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DepotVenteCrudController extends AbstractCrudController
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

    public static function getEntityFqcn(): string
    {
        $order = DepotVente::class;
        return $order;
    }

    public function configureActions(Actions $actions): Actions
    {

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add('index', 'detail');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder //REQUETE SQL
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    
        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere("entity.type = 'depot-vente'"); 
    
        return $response;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'index', title: 'Dépôt vente')
        ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->onlyOnIndex(),
            TextField::new('nom', 'Nom')->setFormTypeOption('disabled','disabled'),
            TextField::new('prenom', 'Prénom')->setFormTypeOption('disabled','disabled'),
            EmailField::new('email', 'Adresse email')->setFormTypeOption('disabled','disabled'),
            TextField::new('phone', 'Téléphone')->setFormTypeOption('disabled','disabled'),
            TextField::new('adresse', 'Adresse')->setFormTypeOption('disabled','disabled'),
            NumberField::new('postal', 'Code postal')->setFormTypeOption('disabled','disabled'),
            DateTimeField::new('faitLe', 'Fait le')->setFormTypeOption('disabled','disabled'),
            
            ImageField::new('photoUn','Première photo')
               ->setBasePath('uploads/depot-ventearmes/')
               ->setUploadDir('public/uploads/depot-ventearmes')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false)->hideWhenUpdating(),
            ImageField::new('photoDeux', 'Deuxième photo')
               ->setBasePath('uploads/depot-ventearmes/')
               ->setUploadDir('public/uploads/depot-ventearmes')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false)->hideWhenCreating(),
            ImageField::new('photoTrois','Troisième photo')
               ->setBasePath('uploads/depot-ventearmes/')
               ->setUploadDir('public/uploads/depot-ventearmes')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
               ImageField::new('photoQuatre','Quatrième photo')->hideOnIndex()
               ->setBasePath('uploads/depot-ventearmes/')
               ->setUploadDir('public/uploads/depot-ventearmes')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
               TextField::new('prixLot','Prix du lot')->setFormTypeOption('disabled','disabled'),
               NumberField::new('nbTotalArme', "Nombre total d'armes")->setFormTypeOption('disabled','disabled')->hideOnIndex(),
               TextField::new('description')->setFormTypeOption('disabled','disabled'),
        ];
    }

}
