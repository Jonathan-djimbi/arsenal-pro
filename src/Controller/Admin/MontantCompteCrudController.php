<?php

namespace App\Controller\Admin;

use App\Entity\CarteFidelite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MontantCompteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CarteFidelite::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE); //NE PEUT NI EFFACER ET NI CREER

    }

    public function configureFields(string $pageName): iterable
    {
        return [
            MoneyField::new('sommeCompte','Montant du compte client en euros')->setCurrency('EUR'),
        ];
    }
    
}
