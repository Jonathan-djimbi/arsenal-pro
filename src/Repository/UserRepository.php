<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function topClient($limit){
        $query = $this->createQueryBuilder('u')->select('u.firstname, u.lastname, u.id','ca.dernier_achat, ca.nombre_achat')
        ->innerJoin( join: 'u.carteFidelite', alias: 'ca')->orderBy('ca.nombre_achat','DESC')->setMaxResults($limit);
        return $query->getQuery()->getResult();
    }
    public function dernierAchatClient(){
        $query = $this->createQueryBuilder('u')->select('ca.dernier_achat, u.lastname, u.firstname, u.id')
        ->innerJoin( join: 'u.carteFidelite', alias: 'ca')->orderBy('ca.dernier_achat','DESC')->setMaxResults(1);
        return $query->getQuery()->getResult();
    }
    public function topMontant($limit){
        //query qui récupère les 5 comptes ayant le plus de somme_compte
        $query = $this->createQueryBuilder('u')->select('u.firstname, u.lastname, u.id','ca.sommeCompte')
        ->innerJoin( join: 'u.carteFidelite', alias: 'ca')->orderBy('ca.sommeCompte','DESC')->setMaxResults($limit);
        return $query->getQuery()->getResult();
    }
//     SELECT * FROM `user` u
// left join carte_fidelite c on u.id = c.user_id 
// order by c.nombre_achat desc limit 5;

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
