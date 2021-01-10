<?php

namespace App\Repository;

use App\Entity\SharableContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SharableContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method SharableContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method SharableContact[]    findAll()
 * @method SharableContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SharableContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SharableContact::class);
    }

    // /**
    //  * @return SharableContact[] Returns an array of SharableContact objects
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
    public function findOneBySomeField($value): ?SharableContact
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
