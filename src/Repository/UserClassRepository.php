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
 * @author Nicolas Peugnet <n.peugnet@free.fr>
 * @copyright 2020-2021 Vincent Peugnet, Nicolas Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

namespace App\Repository;

use App\Entity\UserClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
     * Get all UserClass that are at lower or equal.
     *
     * @param UserClass $userClass the user class as reference
     *
     * @return UserClass[] Returns an array of UserClass objects
     */
    public function findLowerthan(UserClass $userClass): array
    {
        return $this->findBetween($this->findFirst(), $userClass);
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

    public function findLast(): ?UserClass
    {
        $qb = $this->createQueryBuilder('u');
        return $qb->andWhere($qb->expr()->isNull('u.next'))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Recursive SQL raw query to find a list of user class between two others.
     *
     * @param UserClass $from the lowest class from which to search (included).
     * @param UserClass|null $to the optional highest class to which the search should stop (included).
     * @return UserClass[] the list of classes that match the given parameters.
     */
    public function findBetween(UserClass $from, ?UserClass $to = null): array
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(UserClass::class, 'cte');

        $exitCondition = !is_null($to) ? 'WHERE c.id != ?' : '';
        $sql = "WITH RECURSIVE cte as (
                SELECT u.*
                FROM   user_class as u
                WHERE  u.id = ?

                UNION  ALL

                SELECT n.*
                FROM   cte c
                JOIN   user_class n ON n.id = c.next_id
                $exitCondition
            )
            SELECT *
            FROM   cte;";
        $query = $this->_em->createNativeQuery($sql, $rsm);

        $query->setParameter(1, $from);
        if (!is_null($to)) {
            $query->setParameter(2, $to);
        }
        $result = $query->getResult();
        return $result;
    }

    /**
     * @return UserClass[] All the user classes sorted from first to last
     */
    public function findAll(): array
    {
        $first = $this->findFirst();
        return $this->findBetween($first);
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
