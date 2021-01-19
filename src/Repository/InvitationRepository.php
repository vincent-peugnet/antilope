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

use App\Entity\Invitation;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    /**
     * Get all the invites that where used
     *
    * @return Invitation[] Returns an array of Invitation objects
    */
    public function findUsedInvitations(User $user)
    {
        $qb = $this->createQueryBuilder('i');
        $result = $qb->andWhere($qb->expr()->isNotNull("i.child"))
            ->andWhere('i.parent = :parent')
            ->setParameter('parent', $user->getId())
            ->getQuery()
            ->getResult();
        return $result;
    }

    /**
     * Get the current actives invites of an user
     * @return Invitation[]
     */
    public function findActiveInvitations(User $user, DateInterval $invitationDuration)
    {
        $passed = new DateTime();
        $passed->sub($invitationDuration);

        $qb = $this->createQueryBuilder('i');
        $result = $qb->andWhere($qb->expr()->isNull("i.child"))
            ->andWhere('i.parent = :parent')
            ->andWhere('i.createdAt > :passed')
            ->setParameter('parent', $user->getId())
            ->setParameter('passed', $passed)
            ->getQuery()
            ->getResult();
        return $result;
    }


    /*
    public function findOneBySomeField($value): ?Invitation
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
