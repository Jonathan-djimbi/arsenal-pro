<?php

namespace App\Controller\Admin;

use App\Entity\CarteCadeau;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CarteCadeauCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CarteCadeau::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            MoneyField::new('price','Montant de la carte')->setCurrency('EUR'),
            TextField::new('code','Code'),
            AssociationField::new('claimedBy','Utilisé par'),
            DateTimeField::new('generatedAt', 'Carte généré le'),
            DateTimeField::new('usedAt','Code utilisé le'),
        ];
    }
    
}
