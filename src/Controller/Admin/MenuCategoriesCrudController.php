<?php

namespace App\Controller\Admin;

use App\Entity\MenuCategories;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MenuCategoriesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MenuCategories::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['type' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ChoiceField::new('type','Menu')->setChoices([
                'Armes règlementées' => 'Armes règlementées',
                'Armes de chasse' => 'Armes de chasse',
                'Armes de loisirs' => 'Armes de loisirs',
                'Armes de défense' => 'Armes de défense',
                'Accessoires' => 'Accessoires',
                'Optique et électronique' => 'Optique et électronique',
                'Munitions et rechargement' => 'Munitions et rechargement',
                'Pièces détachées' => 'Pièces détachées',
                "Forces de l'ordre" => "Forces de l'ordre"
            ])->hideWhenUpdating(),
            AssociationField::new('subCategory','Sous-catégorie'),
            ArrayField::new('famille','Sous-sous-catégories (utilisez des catégories existantes dans la BDD)'),
        ];
    }
    
}
