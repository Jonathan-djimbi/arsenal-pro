<?php

namespace App\Repository;

use App\Entity\ProduitListeAssociation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitListeAssociation>
 *
 * @method ProduitListeAssociation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProduitListeAssociation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProduitListeAssociation[]    findAll()
 * @method ProduitListeAssociation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitListeAssociationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitListeAssociation::class);
    }

    public function save(ProduitListeAssociation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProduitListeAssociation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ProduitListeAssociation[] Returns an array of ProduitListeAssociation objects
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

//    public function findOneBySomeField($value): ?ProduitListeAssociation
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
