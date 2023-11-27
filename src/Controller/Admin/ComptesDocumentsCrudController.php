<?php

namespace App\Controller\Admin;

use App\Controller\CompteDocumentsController;
use App\Entity\ComptesDocuments;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ComptesDocumentsCrudController extends AbstractCrudController
{
    private $comptedocuments;
    private $adminUrlGenerator;

    public function __construct(CompteDocumentsController $comptedocuments, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->comptedocuments = $comptedocuments;
        $this->adminUrlGenerator = $adminUrlGenerator;  

    }

    public static function getEntityFqcn(): string
    {
        return ComptesDocuments::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']); //Ordre décroissant sur easyadmin en SQL
    }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            // it's equivalent to adding this inside the <head> element:
            // <script src="{{ asset('...') }}"></script>
            ->addHtmlContentToBody('<script src="/assets/js/c0259a756f9e68d046a64d68e6d54b.js"></script>')
            ->addHtmlContentToBody('<!-- generated at '.time().' -->');
    }

    public function configureActions(Actions $actions): Actions
    {
        $deleteDocuments = Action::new('deleteDocuments', 'Supprimer', 'fas fa-trash-can text-danger')->linkToCrudAction('deleteDocuments');
        $validerDocuments = Action::new('validerDocuments', 'Valider', 'fas fa-check text-success')->linkToCrudAction('validerDocuments');

        return $actions
            ->add('index', $deleteDocuments)
            ->add('index', $validerDocuments)
            ->add('index','detail')
            ->remove(Crud::PAGE_INDEX, Action::NEW) //NE PEUT PAS CREER
            ->remove(Crud::PAGE_INDEX, Action::DELETE) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_DETAIL, Action::DELETE); //NE PEUT NI EFFACER ET NI CREER
    }

    public function deleteDocuments(AdminContext $context){
        $id = $context->getEntity()->getInstance();

        $this->comptedocuments->deleteDocumentsCompte($id);

        $this->addFlash('notice', "<span style='color:green;'><strong>Les documents de ".$id->getUser()->getFullname()." sont supprimés.</strong></span>");

        $url = $this->adminUrlGenerator->setController(ComptesDocumentsCrudController::class)->setAction(Action::INDEX)->setEntityId($id->getId())->generateUrl();
        return $this->redirect($url);
    }

    public function validerDocuments(AdminContext $context){
        $doc = $context->getEntity()->getInstance();

        return $this->redirectToRoute('app_library_account_documents_check', ['userId' => $doc->getId()]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
                IdField::new('id')->setFormTypeOption('disabled','disabled'), //pas touche au ID
                TextField::new('user.fullname', 'Utilisateur')->setFormTypeOption('disabled','disabled'),
                ImageField::new('cartId','CNI')
                ->setFormType(FileUploadType::class)
                    ->setBasePath('uploads/documents')
                    ->setUploadDir('public/uploads/documents')
                    ->setUploadedFileNamePattern('[randomhash].[extension]')
                    ->setFormTypeOptions(['attr' => [
                        'accept' => 'image/*'
                        ]
                    ])->setRequired(true)->hideWhenUpdating(),
                DateField::new('cartIdDate','Date de validité')->hideWhenUpdating(),

                ImageField::new('justificatifDomicile','Si justificat domicile -3 mois')
                ->setBasePath('uploads/documents')
                ->setUploadDir('public/uploads/documents')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions(['attr' => [
                    'accept' => 'image/*'
                    ]
                ])->setRequired(false)->hideWhenUpdating(),
                ImageField::new('licenceTirId','Licence de tir')
                    ->setBasePath('uploads/documents')
                    ->setUploadDir('public/uploads/documents')
                    ->setUploadedFileNamePattern('[randomhash].[extension]')
                    ->setFormTypeOptions(['attr' => [
                        'accept' => 'image/*'
                        ]
                    ])->setRequired(true)->hideWhenUpdating(),
                DateField::new('licenceTirIdDate','Date de validité')->hideWhenUpdating(),
                ImageField::new('certificatMedicalId','Certificat médical')
                    ->setBasePath('uploads/documents')
                    ->setUploadDir('public/uploads/documents')
                    ->setUploadedFileNamePattern('[randomhash].[extension]')
                    ->setFormTypeOptions(['attr' => [
                        'accept' => 'image/*'
                        ]
                    ])->setRequired(true)->hideWhenUpdating(),
                DateField::new('certificatMedicalIdDate','Date de validité')->hideWhenUpdating(),
                ImageField::new('cartPoliceId','Carte de police')
                ->setBasePath('uploads/documents')
                ->setUploadDir('public/uploads/documents')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions(['attr' => [
                    'accept' => 'image/*'
                    ]
                    ])->setRequired(true)->hideWhenUpdating(),
                // DateField::new('cartPoliceIdDate','Date de validité')->hideWhenUpdating(),
                TextField::new('numero_sea','Numéro SIA'),
                BooleanField::new('cartIdcheck','Vérification CNI')->hideOnIndex(),
                BooleanField::new('licenceTirIdcheck','Vérification licence de tir')->hideOnIndex(),
                BooleanField::new('certificatMedicalIdcheck', 'Vérification certificat médical')->hideOnIndex(),
                BooleanField::new('cartPoliceIdcheck', 'Vérification carte police')->hideOnIndex(),
                BooleanField::new('numero_sea_check', 'Vérification numéro SIA')->hideOnIndex(),
                BooleanField::new('vosdocumentsverifies', 'Ce profil a été vérifié ?')->setFormTypeOption('disabled','disabled'),
        ];
    }
}
