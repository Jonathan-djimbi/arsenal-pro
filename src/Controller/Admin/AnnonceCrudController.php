<?php

namespace App\Controller\Admin;

use App\Entity\Annonce;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class AnnonceCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Annonce::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index', 'detail')
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_DETAIL, Action::DELETE); 
    }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            ->addHtmlContentToBody('<script src="/assets/js/customscript.js"></script>')
            ->addHtmlContentToBody('<script src="/assets/js/c0259a756f9e68d046a64d68e6d54b.js"></script>')
            ->addHtmlContentToBody('<!-- generated at '.time().' -->');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('contenu','Contenu annonce'),
            BooleanField::new('isAffiche', 'Afficher cette annonce ?'),
        ];
    }

}
