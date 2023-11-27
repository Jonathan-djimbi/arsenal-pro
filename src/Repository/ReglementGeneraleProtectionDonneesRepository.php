<?php

namespace App\Repository;

use App\Entity\ReglementGeneraleProtectionDonnees;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReglementGeneraleProtectionDonnees>
 *
 * @method ReglementGeneraleProtectionDonnees|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReglementGeneraleProtectionDonnees|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReglementGeneraleProtectionDonnees[]    findAll()
 * @method ReglementGeneraleProtectionDonnees[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReglementGeneraleProtectionDonneesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReglementGeneraleProtectionDonnees::class);
    }

    public function add(ReglementGeneraleProtectionDonnees $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ReglementGeneraleProtectionDonnees $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ReglementGeneraleProtectionDonnees[] Returns an array of ReglementGeneraleProtectionDonnees objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReglementGeneraleProtectionDonnees
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
