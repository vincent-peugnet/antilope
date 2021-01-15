<?php

namespace App\Repository;

use App\Entity\UserClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserClass|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserClass|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserClass[]    findAll()
 * @method UserClass[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserClassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserClass::class);
    }

    /**
     * Get all UserClass that are at lower or equal rank.
     *
     * @param UserClass $userClass the user class as reference
     *
     * @return UserClass[] Returns an array of UserClass objects
     */
    public function findLowerthan(UserClass $userClass)
    {

        return $this->createQueryBuilder('u')
            ->andWhere('u.rank <= :rank')
            ->setParameter('rank', $userClass->getRank())
            ->orderBy('u.rank', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find next UserClass after the one given
     *
     * @param UserClass $userClass the user class as reference
     *
     * @return UserClass|null Returns an array of UserClass objects
     */
    public function findNext(UserClass $userClass): ?UserClass
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.rank > :rank')
            ->setParameter('rank', $userClass->getRank())
            ->orderBy('u.rank', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?UserClass
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
