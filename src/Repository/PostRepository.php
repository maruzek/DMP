<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
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
     * @return Post[]
     */
    public function searchPost($value)
    {
        $q = $this->createQueryBuilder('u');

        foreach (preg_split('/\s+/', trim($value)) as $parsedPhrase) {
            if ($parsedPhrase != '') {
                if (!preg_match('/%/', $parsedPhrase)) {
                    $q->where(
                        $q->expr()->andX(
                            $q->expr()->orX(
                                $q->expr()->like('u.text', ':val'),
                            ),
                        )
                    )
                        ->andWhere("u.deleted=0")
                        ->andWhere("u.privacy=0")
                        ->setParameter('val', '%' . $parsedPhrase . '%');
                }
            }
        }

        return $q->getQuery()->getResult();
    }

    public function findPostLimit($limit, $project)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.project = :project')
            ->setParameter('project', $project)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findPostFromTo($from, $to, $privacy, $project)
    {
        if ($privacy == 0) {
            return $this->createQueryBuilder('p')
                ->andWhere('p.project = :project')
                ->setParameter('project', $project)
                ->setFirstResult($from)
                ->setMaxResults($to)
                ->andWhere('p.deleted=0')
                ->andWhere('p.privacy=0')
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getResult();
        } else {
            return $this->createQueryBuilder('p')
                ->andWhere('p.project = :project')
                ->setParameter('project', $project)
                ->setFirstResult($from)
                ->setMaxResults($to)
                ->andWhere('p.deleted=0')
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getResult();
        }
    }

    public function findNonDeleted($project, $privacy)
    {
        if ($privacy == 0) {
            return $this->createQueryBuilder('p')
                ->andWhere('p.project = :project')
                ->setParameter('project', $project)
                ->andWhere('p.deleted=0')
                ->andWhere('p.privacy=0')
                ->getQuery()
                ->getResult();
        } else {
            return $this->createQueryBuilder('p')
                ->andWhere('p.project = :project')
                ->setParameter('project', $project)
                ->andWhere('p.deleted=0')
                ->getQuery()
                ->getResult();
        }
    }

    /*
    public function findOneBySomeField($value): ?Post
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
