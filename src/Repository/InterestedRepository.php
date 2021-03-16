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

use App\Entity\Interested;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Interested|null find($id, $lockMode = null, $lockVersion = null)
 * @method Interested|null findOneBy(array $criteria, array $orderBy = null)
 * @method Interested[]    findAll()
 * @method Interested[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterestedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Interested::class);
    }

    /**
     * @return Interested[] Returns an array of Interested objects
     */
    public function findByUserManaging(User $user, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.sharable', 's')
            ->addSelect('s')
            ->leftJoin('s.managedBy', 'm')
            ->addSelect('m')
            ->andWhere('m.user = :val')
            ->setParameter('val', $user)
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
        ;
        return $qb->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Interested
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
