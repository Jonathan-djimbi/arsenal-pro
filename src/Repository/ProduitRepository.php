<?php

namespace App\Repository;

use App\Classe\Search;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function add(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
        /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findOccassion(){
        $query = $this->createQueryBuilder('p')->select('p')->andWhere('p.isOccassion = :isOccassion')->setParameter('isOccassion',1)->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1)
        ->andWhere('p.quantite > :quantite')->setParameter('quantite',0)
        ->andWhere('p.isSuisse = :isSuisse')->setParameter('isSuisse', 0);
        return $query->getQuery()->getResult();
    }
    public function findProduit(){ //category 7 = prestation 
        $query = $this->createQueryBuilder('p')->select('p') //->groupBy('p.referenceAssociation')
        // ->andWhere('p.category != :category')->setParameter('category',7)
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1); 
        return $query->getQuery()->getResult();
    }
    /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findIsBest($best,$afficher){
        $query = $this->createQueryBuilder('p')->select('p')
        ->andWhere('p.isBest = :isBest')->setParameter('isBest',$best)
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', $afficher);
        return $query->getQuery()->getResult();
    }
     /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findIsPromo($afficher){
        $query = $this->createQueryBuilder('p')->select('p')
        ->andWhere('p.pricepromo IS NOT NULL')
        ->andWhere('p.pricepromo != p.price')
        ->andWhere('p.price > p.pricepromo')
        ->andWhere('p.category != :category')->setParameter('category',7)
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', $afficher);
        return $query->getQuery()->getResult();
    }
    /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findIsSuisse($afficher){
        $query = $this->createQueryBuilder('p')->select('p')
        ->andWhere('p.isSuisse = 1')
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', $afficher);
        return $query->getQuery()->getResult();
    }
    /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findForcesOrdre($afficher){
        $query = $this->createQueryBuilder('p')->select('p')
        ->andWhere('p.isForcesOrdre = 1')
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', $afficher);
        return $query->getQuery()->getResult();
    }
    /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findIsVenteFlash($afficher){
        $query = $this->createQueryBuilder('p')->select('p','vf')
        ->leftJoin( join: 'p.produitsFlash', alias: 'vf') //jointure table Vente flash
        ->andWhere('vf.isAffiche = :isAfficheVF')->setParameter('isAfficheVF', $afficher)
        ->andWhere('p.isVenteFlash = 1') //montrer tous les produits en vente flash
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', $afficher);
        return $query->getQuery()->getResult();
    }

        /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findWithSearchOmax(Search $search) //requete recherche produit par nom seulement
    {
        $query = $this->createQueryBuilder('p'); //->groupBy('p.referenceAssociation');

        if (!empty($search->string)){
            $stringArray = explode(" ",$search->string); //tableau qui prend les espaces de la recherche pour faire une recherche correspondante
            $query = $query->addSelect("(CASE WHEN p.name like :single THEN 0
            ELSE 1 END) AS HIDDEN ORD")->andWhere('p.name LIKE :single')->setParameter('single', "%{$search->string}%"); //mettre le resultat en premier
            
            foreach($stringArray as $tableau){
                 $query = $query->orWhere('p.name LIKE :string OR p.caracteristique LIKE :string')
                ->setParameter('string', "%{$tableau}%");
            }        
            $query = $query->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1)->orderBy('ORD', "ASC");
        }
        return $query->getQuery()->getResult();
    }
    /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findWithSearch(Search $search, $stringObtenu) //$type 0 = neuf, 1 = occasion, 2 = suisse | requete permettant de tout rechercher (par marque, par catégories, par calibre, par nom, etc)
    {
        // dd($search);
        $query = $this
            ->createQueryBuilder('p')
            ->select('c','p','m','f','sb','ca') //->groupBy('p.referenceAssociation')
            ->join( join: 'p.category', alias: 'c')
            ->join( join: 'p.marque', alias: 'm')
            ->leftJoin( join: 'p.calibres', alias: 'ca')
            ->leftJoin('p.famille','f')
            ->leftJoin('p.subCategory','sb');


        if (!empty($stringObtenu) && $stringObtenu !== null){
            $stringArray = explode(" ", $stringObtenu); //tableau qui prend les espaces de la recherche pour faire une recherche correspondante
            $query = $query->addSelect("(CASE WHEN p.name like :single THEN 0
            ELSE 1 END) AS HIDDEN ORD")->andWhere('p.name LIKE :single')->setParameter('single', "%{$stringObtenu}%"); //mettre le resultat en premier
            
            foreach($stringArray as $tableau){
                    $query = $query->orWhere('p.name LIKE :string OR p.caracteristique LIKE :string')
                ->setParameter('string', "%{$tableau}%");
            }        
        }
        // $query = $query->andWhere('c.id != :cata')->setParameter('cata',7); //pas de prestation dans page produit

        if (!empty($search->categories)){
            $query= $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $search->categories);
        }
        if (!empty($search->marques)){
            $query= $query
                ->andWhere('m.id IN (:marques)')
                ->setParameter('marques', $search->marques);
        }
        if (!empty($search->famille)){
            $query= $query
                ->andWhere('f.id IN (:famille)')
                ->setParameter('famille', $search->famille);
        }

        if (!empty($search->subCategories)){
            $query= $query
                ->andWhere('sb.id IN (:subCategory)')
                ->setParameter('subCategory', $search->subCategories);
        }

        if(!empty($search->calibre)){
            $query= $query
            ->andWhere('ca.id IN (:calibre)')
            ->setParameter('calibre', $search->calibre); 
        }
        if (!empty($search->minprice) && !empty($search->maxprice)){ //pour la fourgette de prix, si un des input box sont vide, la recherche ne s'aboutie pas pour le prix
            $query = $query
                ->andWhere('p.price BETWEEN :minprice*100 AND :maxprice*100') //*100 car dans la BDD tous les prix sont multipliés par 100
                ->setParameter('minprice', $search->minprice)->setParameter('maxprice', $search->maxprice);
        }

        // $query= $query
        // ->andWhere('p.isOccassion not in (p.quantite )');
        //  //si occasion, il ne faut pas qu'il trouve d'objets neufs et qui ne sont pas en stock
        if(($search->isOccasion) === true){
            $query = $query->andWhere('p.isOccassion = 1');
        }
        if(($search->isPromo) === true){
            $query = $query->andWhere('p.price > p.pricepromo')->andWhere('p.pricepromo != 0');
        }
        if(($search->isFDO) === true){
            $query = $query->andWhere('p.isForcesOrdre = 1');
        }
        if(!empty($stringObtenu) && $stringObtenu !== null){
            $query = $query->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1)->orderBy('ORD', "ASC");
        } else {
            $query = $query->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1)->orderBy('p.price', "ASC"); //tri de prix en croissance DE BASE
        }
        if(!empty($search->orderPrices)){
            if(($search->orderPrices) == "ASC"){
                $query = $query->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1)->orderBy('p.price', "ASC"); //tri de prix en croissance
            }
            if(($search->orderPrices) == "DESC"){
                $query = $query->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', 1)->orderBy('p.price', "DESC"); //tri de prix en décroissance
            }
        }
        
        return $query->getQuery()->getResult();

    }
    /**
     * Requête qui me permet de récuperer les produits en fonction de la recherche de l'utilisateur
     * @return Produit[]
     */
    public function findPrix($state,$afficher){
        $query = $this->createQueryBuilder('p')->select($state . '(p.price)')
        ->andWhere('p.isAffiche = :isAffiche')->setParameter('isAffiche', $afficher);
        return $query->getQuery()->getResult();
    }

    public function topProduitsEuros($limit, $ordre){
        $query = $this->createQueryBuilder('p')->select('p.name, p.price, p.id')->andWhere('p.price > 0')->andWhere('p.isAffiche = 1')
        ->orderBy('p.price',$ordre)->setMaxResults($limit);
        return $query->getQuery()->getResult();
    }

    public function topProduitsQuantite($limit){
        $query = $this->createQueryBuilder('p')->select('p.id, p.name, p.quantite')->andWhere('p.category != 7')->orderBy('p.quantite','DESC')->setMaxResults($limit); //prestations exclus
        return $query->getQuery()->getResult();
    }
//    /**
//     * @return Produit[] Returns an array of Produit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
