<?php

namespace App\Controller\Admin;

use App\Entity\CarteFidelite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CarteFideliteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CarteFidelite::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'edit', title: "Modifier les points d'un client");
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_DETAIL, Action::DELETE) //NE PEUT NI EFFACER ET NI CREER
            ->add('index', 'detail');
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('user.fullname','Client')->setFormTypeOption('disabled','disabled'),
            NumberField::new('points','Le solde de points'),
        ];
    }
    
}
