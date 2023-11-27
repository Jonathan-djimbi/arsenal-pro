<?php

namespace App\Controller\Admin;

use App\Entity\VenteFlash;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;

class VenteFlashCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VenteFlash::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('pid','Produit')->setFormTypeOption('disabled','disabled')->hideWhenCreating(),
            DateTimeField::new('temps','La date limite de la vente flash'),
            MoneyField::new('new_price','Prix VENTE FLASH')->setCurrency('EUR'),
            BooleanField::new('is_affiche','Afficher dans la page nos vente flash ?'),
        ];
    }
    
}
