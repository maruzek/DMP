<?php

namespace App\Repository;

use App\Entity\ProjectAdmin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjectAdmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectAdmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectAdmin[]    findAll()
 * @method ProjectAdmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectAdminRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectAdmin::class);
    }

    // /**
    //  * @return ProjectAdmin[] Returns an array of ProjectAdmin objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ProjectAdmin
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
