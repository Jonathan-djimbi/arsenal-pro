<?php

namespace App\Controller\Admin;

use App\Entity\ProfessionnelAssociationCompte;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProfessionnelAssociationCompteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProfessionnelAssociationCompte::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
        ->setPageTitle(pageName: 'edit', title: "Modifier les informations professionnel/association/FDO d'un utilisateur");
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('raisonSocial', 'Raison social'),
            NumberField::new('siret', 'Numéro de SIRET'),
            NumberField::new('noTVA', 'Numéro de la TVA')->setRequired(false),
            ChoiceField::new('typeFDO', 'Type FDO')->setChoices([
                'Armée' => 0,
                'Administration penitentiaire' => 1,
                'Convoyeurs de fonds' => 2,
                'Douanes' => 3,
                "Gendarmerie" => 4,
                "Police" => 5,
                "Police ferroviaire" => 6,
                "Police municipale" => 7,
                "Police nationale" => 8,
            ])->setRequired(false),
            NumberField::new('numeroMatricule', 'Numéro matricule')->setRequired(false),

        ];
    }
    
}
