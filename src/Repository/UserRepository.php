<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /**
     * @return User[]
     */
    public function searchUser($value)
    {
        $q = $this->createQueryBuilder('u');

        foreach (preg_split('/\s+/', trim($value)) as $parsedPhrase) {
            if ($parsedPhrase != '') {
                if (!preg_match('/%/', $parsedPhrase)) {
                    $q->where(
                        $q->expr()->andX(
                            $q->expr()->orX(
                                $q->expr()->like('u.firstname', ':val'),
                                $q->expr()->like('u.lastname', ':val'),
                                $q->expr()->like('u.class', ':val')
                            ),
                        )
                    )
                        ->setParameter('val', '%' . $parsedPhrase . '%');
                }
            }
        }

        return $q->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
