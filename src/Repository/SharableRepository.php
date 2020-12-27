<?php

namespace App\Repository;

use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sharable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sharable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sharable[]    findAll()
 * @method Sharable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SharableRepository extends ServiceEntityRepository
{

    public const PAGINATOR_PER_PAGE = 5;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sharable::class);
    }


    /**
     * List all sharable based on user Class and visibleBy setting on the sharables
     * 
     * @param int $offset
     * @param UserClass[] $visibleBy Collection of UserClass
     * @param User the actual user
     */
    public function getSharablePaginator(int $offset, $visibleBy, User $user): Paginator
    {
        $visibleByIds = array_map(function (UserClass $userClass)
        {
            return $userClass->getId();
        }, $visibleBy);

        $query = $this->createQueryBuilder('s');

        if ($user->getUserClass()->getAccess()) {
            $query->where(
                $query->expr()->In('s.visibleBy', $visibleByIds)
            )
            ->orWhere(
                $query->expr()->isNull('s.visibleBy')
            );
        } else {
            $query->where(
                $query->expr()->in('s.visibleBy', $visibleByIds)
            );
        }

        $query->orderBy('s.id', 'DESC')
        ->setMaxResults(self::PAGINATOR_PER_PAGE)
        ->setFirstResult($offset)
        ->getQuery()
    ;




        return new Paginator($query);
    }



    // /**
    //  * @return Sharable[] Returns an array of Sharable objects
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
    public function findOneBySomeField($value): ?Sharable
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
