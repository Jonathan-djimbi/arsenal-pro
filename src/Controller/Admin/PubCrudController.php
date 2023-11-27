<?php

namespace App\Controller\Admin;

use App\Entity\Pub;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;

class PubCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Pub::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            ImageField::new('image','Illustration de la pub (bannière : taille recommandée pas encore définie)')
                ->setFormType(FileUploadType::class)
                    ->setBasePath('uploads/nos-pubs')
                    ->setUploadDir('public/uploads/nos-pubs')
                    ->setUploadedFileNamePattern('[name].[extension]')
                    ->setFormTypeOptions(['attr' => [
                        'accept' => 'image/*'
                        ]
                    ])->setRequired(false),
            BooleanField::new('imageAffiche','Affichée ?'),
            UrlField::new('url','Lien vers une page')->setRequired(false),
            TextField::new('texte','Titre de la pub')->setRequired(false),
            ColorField::new('texteCouleur','Couleur du titre')->setRequired(false),
            ChoiceField::new('section')->setChoices([
                'Pub petit cadre' => 1,
                'Bannière' => 2,
            ])->setRequired(true),
        ];
    }
    
}
