<?php

namespace App\Repository;

use App\Entity\SharableSearch;
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
     * @param Sharable[] $validated Array of sharables validated by the user
     * @param User the actual user
     */
    public function getFilteredSharables(SharableSearch $search, $visibleBy, $validated, User $user)
    {
        $visibleByIds = array_map(function (UserClass $userClass)
        {
            return $userClass->getId();
        }, $visibleBy);

        $validatedIds = $validated->map(function (Sharable $sharable)
        {
            return $sharable->getId();
        });

        $query = $this->createQueryBuilder('s');

        if ($user->getUserClass()->getAccess()) {
            $query->where(
                $query->expr()->in('s.visibleBy', $visibleByIds)
            )
            ->orWhere(
                $query->expr()->isNull('s.visibleBy')
            );
        } else {
            $query->where(
                $query->expr()->in('s.visibleBy', $visibleByIds)
            );
        }

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('s.name LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }

        if ($search->disabled !== -1) {
            $query = $query
                ->andWhere('s.disabled = :d')
                ->setParameter('d', $search->disabled);
        }

        if ($search->validated !== -1) {
            if ($search->validated === 1) {
                $query = $query
                ->andWhere(
                    $query->expr()->In('s.id', $validatedIds)
                );
            } elseif ($search->validated === 0) {
                $query = $query
                ->andWhere(
                    $query->expr()->notIn('s.id', $validatedIds)
                );
            }
        }

        $result = $query->orderBy('s.' .$search->sortBy, $search->order)
        ->getQuery()
        ->getResult();

        return $result;
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
