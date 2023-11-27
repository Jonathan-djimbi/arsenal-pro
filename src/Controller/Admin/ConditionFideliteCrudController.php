<?php

namespace App\Controller\Admin;

use App\Entity\ConditionFidelite;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ConditionFideliteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConditionFidelite::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre','Titre'),
            TextEditorField::new('conditions','Conditions'),
        ];
    }
    
}
