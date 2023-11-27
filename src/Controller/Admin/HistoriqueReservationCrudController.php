<?php

namespace App\Controller\Admin;

use App\Entity\HistoriqueReservation;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class HistoriqueReservationCrudController extends AbstractCrudController
{

    private EntityRepository $entityRepository;


    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public static function getEntityFqcn(): string
    {
        return HistoriqueReservation::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilter $filters): QueryBuilder //REQUETE SQL
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    
        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere('entity.state != 0'); //pas afficher réservations non payées
    
        return $response;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(pageName: 'index', title: "Les réservations payées")->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index','detail')
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_INDEX, Action::EDIT) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_DETAIL, Action::DELETE) 
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
          return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->onlyOnIndex(),
            TextField::new('reference')->setFormTypeOption('disabled','disabled'),
            TextField::new('dateAt', 'Passée le'),
            TextField::new('userDetails', 'Utilisateur'),
            MoneyField::new('total', 'Total de la réservation')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée' => 0,
                'Payée' => 1,
                'Réservation acceptée' => 2,
                'Remboursée' => -1
            ]),
            TextField::new('activiteName','Intitulé activité'),
            MoneyField::new('refundAmount','Montant remboursé')->setCurrency('EUR')->hideOnIndex(),
            ChoiceField::new('typeFormation')->setChoices([
                'Fondamental F' => 0,
                'Basic B' => 1,
                'Expert E' => 2,
            ]),
        ];
    }
}
