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
     * @return UserClass|null UserClass objects if exists
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

    /**
     * Find previous UserClass before the one given
     *
     * @param UserClass $userClass the user class as reference
     *
     * @return UserClass|null UserClass objects if exists
     */
    public function findPrevious(UserClass $userClass): ?UserClass
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.rank < :rank')
            ->setParameter('rank', $userClass->getRank())
            ->orderBy('u.rank', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Return first UserClass
     */
    public function findFirst(): ?UserClass
    {
        $qb = $this->createQueryBuilder('u');
        $sq = $qb
            ->select('IDENTITY(u.next)')
            ->where($qb->expr()->isNotNull('u.next'));

        $qb = $this->createQueryBuilder('uc');
        $query = $qb
            ->andWhere($qb->expr()->notIn('uc.id', $sq->getDQL()))
            ->getQuery();
        return $query->getOneOrNullResult();
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
