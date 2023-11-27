<?php

namespace App\Repository;

use App\Entity\HistoriqueReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriqueReservation>
 *
 * @method HistoriqueReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriqueReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriqueReservation[]    findAll()
 * @method HistoriqueReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriqueReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueReservation::class);
    }

    public function add(HistoriqueReservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HistoriqueReservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findSuccessReservations($user){
        return $this->createQueryBuilder('r')
            ->andWhere('r.state > 0')->orWhere('r.state = -1') //si réservation payée ou remboursée
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findReservationsAsc(){
        return $this->createQueryBuilder('r')
        ->orderBy('r.createAt', 'ASC')
        ->getQuery()
        ->getResult();
    }

//    /**
//     * @return HistoriqueReservation[] Returns an array of HistoriqueReservation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoriqueReservation
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
