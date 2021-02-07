<?php

namespace App\Repository;

use App\Entity\IndexBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IndexBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndexBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndexBlock[]    findAll()
 * @method IndexBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndexBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndexBlock::class);
    }

    // /**
    //  * @return IndexBlock[] Returns an array of IndexBlock objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IndexBlock
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
