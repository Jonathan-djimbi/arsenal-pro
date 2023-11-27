<?php

namespace App\Controller\Admin;

use App\Entity\Taille;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TailleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Taille::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('taille'),
        ];
    }
    
}
