<?php

namespace App\Repository;

use App\Entity\ReportSharable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReportSharable|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportSharable|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportSharable[]    findAll()
 * @method ReportSharable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportSharableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportSharable::class);
    }

    // /**
    //  * @return ReportSharable[] Returns an array of ReportSharable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReportSharable
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
