<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function add(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /*
     * findSuccessOrders()
     * Permet d'afficher les commandes dans l'espace membre de l'utilisateur
     */
    public function findSuccessOrders($user)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.state > 0')->orWhere('o.state = -1') //si commande payée ou remboursée
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
    public function findOrdersAsc(){ //permet de trier les commandes par rapport à la date, fonction utilisé dans la génération d'un excel "Journal de ventes"
        return $this->createQueryBuilder('o')
        ->orderBy('o.createAt', 'ASC')
        ->getQuery()
        ->getResult();
    }
    public function prixCommandeManip($state){
        return $this->createQueryBuilder('o')->select("avg(ord.total) as prixCommande")
            ->innerJoin( join: 'o.orderDetails', alias: 'ord')
            ->andWhere('o.state >= :state')->setParameter('state', $state)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
    }

    public function quantiteCommandeMoyenne(){
        return $this->createQueryBuilder('o')->select("avg(ord.quantity) as quantiteMoyenne")
            ->innerJoin( join: 'o.orderDetails', alias: 'ord')
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
    }

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
