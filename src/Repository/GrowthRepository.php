<?php

namespace App\Repository;

use App\Entity\Growth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Growth|null find($id, $lockMode = null, $lockVersion = null)
 * @method Growth|null findOneBy(array $criteria, array $orderBy = null)
 * @method Growth[]    findAll()
 * @method Growth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GrowthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Growth::class);
    }

    // /**
    //  * @return Growth[] Returns an array of Growth objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Growth
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
