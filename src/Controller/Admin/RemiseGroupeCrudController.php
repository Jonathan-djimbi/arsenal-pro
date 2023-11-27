<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use App\Entity\RemiseGroupe;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class RemiseGroupeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $em;
    private AdminUrlGenerator $aurl;

    public function __construct(EntityManagerInterface $em, AdminUrlGenerator $aurl)
    {
        $this->em = $em;
        $this->aurl = $aurl;
    }
    public static function getEntityFqcn(): string
    {
        return RemiseGroupe::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $miseAJourProduits = Action::new('majProduitsPrix', 'Mise à jour des prix', 'fas fa-box-open')->linkToCrudAction('majProduitsPrix');

        return $actions
        ->add('index', $miseAJourProduits);
    }

    public function configureAssets(Assets $assets): Assets {
        return $assets
            ->addHtmlContentToBody('<script src="/assets/js/0a598c254d68ea67f67c67b12d280f0.js"></script>')
            ->addHtmlContentToBody('<!-- '.time().' -->');
    }

    public function majProduitsPrix(AdminContext $context){
        $maj = $context->getEntity()->getInstance();
        // if(!$maj->isDesactive()){
            // $produits = $this->em->getRepository(Produit::class)->findBy(['subCategory' => $maj->getSubCategories(), 'marque' => $maj->getMarques()]);
            $produits = $this->em->getRepository(Produit::class)->findAll();
            foreach($produits as $produit){
                if(($produit->getSubCategory() == $maj->getSubCategories() && $maj->getSubCategories() !== null) || ($produit->getMarque() == $maj->getMarques() && $maj->getMarques() !== null) || ($produit->getFournisseurs() == $maj->getFournisseur() && $maj->getFournisseur() !== null)){
                    $produit->setPricePromo($produit->getPrice() * (1 - ($maj->getRemise() /100))); //application de la remise
                }
            }
            $maj->setFaitLe(new \DateTimeImmutable()); //dernière MAJ fait le
            // dd($produits);
            $this->em->flush();
            
            $this->addFlash('notice', "<span style='color:green;'><strong>Le pourcentage de remise à  ".$maj->getRemise()."% a été <u>appliquée</u>.</strong></span>");
        // } else {
        //     $this->addFlash('notice', "<span style='color:red;'><strong>Remise désactivée. vVeuillez réactiver la remise depuis cette page.</strong></span>");
        // }
        $url = $this->aurl
            //->build()
            ->setController(RemiseGroupeCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('remise','Remise en %'),
            AssociationField::new('fournisseur','Fournisseurs')->setRequired(false),
            AssociationField::new('subCategories', 'Catégories')->setRequired(false),
            AssociationField::new('marques','Marques')->setRequired(false),
            ChoiceField::new('priority',"Priorité d'impacte (important pour l'insertion auto)")->setChoices([
                'Priorité basse' => 0,
                'Priorité élevée' => 1,
            ]),
            BooleanField::new('desactive', 'Désactivée ? (pour insertion auto)'),
            DateTimeField::new('faitLe', 'Dernière application manuelle')->setFormTypeOption('disabled','disabled')->hideWhenUpdating()->hideWhenCreating(),
        ];
    }
    
}
