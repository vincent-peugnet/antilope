<?php

namespace App\Repository;

use App\Entity\SharableSearch;
use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
use App\Security\Voter\UserVoter;
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
     * @param UserClass[] $visibleBy Collection of UserClass
     * @param User the actual user
     */
    public function getFilteredSharables(SharableSearch $search, $visibleBy, User $user)
    {
        $visibleByIds = array_map(function (UserClass $userClass)
        {
            return $userClass->getId();
        }, $visibleBy);

        $qb = $this->createQueryBuilder('s')
            ->join('s.managedBy', 'm')
            ->addSelect('m')
            ->leftJoin('s.visibleBy', 'uc')
            ->addSelect('uc')
            ->leftJoin('s.validations', 'v')
            ->addSelect('v');

        // Filter Sharables by manager
        // Not working,
        if ($search->getManagedBy() !== null) {
                $qb->andWhere('m.id = :mid')
                ->setParameter('mid', $search->getManagedBy());

        }

        // Work, but there may be a better way to do this including a lot of joins
        if ($user->getUserClass()->getAccess()) {
            $qb->andwhere(
                $qb->expr()->in('s.visibleBy', $visibleByIds)
            )
            ->orWhere(
                $qb->expr()->isNull('s.visibleBy')
            );
        } else {
            $qb->where(
                $qb->expr()->in('s.visibleBy', $visibleByIds)
            );
        }

        if (!empty($search->getQuery())) {
            $qb = $qb
                ->andWhere('s.name LIKE :q')
                ->setParameter('q', "%{$search->getQuery()}%");
        }

        if ($search->getDisabled() !== null) {
            $qb = $qb
                ->andWhere('s.disabled = :d')
                ->setParameter('d', $search->getDisabled());
        }

        if ($search->getValidated() !== null) {
            if ($search->getValidated()) {
                $qb = $qb->andWhere(
                    $qb->expr()->In('v.user', $user->getId())
                );
            } else {
                $qb = $qb->andWhere(
                    $qb->expr()->orX(                // does not work

                        $qb->expr()->notIn('v.user', $user->getId()),
                        $qb->expr()->isNull('v.user')
                    )
                );
            }
        }

        if ($search->getSortBy() && $search->getOrder()) {
            $qb->orderBy('s.' .$search->getSortBy(), $search->getOrder());   
        }
        $result = $qb->getQuery()
        ->getResult();

        return $result;

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
