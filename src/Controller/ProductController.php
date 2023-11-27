<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Classe\Search;
use App\Entity\BourseArmes;
use App\Entity\CartDatabase;
use App\Entity\Product;
use App\Entity\Produit;
use App\Entity\Category;
use App\Entity\Contact;
use App\Entity\Famille;
use App\Entity\Marque;
use App\Entity\MenuCategories;
use App\Entity\ProduitListeAssociation;
use App\Entity\ProfessionnelAssociationCompte;
use App\Entity\SubCategory;
use App\Entity\VenteFlash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use App\Form\ContactType;
use App\Service\MailerService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\Annotation\Route;
use App\Classe\CheckImage;
class ProductController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private $stack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $stack)
    {
        $this->entityManager = $entityManager;
        return $this->stack = $stack;

    }

    public function verifMessageContact($visiteur, $messagescontacts, $contactez, $datenow, $formcontact){ //fonctionne uniquement si paramètres sont définis dans la fonction mère
        $mail = new Mail();

	    if(count($messagescontacts) >= 3 && strtotime($messagescontacts[0]->getFaitle()) === strtotime($datenow)){
            $this->addFlash('warning', "Désolé vous avez dépassé la limite d'envoi qui est de 3 messages par jours.");
        } else {
            $URL = "https://arsenal-pro.fr/";
            $contactez->setFaitle(date('Y-m-d'));
            $contactez->setVisiteur($visiteur);
            if($this->getUser()){
                $contactez->setUser($this->getUser());
            }
            $contactFormData = $formcontact->getData();
            $subject = 'Demande de recherche depuis site ' .  $contactFormData->getPrenom() ." ".$contactFormData->getNom();
            // $content = "<h2>" . $contactFormData->getNom() . ' vous a envoyé le message suivant :</h2><br>' . $contactFormData->getDescription();
            $content = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
            <div>
                <h2 style='font-weight: normal;'>Message de ". $contactFormData->getPrenom() ." ".$contactFormData->getNom() ."</h2><br/>
                <h3 style='font-weight: normal;'>". $contactFormData->getDescription()."</h3>
            </div><br/><br/>
            <div>
                <p style='font-weight: normal;'>". $contactFormData->getPrenom() ." ".$contactFormData->getNom() ."</p>
                <p style='font-weight: normal;'>" . $contactFormData->getEmail() ."</p>
                <p style='font-weight: normal;'>Tél : " . $contactFormData->getPhone() ."</p>

            </div>
            </section></section>";
            $subjecttwo = 'ARSENAL PRO va vous répondre';
            $contenttwo = "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
            <h2>Accusé de réception de votre message</h2><br/>
            <div>
                <h3 style='font-weight: normal;'>". $contactFormData->getPrenom() .", nous avons bien reçu votre message en date du " . $datenow . "</h3><br/>
                <h3 style='font-weight: normal;'>Nous nous efforçons de répondre dans un délai inférieur à 48h. Nous recherchons systématiquement une solution avant de vous répondre.</h3>
                <br/>
                <h3 style='font-weight: normal;'>Votre message : <br>". $contactFormData->getDescription()."</h3>
            </div>
            </section></section>";

            $mail->send($contactFormData->getEmail(), $contactFormData->getNom(), $subjecttwo, $contenttwo, 4640141); //pour l'utilisateur
            $mail->send("arsenalpro74@gmail.com", $contactFormData->getNom(), $subject, $content, 4639500); //pour admin 1
            $mail->send("armurerie@arsenal-pro.com", $contactFormData->getNom(), $subject, $content, 4639500); //pour admin 2
            $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais.');
            $this->entityManager->persist($contactez); // création ligne BDD dans la table $contactez
            $this->entityManager->flush(); //MAJ BDD
        }
    }

    public function saveRecherche($search, $titre){ //permet de sauvegarder une recherche string dans un fichier TXT
        if(!empty($search->string)){
            $fichier_donnees = fopen("./../sauvegarde_recherche.txt", "a+"); //ouverture fichier en mode écriture
            if (!$fichier_donnees){
                die ("Erreur : sauvegarde pas enregistrée. Veuillez vérifier si le fichier sauvegarde existe et que les permissions d'écriture sont activés.");
            }
            fwrite($fichier_donnees,"Recherche par " . $titre . ";\t\t" .$search->string. ";\t\t" . date('d/m/Y') . ";\n"); //AJOUT DONNEE DANS LE FICHIER
            fclose($fichier_donnees);
        }
    }

    public function saveString($string){ //permet de mettre à jour et enregistrer localement la variable string de recherche pour bien utiliser correctement les filtres aside
        $session = $this->stack->getSession();
        $session->set('oldRechercheNom', $string);
    }

     public function historiqueCompteProduit($id){ //code exécuté à chaque fois qu'on l'appelle
        if($this->getUser()){ 
            $dateNow = new DateTimeImmutable();
            $cartdata = $this->entityManager->getRepository(CartDatabase::class)->findOneByUser($this->getUser());
            if($cartdata){
                $tab = $cartdata->getCart();
                $tab[count($tab)] = $id; //insertion produit à la fin du tableau
                $cartdata->setTimestamps($dateNow);   
                if(count($cartdata->getCart()) > 0){  
                    $cartdata->setCart(array_unique($tab));  //array_unique pour pas répété les produits déjà vu
                } else {
                    $cartdata->setCart([$id]); //si vide (pour nouveau compte)  
                }

                if($cartdata->isRelance()){ //si mail déjà envoyé mais liste produit à jour alors...
                    $cartdata->setRelance(false); //on remet le booléan en false pour renvoyer un nouveau mail si neccessaire
                }
                $this->entityManager->flush();
            } else { //si pas existant on le créer
                $cartdata = new CartDatabase();
                $cartdata->setUser($this->getUser());
                $cartdata->setTimestamps($dateNow);
                $cartdata->setRelance(false);
                $this->entityManager->persist($cartdata);
                $this->entityManager->flush();
            }
        }
    }


    #[Route('/nos-produits', name: 'app_products')]
     public function index(Request $request, MailerService $mailing): Response
    {
        $dateNow = new \DateTimeImmutable('now +1 hours'); 
        $titre = "La boutique";
        $visiteur = $_SERVER['REMOTE_ADDR'];
        $datenowContact =  date('Y-m-d');

        if($request->get('string') !== null){
            $this->saveString($request->get('string'));
        } 
        $produits = $this->entityManager->getRepository(Produit::class)->findProduit();
        $venteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
        $siPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());
        // dd($produits);

        $search = new Search();
        // $request->request->get('searchFiltre');
        $contactez = new Contact();
        $form = $this->createForm(\App\Form\SearchFiltreType::class, $search);
        $formTwo = $this->createForm(\App\Form\RechercheOnlyType::class, $search);

        $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
        $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenowContact], ['id'=>'desc']); //orderby id
        $form->handleRequest($request);
        $formTwo->handleRequest($request);
        $formcontact->handleRequest($request);
        $searchnotfound = false;
        $checkImage = new CheckImage();
        $checkImage->verifSiImage($produits);

        if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit filtre
                //$search = objet
                $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search, $request->getSession()->get('oldRechercheNom'));
                // dd($produits);
                if(count($produits) <= 0){ //si rien à été trouvé alors
                    $searchnotfound = true;
                } 
                $this->saveRecherche($search, $titre);

        } else if($formTwo->isSubmitted() && $formTwo->isValid()){ //requete recherche produit par nom seulement
            $produits = $this->entityManager->getRepository(Produit::class)->findWithSearchOmax($search);
            if(count($produits) <= 0){ //si rien à été trouvé alors
                $searchnotfound = true;
            } 
            $this->saveRecherche($search, $titre);
        }
        else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
            $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $datenowContact, $formcontact, $mailing);
        }
        //tri produits automatique, mettre en avant LES PRODUITS DEGRESSIF
        $produitsDegressifs = [];
        foreach($produits as $key => $produit){
            if($produit->getIsDegressif()){
                $produitsDegressifs[] = $produit;
                unset($produits[$key]);
            }
        }
        if(count($produitsDegressifs) > 0){ //si pas vide, on reforme $produits
            $produits = $produitsDegressifs + $produits;
        }
        // dd($produits);
        return $this->render('product/index.html.twig', [
            'titre' => $titre,
            'produits' => $produits,
            'venteflash' => $venteFlash,
            'pasderecherche' => $searchnotfound,
            'filtre'=> $form->createView(),
            'recherche' => $formTwo->createView(),
            'searchedString' => $request->getSession()->get('oldRechercheNom'), //nom recherche recup via session oldRechercheNom
            'datenow' => $dateNow,
            'siProPolice' => $siPolice,
            'formcontact' => $formcontact->createView(),
        ]);
    }

    #[Route('/en-promotions', name: 'app_produits_promotions')]
    public function promotions(Request $request, MailerService $mailing): Response
   {
       $dateNow = new \DateTimeImmutable('now +1 hours'); 
       $titre = "Les meilleurs promotions !";
       $visiteur = $_SERVER['REMOTE_ADDR'];
       $datenowContact =  date('Y-m-d');
       $produits = $this->entityManager->getRepository(Produit::class)->findProduit();
       $venteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
       $siPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());
       $promos = [];
       foreach($produits as $produit){
          if($produit->getPricePromo() < $produit->getPrice() && $produit->getPricePromo() > 0){ //si prix promo inférieur au prix de base et que le prix promo est supérieur à 0
            if((100 - (($produit->getPricePromo())/($produit->getPrice())) * 100) >= 10){ //trouver uniquement les promos égales ou supérieurs à 10%
                $promos[] = $produit;
            }
          }
       }
       $search = new Search();
       $contactez = new Contact();
       $form = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
        'action' => $this->generateUrl('app_products'), //route vers page produits
        'method' => 'GET',
       ]);       
       $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
       $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenowContact], ['id'=>'desc']); //orderby id
       $form->handleRequest($request);
       $formcontact->handleRequest($request);
       $searchnotfound = false;
       $checkImage = new CheckImage();
       $checkImage->verifSiImage($produits);
       if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit
               $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search);
               if(count($produits) <= 0){ //si rien à été trouvé alors
                   $searchnotfound = true;
               } 
               $this->saveRecherche($search, $titre);
       }

       else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
           $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $datenowContact, $formcontact, $mailing);
       }

       return $this->render('product/index.html.twig', [
           'titre' => $titre,
           'produits' => $promos,
           'venteflash' => $venteFlash,
           'pasderecherche' => $searchnotfound,
           'filtre'=> $form->createView(),
           'datenow' => $dateNow,
           'siProPolice' => $siPolice,
           'formcontact' => $formcontact->createView(),
       ]);
   }

    #[Route('/nos-produits-occasion', name: 'app_occasion')]
    function occasion(Request $request): Response
   {
       $titre = "La boutique d'occasion";
       $visiteur = $_SERVER['REMOTE_ADDR'];
       $datenowContact =  date('Y-m-d');
       $dateNow = new \DateTimeImmutable('now +1 hours'); 
       $produits = $this->entityManager->getRepository(Produit::class)->findOccassion();
       $venteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
       $siPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());

       $search = new Search();
       $contactez = new Contact();
       $form = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
        'action' => $this->generateUrl('app_products'), //route vers page produits
        'method' => 'GET',
       ]);
       $formTwo = $this->createForm(\App\Form\RechercheOnlyType::class, $search, [
        'action' => $this->generateUrl('app_products'), //route vers page produits
        'method' => 'GET',
       ]);

       $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
       $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenowContact], ['id'=>'desc']); //orderby id
       $form->handleRequest($request);
       $formcontact->handleRequest($request);
       $searchnotfound = false;
       $checkImage = new CheckImage();
       $checkImage->verifSiImage($produits);

       if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit
           // dd($search);
               $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search);
               // dd($produits);
               if(count($produits) <= 0){ //si rien à été trouvé alors
                   $searchnotfound = true;
               }
               $this->saveRecherche($search, $titre);

        } else if($formTwo->isSubmitted() && $formTwo->isValid()){ //requete recherche produit par nom seulement
            $produits = $this->entityManager->getRepository(Produit::class)->findWithSearchOmax($search);
            if(count($produits) <= 0){ //si rien à été trouvé alors
                $searchnotfound = true;
            } 
            $this->saveRecherche($search, $titre);
        }
       else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
            $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $datenowContact, $formcontact);
    }

       return $this->render('product/index.html.twig', [
           'titre' => $titre,
           'produits' => $produits,
           'venteflash' => $venteFlash,
           'pasderecherche' => $searchnotfound,
           'datenow' => $dateNow,
           'siProPolice' => $siPolice,
           'filtre'=> $form->createView(),
           'recherche' => $formTwo->createView(),
           'formcontact' => $formcontact->createView(),
       ]);
   }


    #[Route("/produit/{slug}", name: "app_produit")]
    public function show(Request $request, Produit $produit, $slug): Response
    {
        $ppap = [];
        $idlist = [];
        // $dateNow = new \DateTimeImmutable('now +1 hours'); //offset time
        $dateNow = new \DateTimeImmutable('now +1 hours'); 
        $produitsAssocies = [];
        $produit = $this->entityManager->getRepository(Produit::class)->findOneBy(['slug' => $slug]);
        $produitListeAssociation = $this->entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Produit p
            WHERE p.referenceAssociation is not NULL'
        )->getResult();
        // $boucleAssociation = true;
        // foreach($produitListeAssociation as $tet){
        //     if($boucleAssociation){ //si pas encore trouvé
        //         foreach($tet->getListe() as $liste){
        //             if($liste == $produit->getId()){
        //                 $produitsAssocies = $this->entityManager->getRepository(Produit::class)->findBy(['id' => $tet->getListe()]);
        //                 $boucleAssociation = false; //pour arrêter la boucle for
        //             }
        //         }
        //     }
        // }
        foreach($produitListeAssociation as $tet){
            if($tet->getReferenceAssociation() == $produit->getReferenceAssociation() && $tet->getReferenceAssociation() !== null && $tet->getIsAffiche()){
                $produitsAssocies[] = $tet;
            }
        }
        $produits = $this->entityManager->getRepository(Produit::class)->findIsBest(1,1); //isBest,isAffiche
        $isallaccessoire = $this->entityManager->getRepository(Produit::class)->findBy(['category' => 6]);
        $venteFlash = $this->entityManager->getRepository(VenteFlash::class)->findOneBy(["pid" => $produit]);
        $siBourseArme = $this->entityManager->getRepository(BourseArmes::class)->findOneByPid(['pid' => $produit]);
        $siPro = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());

        if($siBourseArme && $siBourseArme->isAffiche() && $siBourseArme->getDateLimite() > $dateNow){ //si produit étant comme bourse aux armes alors
            return $this->redirectToRoute('app_produit_bourse', ['slug' => $slug]);
        }
        //si pas image alors on le remplace par un placeholder
        $checkImage = new CheckImage();
        $checkImage->verifSiImage($produits);
        $checkImage->verifSiImage($produitsAssocies);

        if(!$produit->getIsAffiche()){ //si isAffiche = 0

            return $this->render('product/indisponible.html.twig',[ //retourne une nouvelle page produit
                'produits'=> $produits,
                'produitsAssocies' => $produitsAssocies,
                'produit' => $produit,
                'listephotos' => array($produit->getIllustrationUn(),$produit->getIllustrationDeux(),$produit->getIllustrationTrois()),
            ]);
        }
        // if(($venteFlash && $produit->isVenteFlash()) && $venteFlash->getTemps() < $dateNow){ //si venteFlash pour produit existe et le temps final est inférieur à aujourd'hui

        //     return $this->render('product/venteflash/termine.html.twig',[ //retourne une nouvelle page produit
        //         'produits'=> $produits,
        //         'produit' => $produit,
        //         'venteflash' => $venteFlash
        //     ]);

        // } else { //sinon
            if(!$venteFlash && !$produit->isVenteFlash()){ //si pas de vente flash en cours pour ce produit
                $venteFlash = false;
            }
            $isaccessoirelier = '';
            
            //rechercher et trier par accessoires | code complexe xD
            foreach($isallaccessoire as $isa){
                $ppap[] = ['id' => $isa->getId(), 'liaison' => explode(',',$isa->getAccessoireLieA())]; //récupere ID accessoire + ID produit(s) correspondant du AccessoireLierA
            }
            foreach($ppap as $pa){
                foreach($pa['liaison'] as $mdr){
                    if( intval($mdr) === $produit->getId()){ //si ID produits collectés AccessoireLierA égale à notre produit id
                        $idlist[] = $pa['id']; //on récupere tous les ID accessoires du tableau $ppap
                    }
                }
            }
            if(count($idlist) > 0){
                $isaccessoirelier = $this->entityManager->getRepository(Produit::class)->findBy(['id' => $idlist]); 
            }
            /* */
            $caracteristique = $produit->getCaracteristique();
            $caracteristiquemiseneforme = str_replace('~','●', $caracteristique);

            //$this->historiqueCompteProduit($produit->getId());

            return $this->render('product/show.html.twig',[ //retourne page produit de base
                'produit' => $produit,
                'produitsAssocies' => $produitsAssocies,
                'produits'=> $produits, //isBest
                'ladescription' => $produit->getDescription(),
                'caracteristique' => $caracteristiquemiseneforme,
                'listephotos' => array($produit->getIllustrationUn(),$produit->getIllustrationDeux(),$produit->getIllustrationTrois()), //photos a afficher
                'idcategory' => $produit->getCategory()->getId(),
                'venteflash' => $venteFlash,
                'siPro' => $siPro,
                'marque' => $produit->getMarque(),
                'accessoires' => $isaccessoirelier,
                // 'url_domain' => $request->getHost(),
                'date' => $dateNow,
            ]);
        //}
    }


    #[Route("/nos-produits/{id}/{name}", name: "app_produit_marque")]
    public function marque(Marque $marque, $id) : Response {
        // $produits = $this->entityManager->getRepository(Produit::class)->findBy(['marque' => $id]);

        return $this->redirect('/nos-produits?marques%5B%5D='. $id .''); //au lieu de copier coller un long code, mieux faire comme ça avec un lien ajax existant
    }

    
    #[Route("/nos-categories/{idSub}-{sub}/{idFam}-{famille}", name: "app_produit_categorie")]
    public function categorie($idSub, $sub, $idFam, $famille) : Response { //liens du menu "Nos produits" pour rechercher les produits subSubCategory
        $this->saveString(null);
        $subCategory = $this->entityManager->getRepository(SubCategory::class)->findOneById($idSub);
        // dd($subSubCategoryFind->getFamille()[$idFam]);
        $subSubCategory = $this->entityManager->getRepository(Famille::class)->findOneById($idFam);
        return $this->redirect('/nos-produits?subCategories%5B%5D=' . $subCategory->getId() .'&famille%5B%5D='. $subSubCategory->getId() .''); //au lieu de copier coller un long code, mieux faire comme ça avec un lien ajax existant
    }

    #[Route("/nos-categories/{idSub}-{sub}", name: "app_produit_sub_categorie_page")]
    public function subCategoriePage($sub, $idSub) : Response {
        $checkIfDispo = [];
        $subCategory = $this->entityManager->getRepository(MenuCategories::class)->findOneBySubCategory($this->entityManager->getRepository(SubCategory::class)->findOneById($idSub));
        $subSubCategory = $this->entityManager->getRepository(Famille::class)->findByName($subCategory->getFamille());

        foreach($subSubCategory as $famille){
            if(count($famille->getProduits()) > 0){ //on désaffiche ceux qui n'ont pas de produits
                $produitDetecte = false;
                if(!$produitDetecte){ //tant qu'on n'a pas trouvé de produit disponible, on recheck
                    foreach($famille->getProduits() as $unProduit){
                        if($unProduit->getIsAffiche() === true && $unProduit->getSubCategory() == $subCategory->getSubCategory()){ //check si c'est dans la bonne sous-catégorie et que les produits sont affichés
                            $produitDetecte = true;
                        }
                    }
                }
                if($produitDetecte){
                    $checkIfDispo[] = $famille;
                }
            }
        }
        // dd($subSubCategory[0]->getProduits()[0]->getIllustration());

        return  $this->render('product/menu_categories/index.html.twig', [
            'sousCategorie' => $subCategory,
            'sousSousCategorie' => $checkIfDispo
        ]);
    }

    #[Route("/nos-categories/groupe/{idSub}-{sub}", name: "app_produit_sub_categorie_all")] //pour page sous-catégorie page
    public function subCategorieSearchAll($sub, $idSub) : Response {
        $this->saveString(null);
        $subCategory = $this->entityManager->getRepository(SubCategory::class)->findOneById($idSub);
        return $this->redirect('/nos-produits?subCategories%5B%5D=' . $subCategory->getId() . '');
    }

    #[Route("/nos-categories/groupe/{idSub}-{sub}/{idFam}-{famille}", name: "app_produit_sub_sub_categorie_all")] //pour page sous-catégorie page
    public function subCategorieSearchSub($sub, $idSub, $idFam, $famille) : Response {
        $subSubCategory = $this->entityManager->getRepository(Famille::class)->findOneById($idFam);
        return $this->redirect('/nos-produits?subCategories%5B%5D=' . $idSub .'&famille%5B%5D='. $subSubCategory->getId() .''); //au lieu de copier coller un long code, mieux faire comme ça avec un lien ajax existant
    }

    #[Route("/nos-produits-suisses", name: "app_produits_suisses")]
    public function suisses(Request $req): Response {

        $titre = "La boutique suisse";
        $visiteur = $_SERVER['REMOTE_ADDR'];
        $datenowContact =  date('Y-m-d');
        $dateNow = new \DateTimeImmutable('now +1 hours'); 
        $produits = $this->entityManager->getRepository(Produit::class)->findIsSuisse(1);
        $venteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
        $siPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());

        $search = new Search();
        $contactez = new Contact();
        $form = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
            'action' => $this->generateUrl('app_products'), //route vers page produits
            'method' => 'GET',
        ]);
        $formTwo = $this->createForm(\App\Form\RechercheOnlyType::class, $search, [
            'action' => $this->generateUrl('app_products'), //route vers page produits
            'method' => 'GET',
        ]);
        $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
        $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenowContact], ['id'=>'desc']);
        $form->handleRequest($req);
        $formcontact->handleRequest($req);
        $searchnotfound = false;
        $checkImage = new CheckImage();
        $checkImage->verifSiImage($produits);
 
        if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit
                $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search);
                if(count($produits) <= 0){ //si rien à été trouvé alors
                    $searchnotfound = true;
                } 
                $this->saveRecherche($search, $titre);
        } else if($formTwo->isSubmitted() && $formTwo->isValid()){ //requete recherche produit par nom seulement
            $produits = $this->entityManager->getRepository(Produit::class)->findWithSearchOmax($search);
            if(count($produits) <= 0){ //si rien à été trouvé alors
                $searchnotfound = true;
            } 
            $this->saveRecherche($search, $titre);
        }
        else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
            $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $datenowContact, $formcontact);
        }
 
        return $this->render('product/index.html.twig', [
            'titre' => $titre,
            'produits' => $produits,
            'venteflash' => $venteFlash,
            'datenow' => $dateNow,
            'pasderecherche' => $searchnotfound,
            'filtre'=> $form->createView(),
            'recherche' => $formTwo->createView(),
            'siProPolice' => $siPolice,
            'formcontact' => $formcontact->createView(),
        ]);
    }

    #[Route('/nos-produits-forces-de-l-ordre', name: 'app_produits_forces_ordre')]
    public function forcesOrdre(Request $request): Response
   {
       $titre = "Forces de l'ordre";
       $visiteur = $_SERVER['REMOTE_ADDR'];
       $datenowContact =  date('Y-m-d');
       $dateNow = new \DateTimeImmutable('now +1 hours'); 
       $produits = $this->entityManager->getRepository(Produit::class)->findForcesOrdre(1);
       $venteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
       $siPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());

       $search = new Search();
       $contactez = new Contact();
       $form = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
        'action' => $this->generateUrl('app_products'), //route vers page produits
        'method' => 'GET',
        ]);
        $formTwo = $this->createForm(\App\Form\RechercheOnlyType::class, $search, [
            'action' => $this->generateUrl('app_products'), //route vers page produits
            'method' => 'GET',
        ]);
       $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
       $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenowContact], ['id'=>'desc']); //orderby id
       $form->handleRequest($request);
       $formcontact->handleRequest($request);
       $searchnotfound = false;
       $checkImage = new CheckImage();
       $checkImage->verifSiImage($produits);
       if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit
           // dd($search);
               $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search);
               // dd($produits);
               if(count($produits) <= 0){ //si rien à été trouvé alors
                   $searchnotfound = true;
               } 
               $this->saveRecherche($search, $titre);
        } else if($formTwo->isSubmitted() && $formTwo->isValid()){ //requete recherche produit par nom seulement
            $produits = $this->entityManager->getRepository(Produit::class)->findWithSearchOmax($search);
            if(count($produits) <= 0){ //si rien à été trouvé alors
                $searchnotfound = true;
            } 
            $this->saveRecherche($search, $titre);
        }
       else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
           $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $datenowContact, $formcontact);
       }

       return $this->render('product/index.html.twig', [
           'titre' => $titre,
           'produits' => $produits,
           'pasderecherche' => $searchnotfound,
           'filtre'=> $form->createView(),
           'recherche' => $formTwo->createView(),
           'datenow' => $dateNow,
           'venteflash' => $venteFlash,
           'siProPolice' => $siPolice,
           'formcontact' => $formcontact->createView(),
       ]);
   }

   #[Route("/nos-vente-flash", name: "app_produits_vente_flash")]
   public function venteflash(Request $req): Response {
    //    if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){ //verifie si connecté et admin (code temporaire)
    //         return $this->redirectToRoute('app_home');
    //    }
    
       $titre = "Nos vente flash !";
       $visiteur = $_SERVER['REMOTE_ADDR'];
       $datenow =  date('Y-m-d'); //date uniquement pour le message contact
       $produits = $this->entityManager->getRepository(Produit::class)->findIsVenteFlash(1);
       $siVenteFlash = $this->entityManager->getRepository(VenteFlash::class)->findAll();
       $siPolice = $this->entityManager->getRepository(ProfessionnelAssociationCompte::class)->findOneByUser($this->getUser());
       $search = new Search();
       $contactez = new Contact();
       $form = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
           'action' => $this->generateUrl('app_products'), //route vers page produits
           'method' => 'GET',
       ]);
       $formTwo = $this->createForm(\App\Form\RechercheOnlyType::class, $search, [
        'action' => $this->generateUrl('app_products'), //route vers page produits
        'method' => 'GET',
       ]);
       $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
       $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $datenow], ['id'=>'desc']);
       $form->handleRequest($req);
       $formcontact->handleRequest($req);
       $searchnotfound = false;
        $checkImage = new CheckImage();
        $checkImage->verifSiImage($produits);

       if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit
               $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search);
               if(count($produits) <= 0){ //si rien à été trouvé alors
                   $searchnotfound = true;
               } 
               $this->saveRecherche($search, $titre);
       
        } else if($formTwo->isSubmitted() && $formTwo->isValid()){ //requete recherche produit par nom seulement
            $produits = $this->entityManager->getRepository(Produit::class)->findWithSearchOmax($search);
            if(count($produits) <= 0){ //si rien à été trouvé alors
                $searchnotfound = true;
            } 
            $this->saveRecherche($search, $titre);
        }
       else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
           $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $datenow, $formcontact);
       }

       return $this->render('product/index.html.twig', [
           'titre' => $titre,
           'produits' => $produits,
           'pasderecherche' => $searchnotfound,
           'filtre'=> $form->createView(),
           'recherche' => $formTwo->createView(),
           'formcontact' => $formcontact->createView(),
           'venteflash' => $siVenteFlash,
           'siProPolice' => $siPolice,
           'datenow' => new \DateTimeImmutable('now +1 hours') //date uniquement pour VENTE FLASH | OFFSET = 'now +1 hours'
       ]);
   }
   //BOURSE AUX ARMES ////////////////////
   #[Route("/bourse-aux-munitions", name: "app_produits_bourse_armes")]
   public function bourseAuxArmes(Request $req): Response {
       /*if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){ //verifie si connecté et admin (code temporaire)
            return $this->redirectToRoute('app_home');
       }*/
       
       $titre = "Bourse aux munitions !";
       $visiteur = $_SERVER['REMOTE_ADDR'];
       $dateContactNow =  date('Y-m-d'); //date uniquement pour le message contact
       $dateNow = new \DateTimeImmutable('now +1 hours'); //date pour produit
       $produitsEnBourse = [];
       $produits = $this->entityManager->getRepository(BourseArmes::class)->findAll();
       //remplissage données avec table bourse aux armes et produits
       foreach($produits as $lesProduits){
            if(($lesProduits->isAffiche() && $lesProduits->getPid()->getIsAffiche()) && $lesProduits->getDateLimite() > $dateNow){ //si en (bourse ET si le produit est affiché (disponible)) ET si la date limite n'est pas passée
                $produitsEnBourse[] = [
                    'produit' => $this->entityManager->getRepository(Produit::class)->findOneById($lesProduits->getPid()),
                    'bourse' => $lesProduits,   
                ];
            }
            // $this->verifSiImage($produitsEnBourse[$id]['produit']);
       }
       $search = new Search();
       $contactez = new Contact();
       $form = $this->createForm(\App\Form\SearchFiltreType::class, $search, [
           'action' => $this->generateUrl('app_products'), //route vers page produits
           'method' => 'GET',
       ]);
       $formTwo = $this->createForm(\App\Form\RechercheOnlyType::class, $search, [
        'action' => $this->generateUrl('app_products'), //route vers page produits
        'method' => 'GET',
       ]);
       $formcontact = $this->createForm(ContactType::class, $contactez); //pour la page de contact intégré depuis la page de produits si aucun produit n'est trouvé
       $messagescontacts = $this->entityManager->getRepository(Contact::class)->findBy(['visiteur'=> $visiteur, 'faitle' => $dateContactNow], ['id'=>'desc']);
       $form->handleRequest($req);
       $formcontact->handleRequest($req);
       $searchnotfound = false;

       if ($form->isSubmitted() && $form->isValid()) { //requete recherche produit
               $produits = $this->entityManager->getRepository(Produit::class)->findWithSearch($search);
               if(count($produits) <= 0){ //si rien à été trouvé alors
                   $searchnotfound = true;
               } 
               $this->saveRecherche($search, $titre);
        } else if($formTwo->isSubmitted() && $formTwo->isValid()){ //requete recherche produit par nom seulement
            $produits = $this->entityManager->getRepository(Produit::class)->findWithSearchOmax($search);
            if(count($produits) <= 0){ //si rien à été trouvé alors
                $searchnotfound = true;
            } 
            $this->saveRecherche($search, $titre);
        }
       else if($formcontact->isSubmitted() && $formcontact->isValid()){ //requete contact
           $this->verifMessageContact($visiteur, $messagescontacts, $contactez, $dateContactNow, $formcontact);
       }
       
       return $this->render('product/bourse_aux_armes.html.twig', [
           'titre' => $titre,
           'produits' => $produitsEnBourse,
           'pasderecherche' => $searchnotfound,
           'filtre'=> $form->createView(),
           'recherche' => $formTwo->createView(),
           'formcontact' => $formcontact->createView(),
       ]);
   }

   #[Route("/produit-en-bourse/{slug}", name: "app_produit_bourse")]
   public function showBourseAuxArmes(Request $request, Produit $produit, $slug): Response
   {
    //    if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){ //verifie si connecté et admin (code temporaire)
    //         return $this->redirectToRoute('app_produit', ['slug' => $slug]);
    //    }
       $ppap = [];
       $idlist = [];
       $dateNow = new \DateTimeImmutable('now +1 hours'); 
       $produit = $this->entityManager->getRepository(Produit::class)->findOneBy(['slug' => $slug]);
       $bourseArme = $this->entityManager->getRepository(BourseArmes::class)->findOneByPid(['pid' => $produit]);

       // dd($bourseArme !== null);
       $produits = $this->entityManager->getRepository(Produit::class)->findIsBest(1,1); //isBest,isAffiche
       $isallaccessoire = $this->entityManager->getRepository(Produit::class)->findBy(['category' => 6]);
       
       if($bourseArme == null || !$bourseArme->isAffiche() || $bourseArme->getDateLimite() < $dateNow){ //si pas affiché ou date limite dépassé aux bourse aux armes
            return $this->redirectToRoute('app_produit', ['slug' => $slug]);
        }

        $$checkImage = new CheckImage();
        $checkImage->verifSiImage($produits);
       

       if(!$produit->getIsAffiche()){ //si isAffiche = 0 (indisponible)

           return $this->render('product/indisponible.html.twig',[ //retourne une nouvelle page produit
               'produits'=> $produits,
               'produit' => $produit,
               'listephotos' => array($produit->getIllustrationUn(),$produit->getIllustrationDeux(),$produit->getIllustrationTrois()),
           ]);
       }
        $isaccessoirelier = '';
        
        //rechercher et trier par accessoires | code complexe xD
        foreach($isallaccessoire as $isa){
            $ppap[] = ['id' => $isa->getId(), 'liaison' => explode(',',$isa->getAccessoireLieA())]; //récupere ID accessoire + ID produit(s) correspondant du AccessoireLierA
        }
        foreach($ppap as $pa){
            foreach($pa['liaison'] as $asc){
                if( intval($asc) === $produit->getId()){ //si ID produits collectés AccessoireLierA égale à notre produit id
                $idlist[] = $pa['id']; //on récupere tous les ID accessoires du tableau $ppap
                }
            }
        }
        if(count($idlist) > 0){
            $isaccessoirelier = $this->entityManager->getRepository(Produit::class)->findBy(['id' => $idlist]); 
        }
        /* */
        $caracteristique = $produit->getCaracteristique();
        $caracteristiquemiseneforme = str_replace('~','●', $caracteristique);

        //$this->historiqueCompteProduit($produit->getId());

        if($bourseArme !== null){
        return $this->render('product/show_en_bourse.html.twig',[ //retourne page produit de base
            'produit' => $produit, 
            'produits'=> $produits, //isBest
            'bourse' => $bourseArme,
            'ladescription' => $produit->getDescription(),
            'caracteristique' => $caracteristiquemiseneforme,
            'listephotos' => array($produit->getIllustrationUn(),$produit->getIllustrationDeux(),$produit->getIllustrationTrois()), //photos a afficher
            'idcategory' => $produit->getCategory()->getId(),
            'marque' => $produit->getMarque(),
            'accessoires' => $isaccessoirelier,
            'url_domain' => $request->getHost(),
            'date' => $dateNow,
        ]);
    } else { //si pas dans la bourse aux armes alors
        return $this->redirectToRoute('app_produit', ['slug' => $slug]);
    }
  }
}
