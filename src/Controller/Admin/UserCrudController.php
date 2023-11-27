<?php

namespace App\Controller\Admin;

use App\Controller\AccountController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Translation\TranslatableMessage;

class UserCrudController extends AbstractCrudController
{

    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private AccountController $compte;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator, AccountController $compte)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;  
        $this->compte = $compte;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id','ID User')->setFormTypeOption('disabled','disabled')->hideWhenCreating(),
            TextField::new('firstname','Prénom')->setFormTypeOption('disabled','disabled'),
            TextField::new('lastname','Nom'),
            TextField::new('email','Email'),
            CollectionField::new('carteFidelite','Points fidélités')->setTemplatePath('admin/fields/fidelite_account/index.html.twig')->hideOnForm(),
            CollectionField::new('carteFidelite','Montant compte')->setTemplatePath('admin/fields/fidelite_account/montant_compte.html.twig')->hideOnForm(),
            CollectionField::new('adresses')->setTemplatePath('admin/fields/adresses_account/adresses_account.html.twig')->hideOnIndex()->hideOnForm(), //jointure pour ensuite faire un for dans le HTML et afficher
            CollectionField::new('adresses')->setTemplatePath('admin/fields/adresses_account/index.html.twig')->hideOnDetail()->hideOnForm(),
            CollectionField::new('professionnelAssociationComptes','Professionnel/Association')->setTemplatePath('admin/fields/professionnel_association_account/index.html.twig')->hideOnForm()->hideOnIndex(),
            CollectionField::new('professionnelAssociationComptes','FDO')->setTemplatePath('admin/fields/fdo/index.html.twig')->hideOnForm()->hideOnIndex(),
        ];
    }
    
    public function configureActions(Actions $actions): Actions
    {
        // $supprimeCompte = Action::new('supprimeCompte', 'Supprimer le compte', 'fas fa-box-open')->linkToCrudAction('supprimeCompte');

        return $actions
            ->add('index', 'detail')    
            // ->add('detail', $supprimeCompte)
            ->remove(Crud::PAGE_INDEX, Action::DELETE) 
            ->remove(Crud::PAGE_DETAIL, Action::DELETE); //NE PEUT NI EDITER ET NI CREER


    }

}
