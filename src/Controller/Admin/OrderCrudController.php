<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Service\MailerService;
use App\Controller\OrderValidateController;
use App\Entity\Adress;
use App\Entity\Order;
use App\Entity\Produit;
use App\Repository\OrderRepository;
use App\Service\MailProduitDelivereService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository as ORMEntityRepository;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpClient\HttpClient;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection as CollectionFilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private EntityRepository $entityRepository;
    private MailProduitDelivereService $mailLivraison;
    private OrderValidateController $orderValidated;

    public function __construct(EntityManagerInterface $entityManager, EntityRepository $entityRepository, AdminUrlGenerator $adminUrlGenerator, MailProduitDelivereService $mailLivraison, OrderValidateController $orderValidated)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;  
        $this->entityRepository = $entityRepository;
        $this->mailLivraison = $mailLivraison;
        $this->orderValidated = $orderValidated;
    }

    public static function getEntityFqcn(): string
    {
        $order = Order::class;
        return $order;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, CollectionFilterCollection $filters): QueryBuilder //REQUETE SQL
    {
        parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    
        $response = $this->entityRepository->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $response->andWhere('entity.state != 0'); //pas afficher commandes non payées
    
        return $response;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation', 'Préparation en cours', 'fas fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery', 'Livraison en cours', 'fas fa-truck')->linkToCrudAction('updateDelivery');
        $updateDelivered = Action::new('updateDelivered', 'Livrée', 'fas fa-truck')->linkToCrudAction('updateDelivered');
        $facture = Action::new('getFacture', 'Voir la facture', 'fas fa-folder')->linkToCrudAction('getFacture');
        $refund = Action::new('refund', 'Rembourser', 'fas fa-rotate-right')->linkToCrudAction('refund');
        $reColissimo = Action::new('generateColissimoAgain', 'Regénérer un ticket colissimo', 'fas fa-truck')->linkToCrudAction('generateColissimoAgain');
        $addAgenda = Action::new('addAgenda', 'Email prise RDV', 'fas fa-calendar')->linkToCrudAction('addAgenda');

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_INDEX, Action::EDIT) //NE PEUT NI EFFACER ET NI CREER
            ->remove(Crud::PAGE_DETAIL, Action::DELETE) 
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->add('detail', $facture)
            ->add('detail', $refund)
            ->add('detail', $reColissimo)
            ->add('index', $reColissimo)
            ->add('detail', $addAgenda)
            ->add('index', $addAgenda)
            ->add('index', $facture)
            ->add('detail', $updatePreparation)
            ->add('detail', $updateDelivery)
            ->add('detail', $updateDelivered)
            ->add('index', 'detail');
    }

    public function updatePreparation(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        $this->entityManager->flush();

        $this->addFlash('notice', "<span style='color:green;'><strong>La commande ".$order->getReference()." est bien <u>en cours de préparation</u>.</strong></span>");

        $url = $this->adminUrlGenerator
            //->build()
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function updateDelivery(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(3);
        $this->entityManager->flush();

        $this->addFlash('notice', "<span style='color:orange;'><strong>La commande ".$order->getReference()." est bien <u>en cours de livraison</u>.</strong></span>");

        $url = $this->adminUrlGenerator
            //->build()
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function updateDelivered(AdminContext $context) //quand commande livrée (colissimo par exemple)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(4);
        $this->entityManager->flush();

        $this->mailLivraison->envoiMail($order->getId()); //envoi mail commande livre

        $this->addFlash('notice', "<span style='color:green;'><strong>La commande ".$order->getReference()." est bien en statut <u>livrée</u>.</strong></span>");

        $url = $this->adminUrlGenerator
            //->build()
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function refund(AdminContext $context){
        $commande = $context->getEntity()->getInstance();
        if($commande->getState() === -1){
            $this->addFlash('warning','La commande ' . $commande->getReference() .' a été déjà remboursée.');
            
            $url = $this->adminUrlGenerator
            //->build()
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
        } else {
            return $this->redirectToRoute('app_order_refund', ['id' => $commande->getId(), 'reference' => $commande->getReference()]); 
        }
    }

    public function getFacture(AdminContext $context){
        $order = $context->getEntity()->getInstance();
        $nofacture = $order->getReference(); 
        $noFactureFinal = "facture-";
        for($i = 0; $i < 4; $i++){
            if($i >= 3){ 
                $noFactureFinal .= explode('-', $nofacture)[$i];
            } else {
                $noFactureFinal .= explode('-', $nofacture)[$i] . "-";
            }
        }
        $date = $order->getCreateAt()->format('m-Y');
        return $this->redirectToRoute("app_library_pdf_factures", ["mois" => $date, "src" => $noFactureFinal . ".pdf"]); //factures 100% en PDF
    }

    public function generateColissimoAgain(AdminContext $context){
        $commande = $context->getEntity()->getInstance();
        $masse = 0;
        $prix_total = 0;
        $catB_detection = [];
        $mail = new Mail();
        $url = $this->adminUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        if($commande->getCarrierName() == "COLISSIMO"){ //verif si commande est conforme pour la livraison en COLISSIMO
	        //dd($commande->getDelivry()->getId());
            $acheteur = $this->entityManager->getRepository(Adress::class)->findOneBy(["id" => $commande->getDelivry()->getId()]); //adresse livraison
	 
            foreach($commande->getOrderDetails()->getValues() as $produit){
                $product_object = $this->entityManager->getRepository(Produit::class)->findOneById($produit->getPid()->getId());
                
                if($product_object->getCategory()->getId() === 2){ //si arme de cat B
                    $catB_detection[] = ["id" => $product_object->getId(), "masse" => ($product_object->getMasse() * $produit->getQuantity()), "prix" => ($produit->getPrice() * $produit->getQuantity())]; //produit->getPrice()! !!!!
                }

                if($product_object){
                    if($product_object->getMasse() !== null){ //si pas masse
                        $masse = $masse + ($product_object->getMasse() * $produit->getQuantity()); //poids produit
                    } else {
                        $masse = $masse + 1; //1kg ajouté par défaut
                    }
                    $prix_total = $prix_total + ($produit->getPrice() * $produit->getQuantity()); //prix et quantité enregistré de la commande
                }
            }
            // dd($commande->getUser()->getEmail());
            // dd($masse, $prix_total);
            if($masse > 0){

                if(count($catB_detection) > 0){ //si produit(s) catégorie B détecté(s)
                    $prixTotalCatB = 0;
                    $masseTotalCatB = 0;
                    foreach($catB_detection as $product){
                        $masseTotalCatB = $masseTotalCatB + $product["masse"]; //$masseTotalCatB++ $product->getMasse();
                        $prixTotalCatB = $prixTotalCatB + $product["prix"]; //$masseTotalCatB++ $product->getMasse();
                    }
                    $masseTotalCatB = ($masseTotalCatB * 0.15); //15% de la masse cat B;
                    if($masseTotalCatB > 0){
                        $this->orderValidated->generateTicketColissimo($commande, $acheteur, $mail, $masseTotalCatB, $commande->getReference() . "-CATB", $prixTotalCatB); //faire un deuxième colissimo
                    }
                }
                $this->orderValidated->generateTicketColissimo($commande, $acheteur, $mail, $masse, $commande->getReference(), $prix_total);
            } else {
                $this->addFlash('notice', "<span style='color:red;'><strong>Erreur sur la masse.<u>Masse doit être supérieure à 0kg</u>.</strong></span>");
                return $this->redirect($url);
            }

            $this->addFlash('notice', "<span style='color:green;'><strong>Le colissimo pour la commande ". $commande->getReference()." a bien été <u>regénéré</u>.</strong></span>");

        } else {
            $this->addFlash('notice', "<span style='color:red;'><strong>Cette commande ". $commande->getReference()." n'appartient pas pour la livraison par <u>COLISSIMO</u>.</strong></span>");
        }

        return $this->redirect($url);
    }
    // fonction qui envoie un mail au client pour prendre RDV : https://calendar.app.google/3od3dUBgNQ4nCfbcA
    public function addAgenda(AdminContext $context, MailerService $mailer)
    {
        $order = $context->getEntity()->getInstance();
        $products = $order->getOrderDetails()->getValues();
        $reference = $order->getReference();
        $URL = "https://arsenal-pro.fr";
        $clientEmail = $order->getUser()->getEmail();
        $clientFullName = $order->getUser()->getFirstName() . ' ' . $order->getUser()->getLastName();
        $eventLink = 'https://calendar.app.google/3od3dUBgNQ4nCfbcA';
        $mail = new Mail();
        $subject = 'Rendez-vous retrait armurerie arsenal pro';
        $content = "<section style='font-family: arial; color: black;'>
                        <section style='width: 95%; margin: auto; padding: 15px 0;'>
                            <div>
                                <h3 style='font-weight: normal;'>Numéro de commande : <span style='font-weight: bold;'>" . $reference . "</span></h2>
                                <h3 style='font-weight: normal;'>Bonjour " . $clientFullName . ",</h3>
                                <h3 style='font-weight: normal;'>Vous avez acheté un produit sur notre site et nous vous invitons à planifier un rendez-vous en utilisant le lien ci-dessous  :</h3>
                                </br>
                            </div>
                            <div style='width: auto; padding: 10px; margin: auto; width: 200px; color: white;'>
                                <a style='display: inline-block; padding: 10px 20px; background-color: #07af15; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; text-align: center;' href='" . $eventLink . "'>Prendre rendez-vous</a>
                            </div>
                            <br>";
                    // $content .= "
                    //         <div>
                    //             <h3 style='font-weight: normal;'>Commande :</h3>";
                    //             foreach ($products as $product) {
                    //             $content .= "
                    //                 <div style='display: flex;'>
                    //                     <div style='margin: auto 10px;'>
                    //                         <a style='text-decoration: none;' href='https://arsenal-pro.fr/produit/" . $product->getPid()->getSlug() . "'><img width='100' height='100' style='object-fit: contain; height: 100px !important;' src='" . $URL . "/uploads/" . $product->getPid()->getIllustration() . "' /></a>
                    //                     </div>
                    //                     <div style='margin: auto 10px; width: 100%;'>
                    //                         <b><a href='https://arsenal-pro.fr/produit/" . $product->getPid()->getSlug() . "'>". $product->getPid()->getName() . "</a></b>
                    //                         <p style='font-weight: normal;'>Qté(s) : " . $product->getQuantity() . "</p>
                    //                     </div>                                        
                    //                     <div style='width: 100%; margin: auto 10px;'>
                    //                         <b>" . number_format(($product->getPrice() * $product->getQuantity()) / 100, 2) . "€</b>
                    //                     </div>
                    //                 </div>";                            
                    //             }
                    // $content .= "
                    //         </div>";
                $content .= "<br>
                            <h3 style='font-weight: normal;'>Cordialement,
                            <br>
                            L'équipe Arsenal Pro</h3>
                        </section>
                    </section>";
        // dd($content);
        // $mail->send($clientEmail, $clientFullName, $subject, $content, 4640141); //pour utilisateur
        $mail->send("anthopark0021@gmail.com", $clientFullName, $subject, $content, 4639822);

        // You can add a success flash message here if needed

        // Redirect back to the admin page
        $url = $this->adminUrlGenerator
        ->setController(OrderCrudController::class)
        ->setAction('index')
        ->generateUrl();

        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(pageName: 'index', title: "Les commandes payées")
                    ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setFormTypeOption('disabled','disabled')->onlyOnIndex(),
            TextField::new('reference')->setFormTypeOption('disabled','disabled'),
            TextField::new('dateAt', 'Passée le'),
            TextField::new('userDetails', 'Utilisateur'),
            TextEditorField::new('delivry', 'Adresse de livraison')->onlyOnDetail(),
            MoneyField::new('totalFinal', 'Total produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice', 'Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée' => 0,
                'Payée' => 1,
                'Préparation en cours' => 2,
                'Livraison en cours' => 3,
                'Livrée' => 4,
                'Remboursée' => -1
            ]),
            MoneyField::new('refundAmount','Montant remboursé')->setCurrency('EUR')->hideOnIndex(),
            TextField::new('promo','Code promo utilisé')->hideOnIndex(),
            NumberField::new('pointFideliteGagne', 'Point(s) fidélité gagné(s)')->setFormTypeOption('disabled','disabled')->hideOnIndex(),
            NumberField::new('pointFideliteUtiliseFormate', 'Point(s) fidélité utilisé(s)')->setFormTypeOption('disabled','disabled')->hideOnIndex(),
            MoneyField::new('montantCompteUtilise', 'Montant compte utilisé')->setCurrency('EUR')->setFormTypeOption('disabled','disabled')->hideOnIndex(),
            // ArrayField::new('orderDetails', 'Produits achetés')->hideOnIndex(),
            CollectionField::new('orderDetails','Produits achetés')->setTemplatePath('admin/fields/commande/order_details.html.twig')->hideOnForm()->hideOnIndex(),
 
        ];
    }

}
