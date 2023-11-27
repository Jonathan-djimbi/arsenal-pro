<?php

namespace App\Controller\Admin;

use App\Entity\Marque;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MarqueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Marque::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->hideWhenCreating()->hideOnIndex(),
            TextField::new('name'),
            ImageField::new('photo','Illustration de la marque')
               ->setBasePath('uploads/marques')
               ->setUploadDir('public/uploads/marques')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
        ];
    }
    
}
