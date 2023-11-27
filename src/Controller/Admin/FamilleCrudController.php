<?php

namespace App\Controller\Admin;

use App\Entity\Famille;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FamilleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Famille::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->hideWhenCreating(),
            TextField::new('name','Sous-sous-catégorie'),
            ImageField::new('illustration')
               ->setBasePath('uploads/')
               ->setUploadDir('public/uploads/categories/sous-categories')
               ->setUploadedFileNamePattern('[name].[extension]')
               ->setRequired(false),
        // AssociationField::new('category','Catégorie liée à')
        ];
    }
}
