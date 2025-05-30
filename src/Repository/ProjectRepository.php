<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    // /**
    //  * @return Project[] Returns an array of Project objects
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

    /**
     * @return Project[]
     */
    public function searchProject($value)
    {
        $q = $this->createQueryBuilder('u');

        foreach (preg_split('/\s+/', trim($value)) as $parsedPhrase) {
            if ($parsedPhrase != '') {
                if (!preg_match('/%/', $parsedPhrase)) {
                    $q->where(
                        $q->expr()->andX(
                            $q->expr()->orX(
                                $q->expr()->like('u.name', ':val'),
                                $q->expr()->like('u.description', ':val'),
                            ),
                        )
                    )
                        ->andWhere("u.deleted='0'")
                        ->setParameter('val', '%' . $parsedPhrase . '%');
                }
            }
        }

        return $q->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Project
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
