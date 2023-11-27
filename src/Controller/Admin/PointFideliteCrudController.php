<?php

namespace App\Controller\Admin;

use App\Entity\PointFidelite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PointFideliteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PointFidelite::class;
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
            MoneyField::new('montant_panier','Incrémentation en euros')->setCurrency('EUR'),
            NumberField::new('point','Incrémentation en point'),
            MoneyField::new('conversion_euro_points','Point en euros')->setCurrency('EUR'),
            NumberField::new('ratio_cde_panier_en_point'),
            NumberField::new('remise','Remise en %')
        ];
    }
    
}
