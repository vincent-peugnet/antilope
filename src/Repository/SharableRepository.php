<?php

/**
 * This file is part of Antilope
 *
 * Antilope is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * PHP version 7.4
 *
 * @package Antilope
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2020-2021 Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Repository;

use App\Entity\SharableSearch;
use App\Entity\Sharable;
use App\Entity\User;
use App\Entity\UserClass;
use App\Security\Voter\UserVoter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Sharable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sharable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sharable[]    findAll()
 * @method Sharable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SharableRepository extends ServiceEntityRepository
{

    private Security $security;


    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Sharable::class);
        $this->security = $security;
    }

    /**
     * Query all sharable based on user Class and visibleBy setting on the sharables
     * @param User $user the actual user
     * @param bool $geo return only sharable that have coordinates
     */
    public function getFilteredSharables(SharableSearch $search, User $user, bool $geo = false): array
    {
        return $this->getFilteredSharablesQuery($search, $user, $geo)->getResult();
    }


    /**
     * Query all sharable based on user Class and visibleBy setting on the sharables
     * @param User $user the actual user
     * @param bool $geo return only sharable that have coordinates
     */
    public function getFilteredSharablesQuery(SharableSearch $search, User $user, bool $geo = false): Query
    {
        $userClassRepository = $this->getEntityManager()->getRepository(UserClass::class);
        assert($userClassRepository instanceof UserClassRepository);
        $visibleBy = $userClassRepository->findLowerthan($user->getUserClass());

        $visibleByIds = array_map(function (UserClass $userClass) {
            return $userClass->getId();
        }, $visibleBy);

        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.managedBy', 'm')
            ->addSelect('m')
            ->leftJoin('s.visibleBy', 'uc')
            ->addSelect('uc')
            ->leftJoin('s.validations', 'v')
            ->addSelect('v')
            ->leftJoin('s.bookmarks', 'b')
            ->addSelect('b')
            ->leftJoin('s.interesteds', 'i')
            ->addSelect('i')
            ->leftJoin('s.tags', 't')
            ->addSelect('t')
        ;

        // Filter sharable that have coordinates
        if ($geo) {
            $qb->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->isNotNull('s.latitude'),
                    $qb->expr()->isNotNull('s.longitude')
                )
            );
        }

        // Filter Sharables by manager
        // Check if user paranoia level authorize this
        if ($search->getManagedBy() !== null) {
            $manager = $search->getManagedBy();
            $qb->andWhere('m.user = :mid')
                ->setParameter('mid', $manager->getId())
                ->andWhere('m.confirmed = 1');
            if ($manager !== $user) {
                $qb->andWhere('m.anonymous = 0');
            }
        }

        // Filter Sharables by validatedBy
        // Check if user paranoia level authorize this
        if ($search->getValidatedBy() !== null) {
                $qb->andWhere('v.user = :vid')
                    ->setParameter('vid', $search->getValidatedBy());
        }

        // Filter Sharables by BookmarkedBy
        if (!is_null($search->getBookmarkedBy())) {
            $qb->andWhere('b.user = :bid')
                ->setParameter('bid', $search->getBookmarkedBy());
        }

        // Filter Sharables by InterestedBy
        if (!is_null($search->getInterestedBy())) {
            $qb->andWhere('i.user = :iid')
                ->setParameter('iid', $search->getInterestedBy());
        }

        // Filter Sharables by VisibleBy
        if (!is_null($search->getVisibleBy())) {
            $qb->andWhere('s.visibleBy = :uc')
                ->setParameter('uc', $search->getVisibleBy());
        }

        // Work, but there may be a better way to do this including a lot of joins
        if ($user->getUserClass()->getAccess()) {
            $qb->andwhere(
                $qb->expr()->orX(
                    $qb->expr()->in('s.visibleBy', $visibleByIds),
                    $qb->expr()->isNull('s.visibleBy'),
                    $qb->expr()->eq('m.user', $user->getId())
                )
            );
        } else {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('s.visibleBy', $visibleByIds),
                    $qb->expr()->eq('m.user', $user->getId())
                )
            );
        }

        if (!empty($search->getQuery())) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('s.name', $qb->expr()->literal('%' . $search->getQuery() . '%')),
                    $qb->expr()->like('s.description', $qb->expr()->literal('%' . $search->getQuery() . '%')),
                    $qb->expr()->like('s.details', $qb->expr()->literal('%' . $search->getQuery() . '%'))
                )
            );
        }

        if (!$search->getTags()->isEmpty()) {
            $qb = $qb
                    ->andWhere('t IN (:tags)')
                    ->setParameter(':tags', $search->getTags())
            ;
        }

        if (!$search->getDisabled()) {
            $qb = $qb
                ->andWhere('s.disabled = :d')
                ->setParameter('d', $search->getDisabled());
        }

        if ($search->getSortBy() && $search->getOrder()) {
            $qb->orderBy('s.' . $search->getSortBy(), $search->getOrder());
        }

        return $qb->getQuery();
    }


    /**
     * @return Sharable[]|Collection
     */
    public function findByManagerAndInterested(User $manager, User $interested): Collection
    {
        $sharables = $this->createQueryBuilder('s')
            ->leftJoin('s.managedBy', 'm')
            ->andWhere('m.user = :mid')
            ->setParameter('mid', $manager->getId())
            ->leftJoin('s.interesteds', 'i')
            ->andWhere('i.user = :iid')
            ->setParameter('iid', $interested->getId())
            ->getQuery()
            ->getResult()
        ;
        return new ArrayCollection($sharables);
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
