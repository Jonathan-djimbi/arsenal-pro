<?php

namespace App\Controller\Admin;

use App\Entity\Adress;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AdressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Adress::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'edit', title: "Modifier l'adresse d'un client");
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('lastname','Nom de famille'),
            TextField::new('company','Société'),
            TextField::new('adress','Adresse'),
            NumberField::new('postal', 'Code postale'),
            TextField::new('city','Ville'),
            NumberField::new('phone','Numéro de téléphone'),
            CountryField::new('country','Pays'),
        ];
    }
    
}
