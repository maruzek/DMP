<?php

namespace App\Repository;

use App\Entity\Seen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Seen|null find($id, $lockMode = null, $lockVersion = null)
 * @method Seen|null findOneBy(array $criteria, array $orderBy = null)
 * @method Seen[]    findAll()
 * @method Seen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seen::class);
    }

    // /**
    //  * @return Seen[] Returns an array of Seen objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Seen
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
