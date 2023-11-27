<?php

namespace App\Repository;

use App\Entity\ConditionGeneraleVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConditionGeneraleVente>
 *
 * @method ConditionGeneraleVente|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConditionGeneraleVente|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConditionGeneraleVente[]    findAll()
 * @method ConditionGeneraleVente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConditionGeneraleVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConditionGeneraleVente::class);
    }

    public function add(ConditionGeneraleVente $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConditionGeneraleVente $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ConditionGeneraleVente[] Returns an array of ConditionGeneraleVente objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ConditionGeneraleVente
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
