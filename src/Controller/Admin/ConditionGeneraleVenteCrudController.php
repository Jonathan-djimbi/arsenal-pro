<?php

namespace App\Controller\Admin;

use App\Entity\ConditionGeneraleVente;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;

class ConditionGeneraleVenteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConditionGeneraleVente::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre','Titre'),
            TextEditorField::new('regle','Réglement'),
        ];
    }

}
